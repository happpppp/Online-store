<?php

include "conn.php"; //连接数据库

$Time = date('Y-m-d H:i:s', time()); //得到现在的时间
$Password = $_GET["Password"]; //接收密码
$Mode = $_GET["Mode"]; //接收模式
$Phone = $_GET["Phone"]; //接收电话
$EMail = $_GET["EMail"]; //接收邮件号

$Portrait = $_FILES["Portrait"]; //文件格式接收头像

if (Token_Check() == true) {
    if ($Mode <> "") {
        global $UserID; //通过全局变量拿到用户ID //通过全局变量拿到用户ID
        if ($Portrait <> "") { //如果头像传有进来的话
            $Portrait = UploadImage($Portrait, $UserID, "Portrait"); //那么就上传头像
        }

        switch ($Mode) {
            case "U":
                Edit($Password, $Phone, $EMail, $Portrait); //调用修改函数
                break;
            case "R":
                Confirm(); //调用读取函数
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

function Confirm()
{
    global $conn; //全局变量调用数据库连接
    global $Time;
    global $UserID; //通过全局变量拿到用户ID
    $sql = "SELECT * FROM `User`  WHERE `UserID` = '{$UserID}'  ";
    $query = mysqli_query($conn, $sql);
    $rm = mysqli_fetch_array($query);
    $Phone = $rm["Phone"];
    $EMail = $rm["EMail"];
    $Portrait = $rm["Portrait"];
    $UserID = $rm["UserID"];

    $sql2 = "UPDATE `User` SET `LastTime` = '{$Time}' where `UserID`= '{$UserID}' ";
    mysqli_query($conn, $sql2); //修改为最后一次读取时间
    if ($Portrait == "") {
        $Portrait = null;
    }
    $rows = array("ErrorCode" => 0, "Phone" => $Phone, "EMail" => $EMail, "Portrait" => $Portrait, "UserID" => $UserID); //获取用户信息
    $json = json_encode($rows);
    echo $json;
}

function Edit($Password, $Phone, $EMail, $Portrait)
{
    global $conn; //全局变量调用数据库连接
    global $UserID; //通过全局变量拿到用户ID
    $Time = date('Y-m-d H:i:s', time());
    $sql = "SELECT * FROM `User`  WHERE `UserID` = '{$UserID}'";
    $query = mysqli_query($conn, $sql);
    $rm = mysqli_fetch_array($query);
    $ID = $rm["id"];  //看看是否能取到ID，来证明用户ID是否有效；
    $Phone3 = $rm["Phone"];
    $EMail3 = $rm["EMail"];
    $Portrait3 = $rm["Portrait"];
    $Password3 = $rm["Password"];
    if ($ID == "") {
        $rows = array("ErrorCode" => 10001); //找不到信息，报错，修改失败
        $json = json_encode($rows);
        echo $json;
    } else {
        if ($Password == "") {
            $Password = $Password3;
        }
        if ($Phone == "") {
            $Phone = $Phone3;
        }
        if ($EMail == "") {
            $EMail = $EMail3;
        }
        if ($Portrait == "") {
            $Portrait = $Portrait3;
        }

        $sql2 = "UPDATE `User` SET `LastTime` = '{$Time}',`Password` = '{$Password}',`Phone` = '{$Phone}',`EMail` = '{$EMail}',`Portrait` = '{$Portrait}' where `UserID`= '{$UserID}' ";
        mysqli_query($conn, $sql2); //修改用户数据
        if ($Portrait == "") {
            $Portrait = null;
        }
        $rows = array("ErrorCode" => 0, "Phone" => $Phone, "EMail" => $EMail, "Portrait" => $Portrait); //返回用户最新的信息
        $json = json_encode($rows);
        echo $json;
    }
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
            return "http://localhost/" . $move_to_file;  //自己记得修改自己的域名；
        } else {
            return "fail"; //上传失败，目录找不到
        }
    } else {
        return "fail"; //上传失败，文件没上传成功；
    }
}
CloseTable($conn); //关闭数据库 //关闭数据库
