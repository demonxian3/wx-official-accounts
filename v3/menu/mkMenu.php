<?php
    include("../WeChatClass.php");
    
    $appid = "wxdceea495fc7592d8";
    $appsecret = "c05b0b054d15c065700337701495503e";

    $wc = new WeChat("",$appid, $appsecret);

    $menuJson = ' {
        "button":[ {    
          "type":"click",
          "name":"功能说明",
          "key":"info"
       },
       {
          "name":"菜单",
          "sub_button":[ {    
             "type":"click",
             "name":"成绩排名",
             "key":"rank"
          },
          {
             "type":"view",
             "name":"博客",
             "url":"http://cnblogs.com/demonxian3"
          },
          {
             "type":"click",
             "name":"支持一下",
             "key":"support"
          }]
       }]
    }';
    if($wc->mkMenu($menuJson)){
        echo "<script> alert('create menu sucessfully')</script>";
    }else{
        echo "<script> alert('create menu failed')</script>";
    }
?>

