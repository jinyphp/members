<?php

namespace Jiny\Members;

// 회원관리 설정파일
class Config
{
    private $config_path = "../config/login.json";
    protected $conf;

    protected function config()
    {
        if (file_exists($this->config_path)) {
            $this->conf = \jiny\json_get_object($this->config_path);
        }        
    }
}