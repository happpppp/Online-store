<?php

include "conn.php"; //连接数据库

$Content = GetPost(); //POST获得数据
foreach ($Content as $k => $v) {
    $UserID = $v->UserID; //获取用户名
    $Phone = $v->Phone; //获取电话
    $NewPassword = $v->NewPassword; //获取新密码

}

if ($UserID <> "" and $Phone <> "" and $NewPassword <> "") { //三个值都必传，所以不等于空才执行下面的代码
    $sql = "SELECT * FROM `User`  WHERE `UserID` = '{$UserID}' and `Phone` = '{$Phone}' ";
    $query = mysqli_query($conn, $sql);
    $rm = mysqli_fetch_array($query);
    $ID = $rm["id"];
    if ($ID == "") { //找不到ID证明用户名和手机不匹配，无法重置密码
        $rows = array("ErrorCode" => 10001);
        $json = json_encode($rows);
        echo $json;
    } else {
        $sql2 = "UPDATE `User` SET `Password` = '{$NewPassword}' where `UserID`= '{$UserID}' ";
        mysqli_query($conn, $sql2); //设置新密码
        $rows = array("ErrorCode" => 0);
        $json = json_encode($rows);
        echo $json;
    }
} else {
    $rows = array("ErrorCode" => 10000);
    $json = json_encode($rows);
    echo $json;
}

CloseTable($conn); //关闭数据库 //关闭数据库
