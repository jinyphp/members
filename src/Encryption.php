<?php

namespace Jiny\Members;

/**
 * 패스워드 암호화
 */
class Encryption
{
    private $algo = "sha256";
    private $salt = "jiny123456!@#$%^";
    public function __construct($salt=null)
    {
        // echo __CLASS__;
        if($salt) {
            $this->setSalt($salt);
        }
    }

    public function setAlgo($algo)
    {
        // 잘못된 해쉬방법 설정 필터
        if(\in_array($algo, hash_algos())){
            // echo "알고리즘 존재";
            $this->algo = $algo;
        } else {
            // echo "알고리즘 없음";
        }
        return $this;
    }

    public function getAlgo()
    {
        return $this->algo;
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    public function encryption($data)
    {
        return \hash($this->algo, $data.$this->salt);
    }

    public function verify($hash, $data)
    {
        // echo "<br> 해쉬값=".$hash;
        $key = $this->encryption($data);
        // echo "비밀번호=".$key;
        // echo "<br>";
        if($hash === $this->encryption($data)){
            return true;
        }
        return false;
    }
    /**
     * 
     */
}