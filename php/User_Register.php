<?php

include "conn.php"; //连接数据库

$Phone = $_GET["Phone"];
$EMail = $_GET["EMail"];
$UserID = $_GET["UserID"];
$Password = $_GET["Password"];
$Portrait = $_FILES["Portrait"];

if ($Portrait <> "") {
    $Portrait = UploadImage($Portrait, $UserID, "Portrait");
}

if ($UserID <> "" and $Password <> "") {
    $Time = date('Y-m-d H:i:s', time());
    $sql = "SELECT * FROM `User`  WHERE `UserID` = '{$UserID}'";
    $query = mysqli_query($conn, $sql);
    $rm = mysqli_fetch_array($query);
    $ID = $rm["id"];  //查看是否存在这个用户名

    if ($ID == "") { //如果不存在，就给他注册；
        $sql = "INSERT INTO `User` (`UserID`,`Phone`,`Portrait`,`Password`,`EMail`,`Time`)  VALUES ('{$UserID}','{$Phone}','{$Portrait}','{$Password}','{$EMail}','{$Time}')";
        mysqli_query($conn, $sql);

        $rows = array("ErrorCode" => 0);
        $json = json_encode($rows);
        echo $json;
    } else {
        $rows = array("ErrorCode" => 10001); //存在用户名就直接报错；
        $json = json_encode($rows);
        echo $json;
    }
} else {
    $rows = array("ErrorCode" => 10000);
    $json = json_encode($rows);
    echo $json;
}

function UploadImage($Image, $UserID, $Type) //上传图片函数
{
    if (is_uploaded_file($Image['tmp_name'])) {
        //把文件转存到你希望的目录（不要使用copy函数）
        $uploaded_file = $Image['tmp_name'];
        $username = "../Img/User";
        //我们给每个用户动态的创建一个文件夹

        $user_path = $username;
        //mkdir($user_path,0777,true);

        //判断该用户文件夹是否已经有这个文件夹
        if (!file_exists($user_path)) {
            mkdir($user_path);
        }

        //$move_to_file=$user_path."/".$_FILES['file']['name'];
        $file_true_name = $Image['name'];
        $move_to_file = $user_path . "/" . $Type . "_" . $UserID . substr($file_true_name, strrpos($file_true_name, "."));
        if (move_uploaded_file($uploaded_file, iconv("utf-8", "gb2312", $move_to_file))) {
            $move_to_file = str_replace("../", "", $move_to_file);
            return "http://localhost/" . $move_to_file; //自己记得修改自己的域名；
        } else {
            return "fail"; //上传失败，目录找不到
        }
    } else {
        return "fail"; //上传失败，文件没上传成功；
    }
}

CloseTable($conn); //关闭数据库
