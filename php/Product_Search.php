<?php

include "conn.php"; //连接数据库

$SearchText = $_GET["Text"]; //接收搜索内容

$sql = "SELECT * FROM `Product` where CONCAT(IFNULL(`Type`,''),IFNULL(`Price`,''),IFNULL(`Name`,''),IFNULL(`Introduction`,'')) like '%{$SearchText}%'  order by `id` desc"; //查询符合的数据
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
    for ($i = 0; $i < count($id); $i++) { //循环获取数据
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

CloseTable($conn); //关闭数据库
