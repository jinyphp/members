<?php

namespace Jiny\Members;

class Mypage extends Config
{
    private $Auth;
    private $csrf;

    public function __construct()
    {
        $this->Auth = \jiny\members\auth();
        $this->config();

        $dbinfo = \jiny\dbinfo();
        $this->db = \jiny\mysql($dbinfo);

        $this->csrf = "hello";
    }


    /**
     * 시작 메소드
     */
    public function main()
    {
        if ($this->Auth->status()) {
            // 로그인 상태
            $reqMethod = strtoupper($_SERVER['REQUEST_METHOD']);
            if (method_exists($this,$reqMethod)) {
                return $this->$reqMethod();
            } else {
                echo __METHOD__;
                echo $reqMethod." 메소드를 호출할 수 없습니다.";
                exit;
            }
        } else {
            // 비로그인 상태일 경우, 로그인을 요청합니다.
            $this->loginRedirect();
        }
    }

    private function loginRedirect($uri=null)
    {
        if(!$uri) $uri = $this->conf->login->uri;

        // post redirect get pattern
        header("HTTP/1.1 301 Moved Permanently");
        header("location:".$uri);

    }

    public function GET()
    {
        // GET 동작
        if ($email = $this->Auth->email()) {

            $MemDB = new \Jiny\Members\Database;
            $info = $MemDB->byEmail($email, ['id','email','firstname','created_at']);
            if ($info) {
                $csrf = \jiny\html\csrf($this->csrf);     
                $vars=[
                    'info'=>$info,
                    'authway'=>$this->Auth->loginType(),
                    'mode'=>'edit',
                    'csrf'=>$csrf
                ];

                $file = $this->conf->mypage->resource;
                $body =  \jiny\html_get_contents($file, $vars);
                return $body;

            } else {
                echo __METHOD__;
                echo "데이터베이스에서 회원정보를 읽어올 수 없습니다.";
                exit;
            }
            
        } else {
            echo __METHOD__;
            echo "오류] 인증된 회원 이메일이 없습니다.";
            exit;
        }
    }


    public function POST()
    {
        // 동작 모드 파싱
        switch ($_POST['mode']) {
            case 'edit':
                return $this->edit();
            case 'editup':
                return $this->editUp();
        }     
    }

    // 회원정보 수정
    private function edit()
    {
        if ($email = $this->Auth->email()) {
            $MemDB = new \Jiny\Members\Database;            
            $info = $MemDB->byEmail($email,['id','email','firstname','created_at']); 
            if ($info) {
                $csrf = \jiny\html\csrf($this->csrf);
                $vars=[
                    'mode'=>'editup',
                    'csrf'=>$csrf,
                    'data'=>$info
                ];

                $file = $this->conf->mypage->edit->resource;
                $body =  \jiny\html_get_contents($file, $vars);
                return $body;

            } else {
                echo __METHOD__;
                echo "데이터베이스에서 회원정보를 읽어올 수 없습니다.";
                exit;
            }            

        } else {
            echo __METHOD__;
            echo "오류] 인증된 회원 이메일이 없습니다.";
            exit;
        }        
    }

    // 회원정보 수정
    private function editUp()
    {
        if (\jiny\html\isCsrf()) {
            if ($email = $this->Auth->email()) {
                $MemDB = new \Jiny\Members\Database;
                if ($data = $MemDB->byEmail($email)) {
                    // 데이터갱신
                    $formdata = \jiny\formData();
                    unset($formdata['email']); // 이메일은 수정이 붕가능함

                    // 패스워드 암호화 처리
                    if($formdata['password']) {
                        $PassWord = new \Jiny\Members\Encryption();
                        $formdata['password'] = $PassWord->encryption($formdata['password']);
                    } else {
                        // 패스워드 값이 없는 경우, 갱신을 하지 않음
                        unset($formdata['password']);
                    }
                    
                    // 데이터베이스 갱신
                    $MemDB->updateByEmail($email, $formdata);

                    header("HTTP/1.1 301 Moved Permanently");
                    header("location:".$this->conf->mypage->uri);
                }

            } else {
                echo __METHOD__;
                echo "오류] 인증된 회원 이메일이 없습니다.";
                exit;
            }  
            
        } else {
            return "CSRF 불일치";
        }
    }

    /**
     * 
     */
}