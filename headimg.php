<?php
	mysql_connect("localhost:3306","root","yourpasswd");
	mysql_set_charset("utf8");
	mysql_select_db("weixin");

	$sql="select headimgurl from wcuser";
	$res=mysql_query($sql);

	if($_POST['change']){
	while($row = mysql_fetch_assoc($res)){
             $str.=' <div class="par">
             <div class="div">
             <img src='.$row[headimgurl].' />
             </div>
             </div>';
        }
        echo $str;
	exit;
        }

?>

<html>

	<head>
		<meta charset="UTF-8">
		<title>扩散</title>
		<style>
			body{
				padding:0px;
				margin:0px;
				background: #99CC99;
			}
			
			.title{
				top: 5%;
				position: absolute;
				width: 100%;
				height: 8%;
				font-family: 楷体;
				font-size: 45px;
				color: #006633;
				text-align: center;
				line-height: 50px;
			}
			
			.boss{
				left: 0.8%;
				position: absolute;
				width: 98%;
				height: 80%;
				border: 3px dashed #006633;
				top: 16%;
				background: #CCFFCC;
			}
			
			.par{
				width:130px;
				height: 130px;
				overflow: hidden;
				float: left;
				margin: 10px;
				border-radius:100px ;
				
			}
			
			.div{
				position: relative;
				transition: left 2s;
			}
			
			img{
				float: left;
				width:  130px;
				height: 130px;
				transition: all 0.6s;
				border: none;
				border-radius:100px ;
			}
			
			
			img:hover{
				margin-left:-12px ;
				margin-top:-12px ;
				width: 160px;
				height: 160px;
				border-radius:100px!important ;
				opacity: 0.8;
			}
		</style>
		
	</head>

	<body>
		<div class="title">
			微信头像墙
		</div>
			
		<div class="boss">
			<?php
				while($row = mysql_fetch_assoc($res)){
				$str.='	<div class="par">
                        		<div class="div">
                                	<img src='.$row[headimgurl].' />
                        		</div>
                        		</div>';
				}
				echo $str;
				
			?>
		</div>
		<script>
			var div = document.getElementById('boss');
			setInterval(loadXMLDoc,5000);

			function loadXMLDoc(){
              		   var xmlhttp;
              		   xmlhttp=new XMLHttpRequest();   
              		   xmlhttp.onreadystatechange=function(){
              		      if (xmlhttp.readyState==4 && xmlhttp.status==200){
              		         document.getElementById("main").innerHTML=xmlhttp.responseText;
              		      }
              		   }
              		   xmlhttp.open("POST","head.php",true);
              		   xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
              		   xmlhttp.send("change=1");
         		}

		</script>		
	</body>

</html>
