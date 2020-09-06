<?php

namespace Jiny\Members;

class Logout extends Config
{
    private $Auth;

    use \Jiny\Petterns\Singleton; // 싱글턴 패턴 적용

    public function __construct()
    {
        // 인증확인 객체 생성
        $this->Auth = \jiny\members\auth();
        $this->config();
    }

    public function main()
    {
        if($this->Auth->status()) {
            // 로그인 상태에서만 로그아웃이 가능합니다.
            $this->Auth->signout();

            if ($resource = $this->resource()) {
                // 로그아웃 리소스가 있는 경우 화면출력
                return $this->success($resource);
            }
        } 
        
        // 로그인 페이지 이동
        $this->redirect($this->conf->login->uri);        
    }

    /**
     * 로그아웃 페이지
     */
    private function success($file)
    {
        $vars = [];
        $body =  \jiny\html_get_contents($file, $vars);
        return $body;
    }

    private function resource()
    {
        // 리소스 파일경로
        return $this->conf->logout->resource;
    }

    /**
     * 로그인 페이지 리다이렉션
     */
    private function redirect($redirect)
    {
        header("HTTP/1.1 301 Moved Permanently");
        header("location:".$redirect);
    }

    public function uri()
    {
        return $this->conf->logout->uri;
    }

    /**
     * 
     */
}