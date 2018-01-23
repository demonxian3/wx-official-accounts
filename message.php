<?php
    mysql_connect("localhost:3306","root","yourpasswd");
    mysql_set_charset('utf8');
    mysql_select_db('weixin');
    $sql="select nickname,headimgurl,message from wcuser,message where message.openid = wcuser.openid order by message.id desc limit 7";
    $res=mysql_query($sql);

    if($_POST['ajax']){
	while($row = mysql_fetch_assoc($res)){
            $item.=  '<div class="item">
            <div class="img"><img src='.$row[headimgurl].'/></div>
            <div class="content">'.$row[nickname].':'.$row[message].'</div>
            </div>';
        } 
        echo $item;
	exit;
    }
?>

<head>
	<meta charset="utf-8" />
	<style>
		body{
			padding: 0px;
			margin: 0px;
			background: #003333;			
		}
		
		
		.title{
			position: absolute;
			width: 99%;
			top:7%;
			color: wheat;
			font-size: 50px;
			font-family: 楷体;
			line-height: 30px;
			text-align: center;
		}
		
		.main{
			border-radius:30px ;
			border: 1px solid white;
			position: absolute;
			top: 17%;
			left: 15%;
			width: 70%;
			height: 550px;
			background: cadetblue;
			overflow: hidden;
		}
		
		.item{
			border-bottom:5px double green;
			width: 100%;
			height: 75px;
			overflow: hidden;
		}
		
		.img img{
			margin-left: 45px;
			margin-top: 3px;
			border: none;
			border-radius: 100px;
			width:70px;	
			height:70px;
			float: left;
		}
		
		.content{
			width:72%;
			height: 100px;
			margin-left:20px;
			text-align: left;
			line-height: 230%;
			font-family: "楷体";
			font-size:30px ;
			color: #CCFFCC;
			float: left;
		}
		
		
	</style>
</head>
<body>
	<div class="title">留言板</div>
	<div class="main" id="main">
		
		<?php 
			while($row = mysql_fetch_assoc($res)){

			   $item.=  '<div class="item">
                        	    <div class="img"><img src='.$row[headimgurl].'/></div>
                        	    <div class="content">'.$row[nickname].':'.$row[message].'</div>
                		    </div>';
			}

			echo $item;
		?>
	</div>

	<script>
	function loadXMLDoc(){
              var xmlhttp;
              xmlhttp=new XMLHttpRequest();   
              xmlhttp.onreadystatechange=function(){
                 if (xmlhttp.readyState==4 && xmlhttp.status==200){
                    document.getElementById("main").innerHTML=xmlhttp.responseText;
                 }
              }
              xmlhttp.open("POST","message.php",true);
              xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
              xmlhttp.send("ajax=1");
         }
	
	setInterval(loadXMLDoc,3000);
	</script>
</body>
