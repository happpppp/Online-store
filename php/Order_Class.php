<?php

include "conn.php"; //连接数据库

$Contentmy = GetPost();
foreach ($Contentmy as $k => $v) {
    $Mode = $v->Mode;
    $ProductList = $v->ProductList;
    $ID = $v->ID;
    $Contact_ID = $v->Contact_ID;
}

if (Token_Check() == true) {
    if ($Mode <> "") {
        switch ($Mode) {
            case "C":
                Create($Contact_ID, $ProductList);
                break;
            case "R":
                Retrieve($ID);
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
    if ($ID <> "") {
        $sql = "SELECT * FROM `Order`  WHERE `id` ='{$ID}'";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $idd = $rm["id"];
        if ($idd == "") {
            $rows = array("ErrorCode" => 10001);
            $json = json_encode($rows);
            echo $json;
        } else {
            $exec = "delete from `Order` where `id`='{$ID}'";
            mysqli_query($conn, $exec);
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
    $sql = "SELECT * FROM `Order` WHERE `UserID` ='{$UserID}'   order by `id` desc";
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
            $sql = "SELECT * FROM `Order`  WHERE `id` ='{$id[$i]}'";
            $query = mysqli_query($conn, $sql);
            $rm = mysqli_fetch_array($query);
            $UserID2[$i] = $rm["UserID"];
            $OrderID[$i] = $rm["OrderID"];
            $Contact_Address[$i] = $rm["Contact_Address"];
            $Contact_Name[$i] = $rm["Contact_Name"];
            $Contact_Phone[$i] = $rm["Contact_Phone"];
            $ProductList[$i] =  getProductMessage($rm["ProductList"]);
            $Total[$i] = $rm["Total"];
            $Time[$i] = $rm["Time"];
            $idxx[$i] = $rm["id"];

            $rr = array("UserID" => $UserID2[$i], "OrderID" => $OrderID[$i], "Contact_Address" => $Contact_Address[$i], "Contact_Name" => $Contact_Name[$i], "Contact_Phone" => $Contact_Phone[$i], "ProductList" => $ProductList[$i], "Total" => floatval($Total[$i]), "Time" => $Time[$i], "ID" => $idxx[$i]);
            array_push($rows, $rr);
        }
        $rows2 = array("ErrorCode" => 0, "Content" => $rows);
        $json = json_encode($rows2);
        echo $json;
    }
}

function getProductMessage($ProductList)
{ //获取商品信息函数
    global $conn; //全局变量调用数据库连接
    $rows = array();  //设置一个空的数组，用于作为返回值；
    if (strpos($ProductList, '[') !== false) { //判断是否有中括号，因为没有中括号的话，就不算数组；

    } else {
        $ProductList = "[" . $ProductList . "]"; //如果没有中括号，PHP就帮助加上；
    }

    $ProductListarr = json_decode($ProductList); //把购物车转成数组
    foreach ($ProductListarr as $k => $v) //循环查找
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
    return $rows;
}


function getTotal($ID)
{ //计算总价函数
    global $conn; //全局变量调用数据库连接
    $sql = "SELECT * FROM `Product`  WHERE `id` = '{$ID}'";
    $query = mysqli_query($conn, $sql);
    $rm = mysqli_fetch_array($query);
    $Price = $rm["Price"];
    return $Price;
}

function Create($Contact_ID, $ProductList)
{

    global $conn; //全局变量调用数据库连接
    global $UserID; //通过全局变量拿到用户ID
    if ($Contact_ID <> "" and  $ProductList <> "") {
        $sql = "SELECT * FROM `Address`  WHERE `id` = '{$Contact_ID}'";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $Contact_Name = $rm["Name"];
        $Contact_Phone = $rm["Phone"];
        $Contact_Address = $rm["Address"];

        $shijian = date('ymdhis', time());
        $suijishu = rand(0, 4000);
        $OrderID = "020$shijian$suijishu";

        $Time = date('Y-m-d H:i:s', time());
        $Total = 0;


        if ($Contact_Name <> "") { //这个地址ID存在，才给下单；
            if (strpos($ProductList, '[') !== false) { //判断是否有中括号，因为没有中括号的话，就不算数组；
                $ProductList2 = $ProductList; //如果已有中括号，PHP就直接赋值；因为数据库要存入原型ProductList，要转成数组操作这里就给一个ProductList2作为临时赋值后数组操作
            } else {
                $ProductList2 = "[" . $ProductList . "]"; //如果没有中括号，PHP就帮助加上；
            }

            $ProductList2 = json_decode($ProductList2);

            foreach ($ProductList2 as $k => $v) {
                if (getTotal($v->ID) == "") {
                    $rows = array("ErrorCode" => 1000111); //有不存在的商品ID
                    $json = json_encode($rows);
                    echo $json;
                    exit;
                } else {
                    $Total = $Total + getTotal($v->ID) * $v->Num;
                    DeleteCart($v->ID); //下单成功后，删除购物车里的商品
                }
            }

            $sql = "INSERT INTO `Order` (`OrderID`,`Contact_Name`,`Contact_Phone`,`Contact_Address`,`UserID`,`ProductList`,`Total`,`Time`)  VALUES ('{$OrderID}','{$Contact_Name}','{$Contact_Phone}','{$Contact_Address}','{$UserID}','{$ProductList}','{$Total}','{$Time}')";
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

function DeleteCart($ID)
{ //下单成功后，删除购物车里的商品
    global $conn; //全局变量调用数据库连接
    global $UserID; //通过全局变量拿到用户ID
    if ($ID <> "") {
        $sql = "SELECT * FROM `User`  WHERE `UserID` = '{$UserID}'  ";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $Cart = $rm["Cart"];

        if ($Cart == "") { //如果是空，那么没必要删除，直接报错10001

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
        }
    } else {
    }
}

function Retrieve($ID)
{
    global $conn; //全局变量调用数据库连接
    if ($ID <> "") {

        $sql = "SELECT * FROM `Order`  WHERE `id` = '{$ID}'";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $UserID = $rm["UserID"];

        if ($UserID == "") {
            $rows = array("ErrorCode" => 10001);
            $json = json_encode($rows);
            echo $json;
        } else {
            $OrderID = $rm["OrderID"];
            $Contact_Address = $rm["Contact_Address"];
            $Contact_Name = $rm["Contact_Name"];
            $Contact_Phone = $rm["Contact_Phone"];

            $ProductList = getProductMessage($rm["ProductList"]);
            $Total = $rm["Total"];
            $Time = $rm["Time"];
            $idxx = $rm["id"];

            $rows2 = array("OrderID" => $OrderID, "Contact_Address" => $Contact_Address, "Contact_Name" => $Contact_Name, "Contact_Phone" => $Contact_Phone, "ProductList" => $ProductList, "Total" => floatval($Total), "Time" => $Time, "ID" => $idxx);
            $json = json_encode($rows2);
            echo $json;
        }
    } else {
        $rows = array("ErrorCode" => 10000);
        $json = json_encode($rows);
        echo $json;
    }
}

CloseTable($conn); //关闭数据库
