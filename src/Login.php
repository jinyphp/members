<?php

namespace Jiny\Members;

class Login extends Config
{
    private $db;
    private $Auth;

    public function __construct()
    {
        $this->Auth = \jiny\members\auth();
        $this->config();
    }

    /**
     * 시작 main
     */
    public function main()
    {
        if($this->Auth->status()) {
            // 로그인 상태
            return $this->mypage();

        } else {
            // 인증 요청
            // $http = \jiny\http();
            // return $http->callback($this);
            $reqMethod = strtoupper($_SERVER['REQUEST_METHOD']);
            if (method_exists($this, $reqMethod)) {
                return $this->$reqMethod();
            } else {
                echo __METHOD__;
                echo $reqMethod." 메소드를 호출할 수 없습니다.";
                exit;
            }

        }
    }

    /**
     * 로그인상태 / 페이지 이동
     */
    public function mypage()
    {
        if(isset($this->conf->mypage->uri)) {
            $this->redirect($this->conf->mypage->uri);
        } else {
            // /jiny/error(__METHOD__, "mypage 이동 uri가 설정되어 있지 않습니다.");
        }        
    }

    private function redirect($redirect)
    {
        // post redirect get pattern
        header("HTTP/1.1 301 Moved Permanently");
        header("location:".$redirect);
    }


    public function GET($vars=[])
    {
        // GET 동작
        return $this->view();
    }

    // 유효성 검사
    private function isEmail()
    {
        if (!isset($_POST['data']['email']) || empty($_POST['data']['email'])) {
            $this->error_message = "이메일이 입력되지 않았습니다.";
            return false;
        } else {
            return true;
        }
    }

    // 유효성 검사
    private function isPassword()
    {
        if (!isset($_POST['data']['password']) || empty($_POST['data']['password'])) {
            $this->error_message = "패스워드가 입력되지 않았습니다.";
            return false;
        } else {
            return true;
        }
    }

    /**
     * post 메서드 파서 
     */
    public function POST()
    {
        $data = \jiny\formData();   
        if ($this->isEmail() && $this->isPassword()) {
            
            // 로그인 DB인증을 요청합니다.
            $status = $this->Auth->signin($data['email'], $data['password']);
            if($status) {
                // mypage로 이동을 합니다.
                return $this->mypage();
            } else {
                // 로그인 실패
                $this->error_message = "이메일, 패스워드가 일치하지 않습니다.";
            }         
        }

        return $this->view();
    }



    // 로그인 화면
    private function view()
    {
        $email = isset($_POST['data']['email']) ? $_POST['data']['email'] : "";
        $vars = [
            'email' => $email
        ];

        $resource = $this->conf->login->resource;
        $body =  \jiny\html_get_contents($resource, $vars);
        return $body;
    }

    /**
     * 
     */
}