<?php

include "conn.php"; //连接数据库

$Mode = $_GET["Mode"]; //接收模式
$ID = $_GET["ID"]; //接收ID
$Type = $_GET["Type"]; //接收类型
$Price = $_GET["Price"]; //接收价格
$Name = $_GET["Name"]; //接收名称
$Introduction = $_GET["Introduction"]; //接收简介
$Image = $_FILES["Image"]; //文件格式获取图片
global $UserID;

//判断Mode的模式，C是增加，R是读取，U是修改，L是列表，D是删除
if ($Mode <> "") {
    switch ($Mode) {
        case "C":
            Create($Type, $Price, $Name, $Introduction, $Image);
            break;
        case "R":
            Retrieve($ID);
            break;
        case "U":
            Edit($ID, $Type, $Price, $Name, $Introduction, $Image);
            break;
        case "L":
            _List($Type);
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

function Delete($ID)
{
    global $conn; //全局变量调用数据库连接
    if (Token_Check() == true) {
        if ($ID <> "") {
            $sql = "SELECT * FROM `Product`  WHERE `id` ='{$ID}'";
            $query = mysqli_query($conn, $sql);
            $rm = mysqli_fetch_array($query);
            $idd = $rm["id"];
            if ($idd == "") {
                $rows = array("ErrorCode" => 10001); //不存在这个ID
                $json = json_encode($rows);
                echo $json;
            } else {
                $sql = "UPDATE `Product` SET `Del`='true' where `id`= '{$ID}' ";
                mysqli_query($conn, $sql); //删除商品
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
}

function Edit($ID, $Type, $Price, $Name, $Introduction, $Image)
{
    global $conn; //全局变量调用数据库连接


    if (Token_Check() == true) {
        global $UserID; //通过全局变量拿到用户ID
        if ($Image <> "") {
            $Image = UploadImage($Image, "Product"); //如果有图片传进来的，就上传到服务器；
        }
        if ($ID <> "") {
            $sql = "SELECT * FROM `Product`  WHERE `id` ='{$ID}'";
            $query = mysqli_query($conn, $sql);
            $rm = mysqli_fetch_array($query);

            if ($Type == "") {
                $Type = $rm["Type"];
            }
            if ($Price == "") {
                $Price = $rm["Price"];
            }
            if ($Name == "") {
                $Name = $rm["Name"];
            }
            if ($Introduction == "") {
                $Introduction = $rm["Introduction"];
            }
            if ($Image == "") {
                $Image = $rm["Image"];
            }

            $sql = "UPDATE `Product` SET `Type`='{$Type}',`Price`='{$Price}',`Name`='{$Name}',`Introduction`='{$Introduction}',`Image`='{$Image}',`Admin`='{$UserID}' where `id`= '{$ID}' ";
            mysqli_query($conn, $sql); //修改商品信息

            $rows = array("ErrorCode" => 0);
            $json = json_encode($rows);
            echo $json;
        } else {
            $rows = array("ErrorCode" => 10000);
            $json = json_encode($rows);
            echo $json;
        }
    }
}

function _List($Type)
{
    global $conn; //全局变量调用数据库连接

    if ($Type <> "") {
        $sql = "SELECT * FROM `Product` WHERE `Type` = '{$Type}' and  `Del` <> 'true'  order by `id` desc";
    } else {
        $sql = "SELECT * FROM `Product`  WHERE  `Del` <> 'true' order by `id` desc";
    }
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
            $sql = "SELECT * FROM `Product`  WHERE `id` ='{$id[$i]}'";
            $query = mysqli_query($conn, $sql);
            $rm = mysqli_fetch_array($query);
            $Type2[$i] = $rm["Type"];
            $Price[$i] = $rm["Price"];
            $Name[$i] = $rm["Name"];
            $Introduction[$i] = $rm["Introduction"];
            $Image[$i] = $rm["Image"];
            $AddTime[$i] = $rm["AddTime"];
            $idxx[$i] = $rm["id"];

            $rr = array("Type" => $Type2[$i], "Price" => floatval($Price[$i]), "Name" => $Name[$i], "Introduction" => $Introduction[$i], "Image" => $Image[$i], "Time" => $AddTime[$i], "ID" => $idxx[$i]);
            array_push($rows, $rr); //得到商品列表
        }
        $rows2 = array("ErrorCode" => 0, "Content" => $rows);
        $json = json_encode($rows2);
        echo $json;
    }
}

function Create($Type, $Price, $Name, $Introduction, $Image)
{

    global $conn; //全局变量调用数据库连接
    $Time = date('Y-m-d H:i:s', time()); //定义一个时间
    if (Token_Check() == true) {
        global $UserID; //通过全局变量拿到用户ID
        if ($Image <> "") {
            $Image = UploadImage($Image, "Product"); //如果有图片传进来的，就上传到服务器；
        }
        $sql = "SELECT * FROM `Menu`  WHERE `Type` = '{$Type}'";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $Type = $rm["Type"]; //判断这个类型是否存在
        if ($Type == "") {     //不存在就不给添加
            $rows = array("ErrorCode" => 10003);
            $json = json_encode($rows);
            echo $json;
        } else {
            if ($Name <> "" and $Price <> "" and $Image <> "") {  //必须要有名称，价格，图片才能继续添加
                $sql = "SELECT * FROM `Product`  WHERE `Admin` = '{$UserID}' and `Name` = '{$Name}' and `Type` = '{$Type}' and `Price` = '{$Price}' and `Introduction` = '{$Introduction}'";
                $query = mysqli_query($conn, $sql);
                $rm = mysqli_fetch_array($query);
                $ID = $rm["id"]; //查询这个商品是否存在

                if ($ID == "") { //如果不存在，才添加

                    $sql = "INSERT INTO `Product` (`Admin`,`Type`,`Price`,`Introduction`,`Image`,`Name`,`AddTime`)  VALUES ('{$UserID}','{$Type}','{$Price}','{$Introduction}','{$Image}','{$Name}','{$Time}')";
                    mysqli_query($conn, $sql); //增加商品

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
}

function Retrieve($ID)
{
    global $conn; //全局变量调用数据库连接
    if ($ID <> "") {

        $sql = "SELECT * FROM `Product`  WHERE `id` = '{$ID}'";
        $query = mysqli_query($conn, $sql);
        $rm = mysqli_fetch_array($query);
        $Name = $rm["Name"];

        if ($Name == "") {
            $rows = array("ErrorCode" => 10001);
            $json = json_encode($rows);
            echo $json;
        } else {
            $Type = $rm["Type"];
            $Price = $rm["Price"];
            $Introduction = $rm["Introduction"];
            $Image = $rm["Image"];
            $AddTime = $rm["AddTime"];
            $idxx = $rm["id"];

            $rows2 = array("ErrorCode" => 0, "Type" => $Type, "Price" => floatval($Price), "Name" => $Name, "Introduction" => $Introduction, "Image" => $Image, "AddTime" => $AddTime, "ID" => $idxx);
            $json = json_encode($rows2); //读取这个商品的信息
            echo $json;
        }
    } else {
        $rows = array("ErrorCode" => 10000);
        $json = json_encode($rows);
        echo $json;
    }
}

function UploadImage($Image, $Type) //上传图片函数
{
    if (is_uploaded_file($Image['tmp_name'])) {
        //把文件转存到你希望的目录（不要使用copy函数）
        $uploaded_file = $Image['tmp_name'];
        $username = "../Img/Product";
        //我们给每个用户动态的创建一个文件夹

        $user_path = $username;
        //mkdir($user_path,0777,true);

        //判断该用户文件夹是否已经有这个文件夹
        if (!file_exists($user_path)) {
            mkdir($user_path);
        }

        //$move_to_file=$user_path."/".$_FILES['file']['name'];
        $file_true_name = $Image['name'];
        $move_to_file = $user_path . "/" . $Type . date('YmdHis', time())   . substr($file_true_name, strrpos($file_true_name, "."));
        if (move_uploaded_file($uploaded_file, iconv("utf-8", "gb2312", $move_to_file))) {
            $move_to_file = str_replace("../", "", $move_to_file);
            return "http://localhost/" . $move_to_file;   //自己记得修改自己的域名；这样才能确保上传你的域名
        } else {
            return "fail2222"; //上传失败，文件没上传成功；
        }
    } else {
        return "fail"; //上传失败，文件没上传成功；
    }
}

CloseTable($conn); //关闭数据库
