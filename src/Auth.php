<?php

namespace Jiny\Members;

class Auth
{
    private $db;
    private $_status = false;
    private $controller;
    
    public $message;

    public function __construct($ctrl=null)
    {
        // echo __CLASS__;
        $this->controller = $ctrl;

        // 미들웨어
        $dbinfo = \jiny\dbinfo();
        $this->db = \jiny\mysql($dbinfo);

        // 세션 시작
        // \session_start();
    }

    // 인증상태 여부
    public function status()
    {
        if(isset($_SESSION['login']) ) {
            return true;
        } else {
            return false;
        }
    }

    // 로그인처리
    public function signin($email, $password)
    {
        // echo __METHOD__;

        $select = $this->db->select("members",["email","password"])->where(['email'])->build();
        //echo $select->getQuery();
        $rows = $select->runObj(['email'=>$email]);
        
        //print_r($rows);

        if($rows) {
            //echo "쿼리 성공";
            $PassWord = new Encryption();
            if($PassWord->verify($rows->password, $password)) {
                $this->message = "로그인 성공";
                \session_regenerate_id(); // session fixed 해킹방지
                $_SESSION['login'] = $email;
                $_SESSION['login-type'] = "email";
                return true;
                
            } else {
                $this->message = "패스워드가 일치하지 않습니다.";
                return false;
            }
        } else {
            $this->message = "등록된 회원이 없습니다.";
            return false;
        }
    }

    public function email()
    {
        if(isset($_SESSION['login'])) {
            return $_SESSION['login'];
        }
    }

    public function loginType()
    {
        if(isset($_SESSION['login-type'])) {
            return $_SESSION['login-type'];
        }
        return "-";    
    }

    // 로그아웃 처리
    public function signout()
    {
        if ($_SESSION['login-type'] == "email") {

        } else if ($_SESSION['login-type'] == "google") {

        } else if ($_SESSION['login-type'] == "naver") {

        } else if ($_SESSION['login-type'] == "facebook") {

        } else if ($_SESSION['login-type'] == "twitter") {

        } else if ($_SESSION['login-type'] == "kakao") {

        } 
        
        session_destroy();
        $this->_status = false;
    }
}