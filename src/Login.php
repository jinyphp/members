<?php

namespace Jiny\Members;

class Login
{
    private $db;
    private $Auth;
    public $conf;
    private $error_message = "";
    public function __construct()
    {
        // 미들웨어
        $this->Auth = new \Jiny\Members\Auth($this);
        $this->conf = \json_decode(\file_get_contents("../config/login.json"));
        // print_r($this->conf);

        $this->resource = $this->conf->login->resource;
    }

    /**
     * 기본 시작main
     */
    public function main()
    {
        if($this->Auth->status()) {
            // 로그인 상태
            return $this->mypage();

        } else {
            // 인증 요청
            $http = \jiny\http();
            return $http->callback($this);
        }
    }

    /**
     * 로그인상태
     * 페이지 이동
     */
    public function mypage()
    {
        // $page = "/mypage";
        $page = $this->conf->mypage->uri;
        // echo "마이페이지";
        // post redirect get pattern
        header("HTTP/1.1 301 Moved Permanently");
        header("location:".$page);
    }

    public function GET($vars=[])
    {
        // GET 동작
        return $this->view();
    }

    private function isEmail()
    {
        if (!isset($_POST['data']['email']) || empty($_POST['data']['email'])) {
            $this->error_message = "이메일이 입력되지 않았습니다.";
            return false;
        } else {
            return true;
        }
    }
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
        // echo "로그인 검증";
        $data = \jiny\formData();
        
        if ($this->isEmail() && $this->isPassword()) {
            $status = $this->Auth->signin($data['email'], $data['password']);
            if($status) {
                // echo "로그인 성공";
                return $this->mypage();
            } else {
                // 로그인 실패
                // echo $this->Auth->message;
                $this->error_message = "이메일, 패스워드가 일치하지 않습니다.";
            }         
        }

        return $this->view();
    }

    public $resource;
    private function view()
    {
        $email = isset($_POST['data']['email']) ? $_POST['data']['email'] : "";
        $vars = [
            'error_message'=>$this->error_message,
            'email' => $email
        ];
        // $this->resource = $this->conf->login->resource;
        // $file = $this->conf->login->resource;
        // echo $file;
        $body =  \jiny\html_get_contents($this->resource, $vars);
        return $body;
    }

    /**
     * 
     */
}