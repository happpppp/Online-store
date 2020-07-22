<?php

include "conn.php"; //连接数据库

$Contentmy = GetPost();
foreach ($Contentmy as $k => $v) {
    $Mode = $v->Mode;
    $ID = $v->ID;
    $Num = $v->Num;
}

if (Token_Check() == true) {
    if ($Mode <> "") {
        switch ($Mode) {
            case "C":
                Create($ID, $Num);
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
}

function Delete($ID)
{
    global $conn; //全局变量调用数据库连接
    global $UserID; //通过全局变量拿到用户ID
    if ($ID <> "") {
        $sql = "SELECT * FROM `User`  WHERE `UserID` = '{$UserID}'  ";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $Cart = $rm["Cart"];

        if ($Cart == "") { //如果是空，那么没必要删除，直接报错10001
            $rows = array("ErrorCode" => 10001);
            $json = json_encode($rows);
            echo $json;
        } else {

            $Cartarr = json_decode($Cart); //如果不是空，那么先转成数组
            foreach ($Cartarr as $k => $v) //循环查找一下这个数组
            {
                if ($v->ID == $ID) { //看看有没有ID等于这个要删除的ID
                    unset($Cartarr[$k]); //如果等于，就删除掉
                }
            }
            $Cartarr2 = json_encode(array_values($Cartarr)); //删除完以后，转回JSON
            if ($Cartarr2 == "[]") { //再检查是不是最后一条，如果是的话，直接把中括号也删除了；
                $Cartarr2 = "";
            }
            $sql = "UPDATE `User` SET `Cart`='{$Cartarr2}'  where `UserID`= '{$UserID}' "; //存入数据库
            mysqli_query($conn, $sql);

            $rows = array("ErrorCode" => 0);
            $json = json_encode($rows);
            echo $json;
        }
    } else {
        $rows = array("ErrorCode" => 10000);
        $json = json_encode($rows);
        echo $json;
    }
}


function _List()
{
    global $conn; //全局变量调用数据库连接
    global $UserID; //通过全局变量拿到用户ID
    $sql = "SELECT * FROM `User` where `UserID`= '{$UserID}' ";
    $query = mysqli_query($conn, $sql);
    $rm = mysqli_fetch_array($query);
    $Cart = $rm["Cart"];

    if ($Cart == "") {
        $rows = array("ErrorCode" => 10001); //如果购物车是空的，那么直接报错10001；
        $json = json_encode($rows);
        echo $json;
    } else {

        $rows = array();  //设置一个空的数组，用于作为返回值；
        $Cartarr = json_decode($Cart); //把购物车转成数组
        foreach ($Cartarr as $k => $v) //循环查找
        {
            $sql = "SELECT * FROM `Product`  WHERE `id` ='{$v->ID}'"; //循环取这个ID的商品信息
            $query = mysqli_query($conn, $sql);
            $rm = mysqli_fetch_array($query);
            $Type[$k] = $rm["Type"];
            $Price[$k] = $rm["Price"];
            $Name[$k] = $rm["Name"];
            $Introduction[$k] = $rm["Introduction"];
            $Image[$k] = $rm["Image"];
            $AddTime[$k] = $rm["AddTime"];
            $idxx[$k] = $rm["id"];
            $Del[$k] = $rm["Del"];
            if ($Del[$k] == "true") { //这里是判断Del是不是删除了，转成PHP的布尔值
                $Del[$k] = true;
            } else {
                $Del[$k] = false;
            }

            $rr = array("Num" => $v->Num, "Del" => $Del[$k], "Type" => $Type[$k], "Price" =>  floatval($Price[$k]), "Name" => $Name[$k], "Introduction" => $Introduction[$k], "Image" => $Image[$k], "AddTime" => $AddTime[$k], "ID" => $idxx[$k]);
            array_push($rows, $rr);
        }

        $rows2 = array("ErrorCode" => 0, "Content" => $rows);
        $json = json_encode($rows2);
        echo $json;
    }
}

function Create($ID, $Num)
{
    global $conn; //全局变量调用数据库连接
    global $UserID; //通过全局变量拿到用户ID
    if ($ID <> "") {
        $sql = "SELECT * FROM `User` where `UserID`= '{$UserID}' ";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $Cart = $rm["Cart"];

        if ($Cart == "") { //判断有没有购物车信息

            $Cart2 = '[{"ID":' . $ID . ',"Num":' . $Num . '}]';  //如果是没有任何购物车信息，就直接添加一条；

            $sql = "UPDATE `User` SET `Cart`='{$Cart2}'  where `UserID`= '{$UserID}' ";
            mysqli_query($conn, $sql);

            $rows = array("ErrorCode" => 0);
            $json = json_encode($rows);
            echo $json;
        } else {

            $Cartarr = json_decode($Cart);  //把购物车转成数组

            foreach ($Cartarr as $k => $v) //循环查找，这个ID存在购物车吗
            {
                if ($v->ID == $ID) { //如果存在
                    $v->Num = $v->Num + $Num; //如果存在，直接增加数量进去；
                    $IDExistence = "existence";  //设置一个变量，标志这个ID已存在；
                }
            }

            if ($IDExistence == "existence") { //判断有没有存在这个ID
                //如果存在，就直接保存增加数量的进来；
                $Cartarr2 = json_encode($Cartarr); //再转回JSON然后存入数据库
                $sql = "UPDATE `User` SET `Cart`='{$Cartarr2}'  where `UserID`= '{$UserID}' "; //存入数据库
                mysqli_query($conn, $sql);

                $rows = array("ErrorCode" => 0);
                $json = json_encode($rows);
                echo $json;
            } else {

                //如果不存在这个ID，那么就增加一个数组；
                $ID = array("ID" => floatval($ID), "Num" => floatval($Num)); //设置一个数组，就是你刚刚传进来的ID和数量
                array_push($Cartarr, $ID);     //导入这个新的商品到数组；
                $Cartarr2 = json_encode($Cartarr); //再转回JSON然后存入数据库


                $sql = "UPDATE `User` SET `Cart`='{$Cartarr2}'  where `UserID`= '{$UserID}' "; //存入数据库
                mysqli_query($conn, $sql);

                $rows = array("ErrorCode" => 0);
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

CloseTable($conn); //关闭数据库
