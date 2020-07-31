<?php

namespace Jiny\Members;
/**
 * 회원 약관동의
 */
class Agree
{
    private $Auth;
    private $conf;
    public function __construct()
    {
        $this->Auth = new \Jiny\Members\Auth($this);
        $this->conf = \jiny\json_get_object("../Config/Login.json");
    }

    public function main()
    {
        if($this->Auth->status()) {
            // echo "로그아웃 상태에서만 회원가입이 가능합니다.";
            // post redirect get pattern
            header("HTTP/1.1 301 Moved Permanently");
            header("location:".$this->conf->mypage->uri); 
        } else {
            $http = \jiny\http();
            $method = $http->Request->method();
            return $this->$method();
        }        
    }

    private function GET()
    {
        // GET 동작
        if ($this->conf->regist->agree->require) {
            $file = $this->conf->regist->agree->resource;
            $body = \jiny\html_get_contents($file );
            return $body;
        } else {
            // echo "회원동의 필요 없음.";
            header("HTTP/1.1 301 Moved Permanently");
            header("location:".$this->conf->regist->forms->uri);
        }
        
    }

    private function POST()
    {
        $data = \jiny\formData();
        print_r($data);
        /*
        foreach($_POST['data'] as $key => $value) {
            if($value) {
                setcookie("input_".$key, $value, time()+60*10,"/"); //10분유지
            } else {
                setcookie("input_".$key, "", time()-60*10); //10분유지
            }
        }*/

        //exit
        // 이전페이지
        // header('Location: ' . $_SERVER['HTTP_REFERER']);

        // post redirect get pattern
        /*
        $redirect = "/regist/form";
        header("HTTP/1.1 301 Moved Permanently");
        header("location:".$this->nextURL);
        */
        
    }

}
