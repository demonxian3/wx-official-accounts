<?php
  include "c.php";
  define('TOKEN','xxx');
  $wc = new wechat();
  if($_GET['echostr'])$wc->checkSignature();
  else $wc->responseMsg();
?>
