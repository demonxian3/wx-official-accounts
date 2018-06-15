<?php
  include "./c.php";
  $wc = new wechat();
  $ac = $wc->getAccessToken();
  $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$ac";
  $json = '{
     "expire_seconds": 20160, 
     "action_name": "QR_SCENE", 
     "action_info": {"scene": {"scene_id": 2225}}
  }';

  $return = $wc->httpRequest($url,$json);
  $ticket = $return['ticket'];
  $tc = urlencode($ticket); 
 
?>

 <script>
   document.location="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=<?php echo $tc?>";
 </script>
<body>
</body>
