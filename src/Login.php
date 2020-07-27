<?php

namespace Jiny\Members;

class Login
{
    private $db;
    private $conf;
    public function __construct()
    {
        // echo __CLASS__;
        $dbinfo = \jiny\dbinfo();
        $this->db = \jiny\mysql($dbinfo);

        $this->conf = \jiny\json_get_object("../Config/Login.json");
    }

    public function main()
    {
        if( !$this->Auth->status()) {
            $http = \jiny\http();
            return $http->callback($this);
        } else {
            // 로그인 상태일경우, mypage로 이동합니다.
            // post redirect get pattern
            header("HTTP/1.1 301 Moved Permanently");
            header("location:".$this->conf->mypage->uri);
        }
    }

    public function GET()
    {
        // GET 동작
        $file = $file = $this->conf->login->resource;
        $body = file_get_contents($file);
        return $body;
    }

    public function POST()
    {
        //echo "로그인 검증";
        $data = \jiny\formData();
        //print_r($data);

        $select = $this->db->select("members",["email","password"])->where(['email'])->build();
        //echo $select->getQuery();
        $rows = $select->runObj(['email'=>$data['email']]);
        
        //print_r($rows);

        if($rows) {
            //echo "쿼리 성공";
            $PassWord = new Password();
            if($PassWord->verify($rows->password, $data['password'])) {
                //echo "암호성공";
            } else {
                //echo "다시확인 필요";
            }
        }
  

        // post redirect get pattern
        header("HTTP/1.1 301 Moved Permanently");
        header("location:".$this->url);
        
    }
}