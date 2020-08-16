<?php

namespace Jiny\Members\Admin;
/**
 * board controller 상속
 * 회원관리
 */
class Members extends \Jiny\Board\Controller
{
    protected $controller; //controller
   
    // 초기화
    public function __construct($controller=null)
    {
        $this->controller = $controller;
        $conf = $this->confPath();
        $this->init()->setEnv($conf);
    }

    public function confPath()
    {
        return str_replace("php", "json", __FILE__);
    }

    /**
     * 
     */
}