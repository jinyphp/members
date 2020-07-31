<?php

namespace Jiny\Members;

class Mypage
{
    private $Auth;
    private $conf;
    private $csrf;
    public function __construct()
    {
        // echo __CLASS__;
        $this->Auth = new \Jiny\Members\Auth($this);
        $this->conf = \jiny\json_get_object("../Config/Login.json");

        $dbinfo = \jiny\dbinfo();
        $this->db = \jiny\mysql($dbinfo);

        $this->csrf = "hello";
    }

    public function main()
    {
        if(  $this->Auth->status()) {
            $http = \jiny\http();
            return $http->callback($this);

        } else {
            // 비로그인 상태일 경우, 로그인을 요청합니다.
            // post redirect get pattern
            header("HTTP/1.1 301 Moved Permanently");
            header("location:".$this->conf->login->uri);
        }
    }

    public function GET()
    {
        // GET 동작
        $email = $this->Auth->email();
        $MemDB = new \Jiny\Members\Database;
        $info = $MemDB->byEmail($email,['id','email','firstname','created_at']);
        $csrf = \jiny\html\csrf($this->csrf);     
        $vars=[
            'info'=>$info,
            'authway'=>$this->Auth->loginType(),
            'mode'=>'edit',
            'csrf'=>$csrf
        ];
        //print_r($vars);

        $file = $this->conf->mypage->resource;
        $body =  \jiny\html_get_contents($file, $vars);
        return $body;
    }

    public function POST()
    {
        if($_POST['mode'] == 'edit') {
            return $this->edit();
        } else if($_POST['mode'] == 'editup') {
            return $this->editUp();
        }        
    }

    private function edit()
    {
        // echo "회원정보 수정";
        $email = $this->Auth->email();
        $MemDB = new \Jiny\Members\Database;
        $info = $MemDB->byEmail($email,['id','email','firstname','created_at']); 

        $csrf = \jiny\html\csrf($this->csrf);
        $vars=[
            'mode'=>'editup',
            'csrf'=>$csrf,
            'data'=>$info
        ];

        $file = $this->conf->mypage->edit->resource;
        $body =  \jiny\html_get_contents($file, $vars);
        return $body;
    }

    private function editUp()
    {
        // echo "데이터베이스 회원정보 수정";
        if (\jiny\html\isCsrf()) {

            $email = $this->Auth->email();
            $MemDB = new \Jiny\Members\Database;
            if ($data = $MemDB->byEmail($email)) {
                // 데이터갱신
                $formdata = \jiny\formData();
                unset($formdata['email']);

                if($formdata['password']) {
                    // 패스워드 암호화
                    $PassWord = new \Jiny\Members\Encryption();
                    $formdata['password'] = $PassWord->encryption($formdata['password']);
                } else {
                    unset($formdata['password']);
                }
                
                $MemDB->updateByEmail($email, $formdata);

                header("HTTP/1.1 301 Moved Permanently");
                header("location:".$this->conf->mypage->uri);
            }                

        } else {
            return "CSRF 불일치";
        }
    }

    /**
     * 
     */
}