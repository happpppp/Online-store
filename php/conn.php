<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers:*');
//数据库链接

$dbname = 'test';
$servername = "localhost";
$username = "Test";
$password = "7samx3RNsDfeTLAe";

$conn = new mysqli($servername, $username, $password, $dbname,3308);

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit();

// 返回内容
function Result($json)
{
    echo json_encode($json);
}

// Token错误码配置
$Token_Code = [
    1400 => ['ErrorCode' => 1400], //Token错误
    1401 => ['ErrorCode' => 1401], //Token过期
    1403 => ['ErrorCode' => 1403], //Token签名错误
];
// Token密钥 | 头部
$Token_Secret = 'ant';
$Token_Header = base64_url(["alg" => "HS256", "typ" => "JWT"], true);

// 创建Token
function Token_Create($Account)
{
    global $Token_Header, $Token_Secret;

    // 生成有效期
    $Time_Start = strtotime(date('Y-m-d H:i:s', strtotime("-1 Minute")));
    $Time_End = strtotime(date("Y-m-d H:i:s", strtotime("+7 day")));

    // 生成中部 iat//签发时间(时间戳)  exp //过期时间(时间戳)  sub 用户ID
    $Payload = ["iat" => $Time_Start, "exp" => $Time_End, "sub" => $Account];
    $Payload = base64_url($Payload, true);

    // 拼接前两段
    $Splice = $Token_Header . "." . $Payload;

    //生成签名
    $Signature = hash_hmac('sha256', $Splice, $Token_Secret, true);
    $Signature = base64_url($Signature, false);

    // 拼接并返回Token
    return $Token_Header . "." . $Payload . "." . $Signature;
}


// 解密Token
function Token_Check()
{
    global $Token_Header, $Token_Secret, $Token_Code;
    // 当前的时间
    $Time = strtotime(date('Y-m-d H:i:s', time()));
    $Token = $_SERVER['HTTP_AUTHORIZATION'];
    $Token = explode(".", $Token);

    // 验证头部
    if ($Token_Header != $Token[0]) return Result($Token_Code[1400]);

    // 收到的中部
    $Token_Payload = json_decode(base64_decode($Token[1]), true);

    // 验证中部是否在有效期中
    if ($Time < $Token_Payload['exp'] and $Time > $Token_Payload['iat']) {

        //拼接头部和中部后进行加密
        $Signature = hash_hmac('sha256', $Token[0] . "." . $Token[1], $Token_Secret, true);
        //把加密后的结果Base64编码
        $Signature = base64_url($Signature, false);

        //判断尾部是否相同
        if ($Signature == $Token[2]) {
            global $UserID; //设置全局变量
            $UserID =  $Token_Payload['sub'];
            return true;
        } else return Result($Token_Code[1402]);
    } else return Result($Token_Code[1401]); // 已过期
}

function base64_url($text, bool $json)
{
    if ($json) $text = json_encode($text);

    $text = base64_encode($text);
    $text = str_replace("=", "", $text);
    $text = str_replace("+", "-", $text);
    $text = str_replace("/", "_", $text);
    return $text;
}

function CloseTable($conn)
{
    mysqli_close($conn);
}

function GetPost()
{
    $Content = file_get_contents('php://input');
    $Content = "[" . $Content . "]";
    $Content = json_decode($Content);

    return $Content;
}
