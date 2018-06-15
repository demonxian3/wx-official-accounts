<?php 
	mysql_connect('localhost','root','yourpasswd');
	mysql_set_charset('utf8');
	mysql_select_db('weixin');
	$sql = "select * from chou where openid = '$_GET[openid]'";
	$res = mysql_query($sql);
	$row = mysql_fetch_assoc($res);
	$num = $row[num];
	$uid  = $row[id];
	if($_POST[uid]&&$_POST[num]){
		$n = $_POST[num];
		$i = $_POST[uid];
		$n = $n - 1;
		$sql="update chou set num = $n where id = $i";
		mysql_query($sql);
		echo "你还有".$n."次机会";
		exit;
	}
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>贤哥抽奖系统</title>
<script type="text/javascript" src="js/jquery-1.7.2-min.js"></script>
<script type="text/javascript" src="js/easing.js"></script>
<style>
*{color:white;}
html,body{margin:0;padding:0;overflow:hidden;}
body{background:url(images/body_bg.jpg) 0px 0px repeat-x #000;}
.main_bg{background:url(images/main_bg.jpg) top center no-repeat;height:1000px;}
.main{width:1000px;height:1000px;position:relative;margin:0 auto;}
.num_mask{background:url(images/num_mask.png) 0px 0px no-repeat;height:184px;width:740px;position:absolute;left:50%;top:340px;margin-left:-370px;z-index:9;}
.num_box{height:450px;width:750px;position:absolute;left:50%;top:340px;margin-left:-370px;z-index:8;overflow:hidden;text-align:center;}
.num{background:url(images/num.png) top center repeat-y;width:181px;height:265px;float:left;margin-right:6px;}
.btn{background:url(images/btn_start.png) 0px 0px no-repeat;width:264px;height:89px;position:absolute;left:50%;bottom:50px;margin-left:-132px;cursor:pointer;clear:both;}
.change{color: white;position: relative;top: 220px;text-align: center;font-size: 22px;}
</style>	
</head>
<body>
<div class="main_bg">
  <div class="main">
  	<div class="change" id="change">你还有<?php echo $num;?>次机会</div>
    <div id="res" style="text-align:center;color:#fff;padding-top:15px;"></div>
    <div class="num_mask"></div>
    <div class="num_box">
      <div class="num"></div>
      <div class="num"></div>
      <div class="num"></div>
      <div class="num"></div>
      <div class="btn"></div>
    </div>
  </div>
</div>
</body>
</html>
<script>
function numRand() {
	var x = 9999; //上限
	var y = 1111; //下限
	var rand = parseInt(Math.random() * (x - y + 1) + y);
	return rand;
}
var isBegin = false;
$(function(){
	var u = 265;
	$('.btn').click(function(){
		//ajax发送的参数
		var uid = "<?php echo $uid;?>";
		var num = "<?php echo $num;?>";
		var par="uid="+uid+"&num="+num;
		loadXMLDoc(par);


		if(isBegin) return false;
		isBegin = true;
		$(".num").css('backgroundPositionY',0);
		var result = numRand();
		//$('#res').text('摇奖结果 = '+result);
		var num_arr = (result+'').split('');
		$(".num").each(function(index){
			var _num = $(this);
			setTimeout(function(){
				_num.animate({ 
					backgroundPositionY: (u*60) - (u*num_arr[index])
				},{
					duration: 6000+index*3000,
					easing: "easeInOutCirc",
					complete: function(){
						if(index==3) isBegin = false;
						if(index==3) alert("你抽中的号码是"+result);
						
					}
				});
			}, index * 30);
		});
	});	
});

		function loadXMLDoc(par){
            		var xmlhttp;
                        xmlhttp=new XMLHttpRequest();   
                        xmlhttp.onreadystatechange=function(){
                          if (xmlhttp.readyState==4 && xmlhttp.status==200){
                            document.getElementById("change").innerHTML=xmlhttp.responseText;
                          }
                        }
                        xmlhttp.open("POST","index.php",true);
                        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
                        xmlhttp.send(par);
                }
</script>
