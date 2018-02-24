<?php


function dd()
{
    $d = func_get_args();

    foreach ($d as $item) {
        var_dump($item);
    }

    die();
}


function e($msg, $code = 403)
{
//    if ($msg == "db_error") {
//        $code = 500;
//    }
//    http_post_data($code);
    return ["success" => false, "mag" => $msg];
}


function s($data = null, $code = 200)
{
//    http_post_data($code);
    return ["success" => true, "data" => $data];
}

function json_die($data)
{
    echo json($data);
    die();
}


function json($data)
{
    header("Content-Type:application/json");
    return json_encode($data);
}

function tpl($path, $ext = "php")
{
    return dirname(__FILE__) . "/../" . $path . ($ext ? "." . $ext : "");
}

function import($path, $ext = "php")
{
    return require_once(tpl($path, $ext));
}


function config($key)
{

    if (!$config = @$GLOBALS["__config"]) {
        $json = file_get_contents(tpl(".config", "json"));
        $config = json_decode($json, true);
        $GLOBALS["__config"] = $config;
    }
    return @$config[$key];
}

//返回值是否登录的布尔值
//是否存在用户id
function login_in()
{
    return (bool)@$_SESSION["user"][0]["id"];
}

//返回登录用户的个人信息
function his($key)
{
    if (!login_in()) {
        return null;
    }
    return @$_SESSION["user"][0][$key];
}


function move_uploaded($key, &$data = null)
{
//    ini_set('upload_max_filesize', '1024');
//    dd($_FILES);
    $file_type = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];
    //图片类型

    $file = @$_FILES[$key];
    //图片的详细信息
//    dd($file);

    if (!$tmp = @$file["tmp_name"]) {
        return false;
    }
//    dd(1);
    $old_name = @$file["name"];
    //图片的自带名字
    $new_name = uniqid() . "." . rand(1, 9999999);
    //新的图片名字,一个以毫秒计数的时间,和一个随机数

    $mime = @$file["type"];
    //取到图片的旧属性
    $ext = @$file_type[$mime];
    //把图片的旧转换成图片的后辍名


//    dd(root("upload","")."/$new_name.$ext");
    $dest = tpl("public/upload","")."/$new_name.$ext";
    if ($r=move_uploaded_file($tmp,$dest)){
        //上传的文件移动到新位置,返回布尔值
        $data = [
            'name'     => $new_name,
            'ext'      => $ext,
            'new_name' => "$new_name.$ext",
            'mime'     => $mime,
            'size'     => $file['size'],
            'old_name' => $old_name,
        ];
        //返回data数据,把图片的现有信息返回;
//        dd($r);
        return $r;
    }
}

function redirect($url){
    header("location:".$url);
}
