<?php

namespace jiny;

function login_button_google($conf=null)
{
    $Google = new \Jiny\Members\Google($conf);
    return $Google->button();
}

function login_button_naver($conf=null)
{
    $Naver = new \Jiny\Members\Naver($conf);
    return $Naver->button();
}

function membersLogout()
{
    return \Jiny\Members\Logout::instance();
}
