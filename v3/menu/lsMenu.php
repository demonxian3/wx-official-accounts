<?php
    include("../WeChatClass.php");
    
    $appid = "wxdceea495fc7592d8";
    $appsecret = "c05b0b054d15c065700337701495503e";

    $wc = new WeChat("",$appid, $appsecret);

    $obj = $wc->lsMenu();


    echo "<pre>";
    print_r($obj);
    echo "</pre>";
?>

