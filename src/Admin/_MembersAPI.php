<?php

namespace Jiny\Members\Admin;
/**
 * 회원관리 API
 */
class MembersAPI extends \Jiny\Board\State
{
    public $confpath = "../vendor/jiny/members/conf/Members.json";

    public function __construct()
    {
        $method = \jiny\http\request()->method();
        $this->conf = \jiny\json_get_array($this->confpath);
    }

    public function POST($params=[], $body=null)
    {
        // API 접속
        if(\jiny\isAPI()) {
            // 리스트목록 출력
            $this->searchField()->searchValue(); // search 쿠키 설정
            if (isset($body->limit)) {
                $limit = $body->limit;
            } else {
                $limit = $this->limit($body->limit);
            }
        
            // 일반 리스트 출력
            $method = "stateLIST";
            return $this->$method($limit);
            
        } 
        // 일반접속
        else {
            return parent::POST($params);
        }
    }

    public function PUT($params=[], $body=null)
    {
        // application/x-www-form-urlencoded; charset=UTF-8
        echo "PUT 업데이트 ";
        print_r($body);
    }

}