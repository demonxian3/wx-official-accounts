<?php

include("wechat.class.php");

$token = "yourToken";
$appID = "yourAppid";
$appsecret = "yourAppsecuret";

$wc = new Wechat($token, $appID, $appsecret);

if($_GET[echostr]){
    $res = $wc->chkSig();
    if($res)
        echo $res;
    else
        echo "check signature is failed";
}else{
    $wc->main();
}
?>
