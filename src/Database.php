<?php

namespace Jiny\Members;
// members database
class Database
{
    private $_db;
    private $_tablename = "members";
    public function __construct($db=null)
    {
        echo __CLASS__;
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

}