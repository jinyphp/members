<?php

namespace Jiny\Members;

class Logout
{
    private $Auth;
    public $nextPage;

    use \Jiny\Petterns\Singleton; // 싱글턴 패턴 적용

    public function __construct()
    {
        // 인증확인 객체 생성
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
            $this->loginRedirection();
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
    private function loginRedirection()
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