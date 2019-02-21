<?php

include("WechatClass.php");
include("HandleClass.php");

$token = "demonxian3";
$appID = "wxdceea495fc7592d8";
$appsecret = "c05b0b054d15c065700337701495503e";

$wc = new Wechat($token, $appID, $appsecret);
$hd = new Handle();

if($_GET[echostr]){
    $res = $wc->chkSig();
    if($res)
        echo $res;
    else
        echo "check signature is failed";
}

$postObj = $wc->main();
$userObj = $wc->getUserInfo($postObj);

$hd->setUserInfo($userObj);
$resuArr = $hd->main($postObj);

switch($resuArr[0]){
    case 'text':
        $wc->sendText($postObj, $resuArr[1]);
        break;

    case 'image':
        $wc->sendImage($postObj, $resuArr[1]);
        break;

    case 'music':
        $wc->sendMusic($postObj, $resuArr[1]);
        break;

    case 'video':
        $wc->sendVideo($postObj, $resuArr[1]);
        break;

    case 'news':
        $wc->sendNews($postObj, $resuArr[1]);
        break;

    case 'robot':
        $wc->sendRotbot($postObj);
        break;
}
    
?>
