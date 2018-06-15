<?php 
  include  "c.php";
  
  $wc = new wecat();

  $ac = $wc->getAccessToken();

  $wc->httpRequest();

?>
