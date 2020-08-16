<?php

namespace Jiny\Members\Admin;
/**
 * 권환설정
 */
class Permit extends \Jiny\Board\Parser
{
    public function __construct()
    {
        $confpath = "../vendor/jiny/members/conf/Permit.json";
        $this->conf = \jiny\json_get_array($confpath);
    }
}