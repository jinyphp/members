<?php

namespace Jiny\Members;

class Logout
{
    private $Auth;
    public $nextPage;

    /**
     * 싱글턴
     */
    public static $_instance;

    public static function instance($args=null)
    {
        if (!isset(self::$_instance)) {        
            //echo "객체생성\n";
            // print_r($args);   
            self::$_instance = new self($args); // 인스턴스 생성
            if (method_exists(self::$_instance,"init")) {
                self::$_instance->init();
            }
            return self::$_instance;
        } else {
            //echo "객체공유\n";
            return self::$_instance; // 인스턴스가 중복
        }
    }

    public function __construct()
    {
        // echo __CLASS__;
        $this->Auth = new \Jiny\Members\Auth($this);
    }

    public function main()
    {
        if($this->Auth->status()) {
            // 로그인 상태에서만 로그아웃이 가능합니다.
            $this->Auth->signout();
            return $this->success();

        } else {
            // 로그인 페이지 이동
            $this->login();
        }
    }

    /**
     * 로그아웃 페이지
     */
    private function success($file = "../resource/members/logout.html")
    {
        $body =  \jiny\html_get_contents($file, $vars);
        return $body;
    }

    /**
     * 로그인 페이지 리다이렉션
     */
    private function login()
    {
        $redirect = "/login";
        header("HTTP/1.1 301 Moved Permanently");
        header("location:".$redirect);
    }

    public function uri()
    {
        return "/logout";
    }

    /**
     * 
     */
}