<?php
  include "c.php";
  define('TOKEN','tokenValue');
  $wc = new wechat();
  if($_GET['echostr'])$wc->checkSignature();
  else $wc->responseMsg();
?>
