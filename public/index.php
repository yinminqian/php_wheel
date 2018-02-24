<?php
require_once("../util/helper.php");
import("api/cat");

init();
function init()
{
    session_start();
    parse_uri();
}

function parse_uri()
{

    $uri = explode('?', $_SERVER['REQUEST_URI'])[0];
    $uri = trim($uri, '/');
    $arr = explode('/', $uri);
    $param = array_merge($_GET, $_POST);

    switch ($arr[0]) {
        case '':
            import('view/admin/cat');
            break;

        case"api":
            $klass = $arr[1];//类名
            $method = $arr[2];//方法


            if (!has_permission_to($method, $klass)) {
                json_die("permission_is_not");
            }
            $msg = [];
            $r = (new $klass)->$method($param, $msg);
            //$r是一个实例化的类,
            if ($r === false)
                json_die(e($msg));
            json_die(s($r));
            break;
    }


}


function has_permission_to($model, $klass)
{
    //$model是方法
    //$klass是类名
//    dd($model, $klass);

    $public = [//开放端口

        "cat" => ['read'],
    ];

    $private = [//权限端口
        'cat' => [
            'read' => ['user', 'admin', 'hr'],
        ],
    ];


    //$klass=cat
    //$model=read

    //检查传进来的方法是否是已经定义的
    //如果是没有定义的,返回
    if (!key_exists($klass, $public) && !key_exists($klass, $private)) {
        var_dump(1);
        return false;
    }


    //如果请求接口在public中就直接返回true;
    $klass_public = @$public[$klass];
    //如果有这个数组,或者床进来的方法,在这个数组中
    //返回true
    if ($klass_public && in_array($model, $klass_public)) {
        return true;
    }


    //检查传进来的类名是否在权限数组中定义了
    $klass_arr = @$private[$klass];
    //如果没有这个数组,或者传进来的方法不在这个数组中
    if (!$klass_arr && in_array($model, $klass_arr)) {
        return true;
    };

//    dd($model);

//    dd($klass_arr);
    //拿到权限数组表
    $permissions_arr = @$klass_arr[$model];
    //拿到当前用户的权限
    $user_permissions = @$_SESSION["user"][0]["permissions"];
//dd($user_permissions);
//    dd($permissions_arr);

    //检查用用户的权限是否出现在权限数组中

    if (!in_array($user_permissions, $permissions_arr)) {
        return false;
    }


    return true;

}



