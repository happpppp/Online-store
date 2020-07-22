<?php

include "conn.php"; //连接数据库

$Time = date('Y-m-d H:i:s', time());
$Content = GetPost(); //POST获得数据
foreach ($Content as $k => $v) {
    if ($v->Mode <> "") {
        $Password = $v->Password; //获取密码
        $Mode = $v->Mode; //获取模式
        $UserID = $v->UserID; //获取用户名
    }
}

//模式是Login就登陆函数，是Confirm就是自动登陆模式；
if ($Mode <> "") {
    switch ($Mode) {
        case "Login":
            Login($UserID, $Password);
            break;
        case "Confirm":
            Confirm();
            break;
        default:
            $rows = array("ErrorCode" => 10009);
            $json = json_encode($rows);
            echo $json;
    }
} else {
    $rows = array("ErrorCode" => 10000);
    $json = json_encode($rows);
    echo $json;
}

function Confirm()
{
    global $conn; //全局变量调用数据库连接
    global $Time;
    if (Token_Check() == true) {
        global $UserID; //通过全局变量拿到用户ID
        $sql = "SELECT * FROM `User`  WHERE `UserID` = '{$UserID}'  ";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $Phone = $rm["Phone"];
        $Email = $rm["EMail"];
        $Portrait = $rm["Portrait"];
        $UserID = $rm["UserID"];

        $sql2 = "UPDATE `User` SET `LastTime` = '{$Time}' where `UserID`= '{$UserID}' ";
        mysqli_query($conn, $sql2); //设置最后一次登陆时间
        if ($Portrait == "") {
            $Portrait = null;
        }
        $rows = array("ErrorCode" => 0, "Phone" => $Phone, "Email" => $Email, "Portrait" => $Portrait, "UserID" => $UserID); //返回用户信息
        $json = json_encode($rows);
        echo $json;
    }
}

function Login($UserID, $Password)
{
    global $conn; //全局变量调用数据库连接
    $Time = date('Y-m-d H:i:s', time());
    $sql = "SELECT * FROM `User`  WHERE `UserID` = '{$UserID}'  and `Password` = '{$Password}' ";
    $query = mysqli_query($conn, $sql);
    $rm = mysqli_fetch_array($query);
    $ID = $rm["id"];  //找不到ID的话，就是取不到信息，说明密码不对
    $Phone = $rm["Phone"];
    $Email = $rm["EMail"];
    $Portrait = $rm["Portrait"];
    if ($ID == "") {
        $rows = array("ErrorCode" => 10001); //登陆失败，密码不对
        $json = json_encode($rows);
        echo $json;
    } else {

        $sql2 = "UPDATE `User` SET `LastTime` = '{$Time}' where `UserID`= '{$UserID}' ";
        mysqli_query($conn, $sql2); //设置最后一次登陆时间
        if ($Portrait == "") {
            $Portrait = null;
        }
        $rows = array("ErrorCode" => 0, "Phone" => $Phone, "Email" => $Email, "Portrait" => $Portrait, "Token" => Token_Create($UserID)); //登陆成功返回token
        $json = json_encode($rows);
        echo $json;
    }
}


CloseTable($conn); //关闭数据库 //关闭数据库
