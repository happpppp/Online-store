<!-- 此文件为测试时的文件，请勿理会 -->

<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers:*');
echo Token_Create("as5081523");

function Token_Create($YQBID)
{
    $TokenSecret = "Ant";
    //生成头部
    $Header = array("alg" => "HS256", "typ" => "JWT");
    $Header = json_encode($Header);
    $Header = base64_encode($Header);

    //生成头部end
    $Time = date('Y-m-d H:i:s', time());
    $Time = strtotime($Time);

    $Time2 = date("Y-m-d H:i:s", strtotime("+3 minute"));
    $Time2 = strtotime($Time2);

    //生成中部 iat//签发时间(时间戳)  exp //过期时间(时间戳)  nbf //该时间之前不接收处理该Token(时间戳)   sub 用户ID
    $Payload = array("iat" => $Time, "exp" => $Time2, "nbf" => $Time, "sub" => $YQBID);
    $Payload = json_encode($Payload);
    $Payload = base64_encode($Payload);

    //生成中部end

    $Splicing = $Header . "." . $Payload;
    $Splicing = str_replace("=", "", $Splicing);
    $Splicing = str_replace("+", "-", $Splicing);
    $Splicing = str_replace("/", "_", $Splicing);

    //生成拼接
    $Splicing = hash_hmac('sha256', $Splicing, $TokenSecret, true);
    $Splicing = base64_encode($Splicing);
    //生成拼接end

    $Token = $Header . "." . $Payload . "." . $Splicing;
    //生成token;

    //替换特殊字符
    $Token = str_replace("=", "", $Token);
    $Token = str_replace("+", "-", $Token);
    $Token = str_replace("/", "_", $Token);
    //替换特殊字符end;
    return $Token;
}