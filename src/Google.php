<?php

namespace Jiny\Members;

class Google
{
    use \Jiny\Petterns\Singleton; // 싱글턴 패턴 적용

    private $login_url;
    private $client;
    private $Auth;
    private $conf;
    public function __construct($conf=null)
    {
        if (!$conf) {
            $this->conf = \json_decode(\file_get_contents("../Config/Login.json"));
        }       

        $this->Auth = new \Jiny\Members\Auth();
        $this->init($this->conf->google);
    }

    /**
     * 구글 클라이언트 초기화
     */
    private function init($key)
    {
        $this->client = new \Google_Client(); // 네임스페이스 root
        
        $this->client->setClientId($key->client_id);
        $this->client->setClientSecret($key->secret);
        $this->client->setApplicationName($key->application);
        $this->client->setRedirectUri($key->redirect);
        $this->client->addScope("https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email");
        
        // login URL
        $this->login_url = $this->client->createAuthUrl();
    }

    public function enable()
    {
        return isset($this->conf->google);
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
        $_SESSION['login-type'] = "google";

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
            $data['password'] = (new Encryption())->encryption($userData['id']);
            $data['auth_at'] = date("Y-m=d H:i:s");

            $MemDB->insert($data); // 긴규회원 등록
        }
        
        return null;
    }

    private function oAuth2($token)
    {
        if(isset($token["error"]) && ($token["error"] == "invalid_grant")){
            // error
            // header('Location: index.php');
            // exit();

        } else {
            // get data from google
            $oAuth = new \Google_Service_Oauth2($this->client);
            return $oAuth->userinfo_v2_me->get();
        }
    }

    private function checkCode()
    {
        if (isset($_GET['code'])) {
            //echo "인증코드=".$_GET['code'];
            return $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
        } else {
            // header('Location: /');
            echo "인증 코드가 필요합니다.";
            exit;
        }
    }

    public function button($type="button")
    {
        if($type == "button") {
            return "<button type='button' onclick=\"document.location.href='".$this->login_url."'\" class='google-login'>구글로그인</button>";
        } else {
            return "<a href='' class='google-login'>구글로그인</a>";
        } 
    }

    public function href()
    {
        return $this->login_url; 
    }

    /**
     * 
     */
}
