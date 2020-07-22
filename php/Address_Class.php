<?php
//地址增删改查列
include "conn.php"; //连接数据库 

$Contentmy = GetPost(); //POST获取参数
foreach ($Contentmy as $k => $v) { //开始拿出POST数据
    $Name = $v->Name;  //获取名字
    $Phone = $v->Phone; //获取电话
    $Mode = $v->Mode; //获取模式
    $ID = $v->ID; //获取ID
    $Address = $v->Address; //获取地址
}

if (Token_Check() == true) { //判断有没有通过token
    if ($Mode <> "") { //判断模式是不是为空
        switch ($Mode) { //模式判断为什么模式
            case "C": //如果模式为C
                Create($Name, $Phone, $Address); //调用增加地址函数
                break; //不然的话
            case "R": //如果模式为R
                Retrieve($ID); //调用读取地址函数
                break; //不然的话
            case "U": //如果模式为U
                Edit($ID, $Name, $Phone, $Address); //调用修改地址函数
                break; //不然的话
            case "L": //如果模式为L
                _List(); //调用地址列表函数
                break; //不然的话
            case "D": //如果模式为D
                Delete($ID); //调用删除地址函数
                break; //不然的话
            default: //如果都不是以上模式
                $rows = array("ErrorCode" => 10009); //提示报错10009，表示模式传输不正确
                $json = json_encode($rows);
                echo $json;
        }
    } else {
        $rows = array("ErrorCode" => 10000); //提示报错10000，表示模式没有传输；
        $json = json_encode($rows);
        echo $json;
    }
}

function Delete($ID)
{
    global $conn; //全局变量调用数据库连接 //调用全局变量得到数据库连接
    if ($ID <> "") {
        $sql = "SELECT * FROM `Address`  WHERE `id` ='{$ID}'";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $idd = $rm["id"];
        if ($idd == "") { //查询有没有这个ID
            $rows = array("ErrorCode" => 10001); //报错，表示不存在这个ID，无法删除
            $json = json_encode($rows);
            echo $json;
        } else {
            $exec = "delete from `Address` where `id`='{$ID}'";
            mysqli_query($conn, $exec); //执行删除
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

function Edit($ID, $Name, $Phone, $Address)
{
    global $conn; //全局变量调用数据库连接//调用全局变量得到数据库连接
    global $UserID; //通过全局变量拿到用户ID
    if ($ID <> "") {
        $sql = "SELECT * FROM `Address`  WHERE `id` ='{$ID}'";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);

        if ($Name == "") {
            $Name = $rm["Name"];
        }
        if ($Phone == "") {
            $Phone = $rm["Phone"];
        }
        if ($Address == "") {
            $Address = $rm["Address"];
        }

        $sql = "UPDATE `Address` SET `Name`='{$Name}',`Phone`='{$Phone}',`Address`='{$Address}',`UserID`='{$UserID}' where `id`= '{$ID}' ";
        mysqli_query($conn, $sql);  //修改数据

        $rows = array("ErrorCode" => 0);
        $json = json_encode($rows);
        echo $json;
    } else {
        $rows = array("ErrorCode" => 10000);
        $json = json_encode($rows);
        echo $json;
    }
}

function _List()
{
    global $conn; //全局变量调用数据库连接//调用全局变量得到数据库连接
    global $UserID;
    $sql = "SELECT * FROM `Address`  WHERE `UserID` ='{$UserID}' order by `id` desc";
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
            $sql = "SELECT * FROM `Address`  WHERE `id` ='{$id[$i]}'";
            $query = mysqli_query($conn, $sql);
            $rm = mysqli_fetch_array($query);
            $Name[$i] = $rm["Name"];
            $Phone[$i] = $rm["Phone"];
            $Address[$i] = $rm["Address"];
            $UserID[$i] = $rm["UserID"];
            $idxx[$i] = $rm["id"];

            $rr = array("Name" => $Name[$i], "Phone" => $Phone[$i], "Address" => $Address[$i], "UserID" => $UserID[$i], "ID" => $idxx[$i]);
            array_push($rows, $rr);    //获取列表数据
        }
        $rows2 = array("ErrorCode" => 0, "Content" => $rows);
        $json = json_encode($rows2);
        echo $json;
    }
}

function Create($Name, $Phone, $Address)
{
    global $UserID; //通过全局变量拿到用户ID
    global $conn; //全局变量调用数据库连接
    if ($Name <> "") {
        $sql = "SELECT * FROM `Address`  WHERE `Name` = '{$Name}' and `Phone` = '{$Phone}' and `Address` = '{$Address}' and `UserID` = '{$UserID}'";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $ID = $rm["id"];   //查询有没有重复的

        if ($ID == "") { //如果没有，就给添加

            $sql = "INSERT INTO `Address` (`Name`,`Phone`,`Address`,`UserID`)  VALUES ('{$Name}','{$Phone}','{$Address}','{$UserID}')";
            mysqli_query($conn, $sql); //增加数据

            $rows = array("ErrorCode" => 0);
            $json = json_encode($rows);
            echo $json;
        } else {
            $rows = array("ErrorCode" => 10002); //有重复，不给添加，直接报错；
            $json = json_encode($rows);
            echo $json;
        }
    } else {
        $rows = array("ErrorCode" => 10000);
        $json = json_encode($rows);
        echo $json;
    }
}


function Retrieve($ID)
{
    global $conn; //全局变量调用数据库连接
    if ($ID <> "") {
        $sql = "SELECT * FROM `Address`  WHERE `id` = '{$ID}'";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $Name = $rm["Name"];

        if ($Name == "") {
            $rows = array("ErrorCode" => 10001);
            $json = json_encode($rows);
            echo $json;
        } else {

            $Phone = $rm["Phone"];
            $Address = $rm["Address"];
            $idxx = $rm["id"];

            $rows2 = array("Address" => $Address, "Phone" => $Phone, "Name" => $Name, "ID" => $idxx);
            $json = json_encode($rows2); //读取数据
            echo $json;
        }
    } else {
        $rows = array("ErrorCode" => 10000);
        $json = json_encode($rows);
        echo $json;
    }
}



CloseTable($conn); //关闭数据库
