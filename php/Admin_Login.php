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
        $sql2 = "UPDATE `Admin` SET `LastTime` = '{$Time}' where `UserID`= '{$UserID}' ";
        mysqli_query($conn, $sql2);

        $rows = array("ErrorCode" => 0);
        $json = json_encode($rows);
        echo $json;
    }
}

function Login($UserID, $Password)
{
    global $conn; //全局变量调用数据库连接
    $Time = date('Y-m-d H:i:s', time());
    $sql = "SELECT * FROM `Admin`  WHERE `UserID` = '{$UserID}'  and `Password` = '{$Password}' ";
    $query = mysqli_query($conn, $sql);
    $rm = mysqli_fetch_array($query);
    $ID = $rm["id"]; //看看密码对不对

    if ($ID == "") { //取不到ID，证明密码不对
        $rows = array("ErrorCode" => 10001);
        $json = json_encode($rows);
        echo $json;
    } else {
        //密码对，就给他登陆
        $sql2 = "UPDATE `Admin` SET `LastTime` = '{$Time}' where `UserID`= '{$UserID}' ";
        mysqli_query($conn, $sql2);

        $rows = array("ErrorCode" => 0, "Token" => Token_Create($UserID));
        $json = json_encode($rows);
        echo $json;
    }
}


CloseTable($conn); //关闭数据库
