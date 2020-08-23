<?php

namespace Jiny\Members;

class Naver
{
    public $apiURL;
    public $client_id;
    public $redirectUR;
    public $client_secret;
    private $conf;
    private $Auth;
    public function __construct($conf=null)
    {
        if (!$conf) {
            $this->conf = \json_decode(\file_get_contents("../Config/Login.json"));
        }
        $this->Auth = new \Jiny\Members\Auth();
        $this->init($this->conf->naver);
    }

    private function init($naver)
    {
        $this->client_id = $naver->client_id; // 위에서 발급받은 Client ID 입력
        $this->client_secret = $naver->secret; ///Client Secret 입력

        $this->redirectURI = urlencode($naver->redirectURI); //자신의 Callback URL 입력
 
        $state = md5(microtime() . mt_rand()); // 램덤값
        $this->apiURL = "https://nid.naver.com/oauth2.0/authorize?response_type=code&client_id=";
        $this->apiURL .= $this->client_id."&redirect_uri=".$this->redirectURI."&state=".$state;
   
    }

    private function checkCode()
    {
        if (isset($_GET['code'])) {
            //echo "인증코드=".$_GET['code'];
            return $_GET['code'];
        } else {
            // header('Location: /');
            echo "인증 코드가 필요합니다.";
            exit;
        }
        return false;
    }


    public function main()
    {
        if($this->Auth->status()) {
            // echo "로그인 상태";
            $this->mypage();
        } else {
            //echo "구글 인증 로그인";
            $token = $this->checkCode();
            if ($userData = $this->oAuth2($token)) {
                $this->success($userData);                
                $this->mypage();
            }
        }
    }

    private function oAuth2($token)
    {
        echo "네이버 로그인";
        $code = $token;
        $state = $_GET["state"];
        
        $url = "https://nid.naver.com/oauth2.0/token?grant_type=authorization_code&client_id=";
        $url .= $this->client_id."&client_secret=".$this->client_secret;
        $url .= "&redirect_uri=".$this->redirectURI."&code=".$code."&state=".$state;
        $is_post = false;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $is_post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = array();
        $response = curl_exec ($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo "status_code:".$status_code."<br>";
        
        curl_close ($ch);
        
        if($status_code == 200) {
            echo $response;
            $responseArr = json_decode($response, true);
            $_SESSION['naver_access_token'] = $responseArr['access_token'];
            $_SESSION['naver_refresh_token'] = $responseArr['refresh_token']; // 토큰값으로 네이버 회원정보 가져오기

            $me_headers = array( 'Content-Type: application/json', sprintf('Authorization: Bearer %s', $responseArr['access_token']) );

            $me_is_post = false; 
            $me_ch = curl_init(); 
            curl_setopt($me_ch, CURLOPT_URL, "https://openapi.naver.com/v1/nid/me");
            curl_setopt($me_ch, CURLOPT_POST, $me_is_post); 
            curl_setopt($me_ch, CURLOPT_HTTPHEADER, $me_headers);
            curl_setopt($me_ch, CURLOPT_RETURNTRANSFER, true); 
            $me_response = curl_exec($me_ch);
            $me_status_code = curl_getinfo($me_ch, CURLINFO_HTTP_CODE); 
            curl_close($me_ch);
            $me_responseArr = json_decode($me_response, true);

            echo "<pre>";
            print_r($me_responseArr);
            return $me_responseArr['response'];

            // $this->success($me_responseArr['response']);
        } else {
            echo "Error 내용:".$response;
        }
    }

    private function mypage()
    {
        // post redirect get pattern
        header("HTTP/1.1 301 Moved Permanently");
        header("location:".$this->conf->mypage->uri);
    }

    private function success($userData)
    {
        // 로그인 세션 설정
        \session_regenerate_id(); // session fixed 해킹방지
        $_SESSION['login'] = $userData['email'];
        $_SESSION['login-type'] = "naver";

        // 데이터베이스 저장
        if ($id = $this->savedata($userData)) {
            // echo "데이터 삽입 성공 = ".$id;
            $this->mypage();
        } else {
            // echo "데이터 삽입 실패";
        }        
    }

    private function savedata($userData)
    {
        $email = $userData['email'];
        $MemDB = new \Jiny\Members\Database;
        if ($data = $MemDB->byEmail($email)) {

        } else {
            // 신규등록
            $data['email'] = $userData['email'];
            $data['password'] = (new Password())->encryption($userData['id']);
            $data['auth_at'] = date("Y-m=d H:i:s");

            $MemDB->insert($data); // 긴규회원 등록
        }
        
        return null;
    }


    public function logout()
    {
        // 네이버 접근 토큰 삭제 
        $naver_curl = "https://nid.naver.com/oauth2.0/token?grant_type=delete&client_id=".NAVER_CLIENT_ID."&client_secret=".NAVER_CLIENT_SECRET."&access_token=".urlencode($mb['mb_sns_token'])."&service_provider=NAVER"; 
        $is_post = false; 

        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $naver_curl); 
        curl_setopt($ch, CURLOPT_POST, $is_post); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        $response = curl_exec ($ch); 
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close ($ch); 
        if($status_code == 200) { 
            $responseArr = json_decode($response, true); // 멤버 DB에서 회원을 탈퇴해주고 로그아웃(세션, 쿠키 삭제) 
            if ($responseArr['result'] != 'success') { 
                // 오류가 발생하였습니다. 
                // 네이버 내정보->보안설정->외부 사이트 연결에서 해당앱을 삭제하여 주십시오 
            } 
        } else { 
            // 오류가 발생하였습니다. 
            // 네이버 내정보->보안설정->외부 사이트 연결에서 해당앱을 삭제하여 주십시오. 
        }

    }


    public function button()
    {
        return "<a href='".$this->apiURL."'><img height='50' src='http://static.nid.naver.com/oauth/small_g_in.PNG'/></a>";
    }

    public function href()
    {
        return $this->apiURL;
    }
}


