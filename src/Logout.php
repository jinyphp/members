<?php

namespace Jiny\Members;

class Logout
{
    /**
     * 싱글턴
     */
    public static $_instance;
    public static function instance($args=null)
    {
        if (!isset(self::$_instance)) {        
            self::$_instance = new self($args); // 인스턴스 생성
            if (\method_exists(self::$_instance,"init")) {
                self::$_instance->init();
            }
            return self::$_instance;
        } else {
            return self::$_instance; // 인스턴스가 중복
        }
    }

    public function destroy()
    {
        unset(self::$_instance);
    }

    private $conf;
    public function init()
    {
        $this->conf = \jiny\json_get_object("../Config/Login.json");
    }

    public function main()
    {
        if (isset($_SESSION['login-type'])) {
            $method = $_SESSION['login-type'];
            if(\method_exists($this, $method)) {
                $this->$method(); // 로그아웃 동작 호출
            }
        }
        \session_destroy();

        if ($this->conf->logout->resource) {
            return \jiny\html_get_contents($this->conf->logout->resource);
        } else {
            // post redirect get pattern
            header("HTTP/1.1 301 Moved Permanently");
            header("location:".$this->conf->logout->uri);
        }
    }

    /**
     * 이메일 로그아웃
     */
    private function email()
    {

    }

    /**
     * 구글 로그아웃
     */
    private function google()
    {

    }

    /**
     * 네이버 로그아웃
     */
    private function naver()
    {

    }

    /**
     * 트위터 로그아웃
     */
    private function twitter()
    {

    }

    /**
     * 페이스북 로그아웃
     */
    private function facebook()
    {

    }

    /**
     * 카카오 로그아웃
     */
    private function kakao()
    {

    }

    /**
     * 로그아웃 uri
     */
    public function uri()
    {
        return $this->conf->logout->uri;
    }

}