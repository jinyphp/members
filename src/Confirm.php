<?php
namespace Jiny\Members;

/**
 * 회원가입 처리
 */
class Confirm
{
    private $conf;
    private $db;
    private $tablename;
    public function __construct()
    {
        // echo __CLASS__;
        $dbinfo = \jiny\dbinfo();
        $this->db = \jiny\mysql($dbinfo);

        $this->tablename = "members";
    }

    public function main()
    {
        // echo "회원가입 승인";
        if ($_GET['token']) {
            if ($row = $this->userToken($token)) {
                //print_r($row);
                if ($this->authUpdate($row)) {
                    // 화면출력
                    $file = "../resource/members/confirm.html";
                    $body = \jiny\html_get_contents($file, $row);
                    return $body;
                } else {
                    $msg = "메일인증 갱신 실패";
                }                
            } else {
                $msg = "잘못된 회원 토큰 코드입니다. 또는, 이미 인증된 회원입니다.";
            }
            
        } else {
            $msg = "회원가입 토큰 정보가 없습니다.";
        }

        $error = new \Jiny\Members\Error($msg);
        return $error->main();
    }

    private function authUpdate($row)
    {
        // 회원데이터 갱신
        $confirm = [
            'token'=>"",
            'auth_at'=>date("Y-m=d H:i:s")
        ];
        $update = $this->db->update($this->tablename, $confirm)->autoField()->id($row['id']);
        return $update;
    }

    private function userToken($token)
    {
        $select = $this->db->select($this->tablename, ['id','email','token'])->where(["token"]);
        $query = $select->build()->getQuery();
        return $select->autoTable()->runAssoc(['token'=>$token]);
    }
}