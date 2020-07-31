<?php
/**
 * 패키지 네임스페이스
 */
namespace jiny\members;

function logout()
{
    return \Jiny\Members\Logout::instance();
}


/**
 * 서브 네임스페이스 설정
 */
namespace jiny\members\logout;

function uri()
{
    return \Jiny\Members\logout()->uri();
}

/**
 * 서브 네임스페이스 설정
 */
namespace jiny\members\login\button;
function google($conf=null)
{
    $Google = new \Jiny\Members\Google($conf);
    echo $Google->button();
}

function naver($conf=null)
{
    $Naver = new \Jiny\Members\Naver($conf);
    echo $Naver->button();
}