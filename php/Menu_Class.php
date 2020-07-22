<?php

include "conn.php"; //连接数据库

$Contentmy = GetPost();
foreach ($Contentmy as $k => $v) {
    $Mode = $v->Mode;
    $Type = $v->Type;
    $ID = $v->ID;
}


if ($Mode <> "") {
    switch ($Mode) {
        case "C":
            Create($Type);
            break;
        case "L":
            _List();
            break;
        case "D":
            Delete($ID);
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

function ProductYesNo($Type){ //判断是否有未删除的商品是属于这个类型的 函数
    global $conn; //全局变量调用数据库连接
    $sql = "SELECT * FROM `Product`  WHERE `Type` ='{$Type}' and `Del` = 'false' ";
    $query = mysqli_query($conn, $sql);
    $rm = mysqli_fetch_array($query);
    $idd = $rm["id"];
    if($idd==""){
        return "No"; //表示没有存在未删除商品
    }else{
        return "YES"; //表示存在未删除商品
    }
}

function Delete($ID)
{
    global $conn; //全局变量调用数据库连接
    if (Token_Check() == true) {
        if ($ID <> "") {
            $sql = "SELECT * FROM `Menu`  WHERE `id` ='{$ID}'";
            $query = mysqli_query($conn, $sql);
            $rm = mysqli_fetch_array($query);
            $idd = $rm["id"];
            $Type= $rm["Type"];
            if ($idd == "") {
                $rows = array("ErrorCode" => 10001);
                $json = json_encode($rows);
                echo $json;
            } else {
                if(ProductYesNo($Type) == "No"){  //如果没有未删除商品，才给删除这个类型
                $exec = "delete from `Menu` where `id`='{$ID}'";
                mysqli_query($conn, $exec);
                $rows = array("ErrorCode" => 0);
                $json = json_encode($rows);
                echo $json;
                }else{
                    $rows = array("ErrorCode" => 10020);
                    $json = json_encode($rows);
                    echo $json; 
                }
            }
        } else {
            $rows = array("ErrorCode" => 10000);
            $json = json_encode($rows);
            echo $json;
        }
    }
}

function _List()
{
    global $conn; //全局变量调用数据库连接
    $sql = "SELECT * FROM `Menu`   order by `id` desc";
    $query = mysqli_query($conn, $sql);
    while ($rm = mysqli_fetch_array($query)) {
        $id[] = $rm['id'];
    }

    if ($id == "") {
        $rows = array("ErrorCode" => 10001);
        $json = json_encode($rows);
        echo $json;
    } else {

        $rows = array();

        for ($i = 0; $i < count($id); $i++) {
            $sql = "SELECT * FROM `Menu`  WHERE `id` ='{$id[$i]}'";
            $query = mysqli_query($conn, $sql);
            $rm = mysqli_fetch_array($query);
            $Type[$i] = $rm["Type"];
            $idxx[$i] = $rm["id"];

            $rr = array("Type" => $Type[$i], "ID" => $idxx[$i]);
            array_push($rows, $rr);
        }
        $rows2 = array("ErrorCode" => 0, "Content" => $rows);
        $json = json_encode($rows2);
        echo $json;
    }
}

function Create($Type)
{

    global $conn; //全局变量调用数据库连接
    if (Token_Check() == true) {
        if ($Type <> "") {
            $sql = "SELECT * FROM `Menu`  WHERE `Type` = '{$Type}'";
            $query = mysqli_query($conn, $sql);
            $rm = mysqli_fetch_array($query);
            $Type33 = $rm["Type"];

            if ($Type33 == "") {

                $sql = "INSERT INTO `Menu` (`Type`)  VALUES ('{$Type}')";
                mysqli_query($conn, $sql);

                $rows = array("ErrorCode" => 0);
                $json = json_encode($rows);
                echo $json;
            } else {
                $rows = array("ErrorCode" => 10002);
                $json = json_encode($rows);
                echo $json;
            }
        } else {
            $rows = array("ErrorCode" => 10000);
            $json = json_encode($rows);
            echo $json;
        }
    }
}

CloseTable($conn); //关闭数据库
