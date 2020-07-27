<?php

namespace Jiny\Members;
/**
 * 회원 약관동의
 */
class Agree
{
    public $nextURL = "/regist/form";
    public function __construct()
    {

    }

    public function main()
    {
        $http = \jiny\http();
        return $http->callback($this);
    }

    public function GET()
    {
        //echo "쿠키\n";
        // print_r($_COOKIE);
        // echo "쿠키 agree1=".$_COOKIE['input_agree1'];
        // GET 동작
        $body = file_get_contents("../resource/agree.html");
        return $body;
    }

    public function POST()
    {
        print_r($_POST);
        foreach($_POST['data'] as $key => $value) {
            if($value) {
                setcookie("input_".$key, $value, time()+60*10,"/"); //10분유지
            } else {
                setcookie("input_".$key, "", time()-60*10); //10분유지
            }
        }

        //exit
        // 이전페이지
        // header('Location: ' . $_SERVER['HTTP_REFERER']);

        // post redirect get pattern
        header("HTTP/1.1 301 Moved Permanently");
        header("location:".$this->nextURL);    
    }

}
