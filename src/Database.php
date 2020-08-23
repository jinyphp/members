<?php

namespace Jiny\Members;
// members database
class Database
{
    private $_db;
    private $_tablename = "members";
    public function __construct($db=null)
    {
        // echo __CLASS__;
        if ($db) {
            $this->_db = $db;
        } else {
            $dbinfo = \jiny\dbinfo();
            $this->_db = \jiny\mysql($dbinfo);
        }
    }

    public function setTable($name)
    {
        $this->_tablename = $name;
        return $this;
    }

    public function getTable()
    {
        return $this->_tablename;
    }




    // 회원목록
    public function list()
    {
        return $this->_db->select($this->_tablename)->runObjAll();
    }

    public function id($id)
    {
        return $this->_db->select($this->_tablename)->where(['id'])->runObj(['id'=>$id]);
    }

    public function byEmail($email, $field=null)
    {
        $select = $this->_db->select($this->_tablename, $field)->where(["email"]);
        $query = $select->build()->getQuery();
        //echo $query;
        //echo $this->Auth->email()."<br>";
        return $select->runAssoc(['email'=>$email]);
    }

    public function updateByEmail($email, $arr)
    {
        $this->_db->update($this->_tablename, $arr)->where(["email"])->build()->run(['email'=>$email]);
    }


    public function replace($data)
    {
        $query = "REPLACE INTO members (id, data) VALUE (?, ?)";
        $stmt = $this->_db->prepare($query);
        $stmt->execute([$id, $data]);
        if ($stmt->rowCount() >0) {
            return true;
        } else {
            return false;
        }
    }

    public function insert($data)
    {
        $insert = $this->_db->insert($this->_tablename, $data);
        if ($id = $insert->save()) {
            return $id;
        }
        return 0;
    }


    /// 파사드 함수
    public function newUser($data)
    {
        if ($this->byEmail($data['email'])) {
            // 회원 이메일 중복
            return false;
        } else {
            // 패스워드 암호화
            $encrypt = new \Jiny\Members\Encryption();
            $data['password'] = $encrypt->encryption($data['password']);

            // 데이터베이스 삽입
            $insert = $this->_db->insert("members", $data)->autoField();
            if ($id = $insert->save()) {
                // echo "데이터 삽입 성공 = ".$id;
                return $id;
            } else {
                // echo "데이터 삽입 실패";
                return false;
            }
        }     
    }





}