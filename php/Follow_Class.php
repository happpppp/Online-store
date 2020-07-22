<?php

include "conn.php"; //连接数据库

$Contentmy = GetPost();
foreach ($Contentmy as $k => $v) {
    $Mode = $v->Mode;
    $ID = $v->ID;
}

if (Token_Check() == true) {
    if ($Mode <> "") {
        switch ($Mode) {
            case "C":
                Create($ID);
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
        $Follow = $rm["Follow"];

        if ($Follow == "") {  //如果是空，那么没必要删除，直接报错10001
            $rows = array("ErrorCode" => 10001);
            $json = json_encode($rows);
            echo $json;
        } else {

            $Followarr = json_decode($Follow); //如果不是空，那么先转成数组
            foreach ($Followarr as $k => $v) {  //循环查找一下这个数组
                if ($v->ID == $ID) { //看看有没有ID等于这个要删除的ID
                    unset($Followarr[$k]);  //如果等于，就删除掉
                }
            }
            $Followarr2 = json_encode(array_values($Followarr)); //删除完以后，转回JSON
            if ($Followarr2 == "[]") { //再检查是不是最后一条，如果是的话，直接把中括号也删除了；
                $Followarr2 = "";
            }
            $sql = "UPDATE `User` SET `Follow`='{$Followarr2}'  where `UserID`= '{$UserID}' "; //存入数据库
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
    $Follow = $rm["Follow"];

    if ($Follow == "") {
        $rows = array("ErrorCode" => 10001);//如果收藏是空的，那么直接报错10001；
        $json = json_encode($rows);
        echo $json;
    } else {
     
        $rows = array();  //设置一个空的数组，用于作为返回值；
        $Followarr=json_decode($Follow);//把收藏夹转成数组
        foreach ($Followarr as $i=>$v) //循环查找
        {
            $sql = "SELECT * FROM `Product`  WHERE `id` ='{$v->ID}'";//循环取这个ID的商品信息
            $query = mysqli_query($conn, $sql);
            $rm = mysqli_fetch_array($query);
            $Type[$i] = $rm["Type"];
            $Price[$i] = $rm["Price"];
            $Name[$i] = $rm["Name"];
            $Introduction[$i] = $rm["Introduction"];
            $Image[$i] = $rm["Image"];
            $AddTime[$i] = $rm["AddTime"];

            $Del[$i] = $rm["Del"];
            if($Del[$i] == "true"){ //这里是判断Del是不是删除了，转成PHP的布尔值
                $Del[$i] = true;
            }else{
                $Del[$i] = false; 
            }

            $idxx[$i] = $rm["id"];
            
            $rr = array("Type" => $Type[$i],"Del" => $Del[$i], "Price" =>  floatval($Price[$i]), "Name" => $Name[$i], "Introduction" => $Introduction[$i], "Image" => $Image[$i], "AddTime" => $AddTime[$i], "ID" => $idxx[$i]);
            array_push($rows, $rr);
        }
        $rows2 = array("ErrorCode" => 0, "Content" => $rows);
        $json = json_encode($rows2);
        echo $json;
    }
}

function  Create($ID)
{
    global $conn; //全局变量调用数据库连接
    global $UserID; //通过全局变量拿到用户ID
    if ($ID <> "") {
        $sql = "SELECT * FROM `User` where `UserID`= '{$UserID}' ";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $Follow = $rm["Follow"];

        if ($Follow == "") {
        
            $Follow2='[{"ID":'.$ID.'}]';//如果是没有收藏，那么就直接增加一个；
            $sql = "UPDATE `User` SET `Follow`='{$Follow2}'  where `UserID`= '{$UserID}' ";
            mysqli_query($conn, $sql);

            $rows = array("ErrorCode" => 0);
            $json = json_encode($rows);
            echo $json;
        } else {
            if (strpos($Follow, $ID) == true) {  //判断有没有这个商品ID存在
                $rows = array("ErrorCode" => 100010); //有的话，就提示已存在；
                $json = json_encode($rows);
                echo $json;
            } else {

                $Followarr = json_decode($Follow); //如果是不存在，那么就先转换为数组
                $ID=array("ID" => floatval($ID)); //设置一个数组，就是你刚刚传进来的ID
                array_push($Followarr, $ID); //把新的ID添加到数组里来
                $Followarr2 = json_encode($Followarr);  //然后转回json存入数据库
                $sql = "UPDATE `User` SET `Follow`='{$Followarr2}'  where `UserID`= '{$UserID}' "; //存入数据库
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
