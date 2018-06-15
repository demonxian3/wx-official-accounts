<?php
include "./c.php";
$appid = "yourAppID";
$appsecret = "yourappsecret";
$nbsp = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$wc = new wechat($appid,$appsecret);
if($_POST['data'])
  $data = $_POST['data'];
else 
  $data='{
"button":[{
	"name" : "赞我一下",
	"type" : "click",
	"key"  : "support"
	},{
	"name" : "微信功能",
	"sub_button": [{
		"name":"点歌",
		"type":"click",
		"key" :"music"
	 },{
		"name":"新闻",
		"type":"click",
		"key" :"news"
	},{
		"name":"视频",
		"type":"click",
		"key" :"video"
	}]
	}]
	}';

    $option = $_POST['option'];
    $ac     = $wc->getAccessToken();
    switch($option){
        case 'create':
            //var_dump($data);
            $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$ac}";
            $arr = $wc->httpRequest($url,$data);
            if( $arr["errmsg"]=="ok")echo "创建成功";
            else echo "创建失败";
            //var_dump($arr);
	    exit;

        case 'select':
            $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$ac}";
            $arr = $wc->httpRequest($url);
            if($arr["errcode"]){echo "列表为空或者其他错误";}
            //var_dump($arr['menu']['button']);
            foreach($arr["menu"]["button"] as $key => $value){
                foreach($value as $key1 => $value1){
                    //如果存在子按钮
                    if(is_array($value1) && count($value1)>0){
                        foreach($value1 as $key2 => $value2){
                            if(is_array($value2) && count($value2)>0){
                                foreach($value2 as $key3 => $value3){
                                    if($key3 == "name")$str.= "<br>".$nbsp."|---".$value3;
                                    if($key3 == "key" )$str.="(".$value3.")<br>";
                                    if($key3 == "url" )$str.="(".$value3.")<br>";
                                    echo $str;
                                    $str='';
                                }
                            }
                    	}
               	    }
                    //如果不存在子按钮
                    else {
                        if($key1 == "name")$str.= "<br>|---".$value1;
                        if($key1 == "key")$str.="(".$value1.")<br>";
                        if($key1 == "url")$str.="(".$value1.")<br>";
                        echo $str;
                        $str='';
                    } 
                }
            }
            exit;

       case 'delete':
            $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$ac}";
            $arr = $wc->httpRequest($url);
            if($arr["errmsg"] == "ok")echo "删除成功";
            else echo "删除失败";
            exit;
    }

?>
<head>
	<meta charset="utf-8" />
	<style>
		#main{
			border: 1px solid  #C0C0C0;
			background: #F0F0F0;
			width: 50%;
			height:97% ;
			position: absolute;
			left: 26%;
			top: 2%;
		}
		
		#title{
			color:#C0C0C0;
			position: absolute;
			text-align: center;
			margin-top: 10px;
			width: 100%;
			font-family:"华文楷体";
			font-size: 20px;
			line-height: 32px;
		}
		
		#content{
			border: 1px solid  #C0C0C0;
			position: relative;
			top: 8%;
			left: 28%;
			width: 68%;
			height: 90%;
			background: white;
			outline: none;
		}
		
		#button{
			position: absolute;
			width:300px;
			height: 100px;
			margin-top:60px;
			margin-left: 50px;
		}
		
		input{
			width: 120px;
			height: 50px;
			cursor: pointer;
			color: white;
			border: 1px  #B8B8B8 dashed;
			border-radius: 40px;
			background:   #D0D0D0;
			display: block;
			margin-top: 14px;
		}
	</style>

	<script>
		function loadXMLDoc(par){
			var xmlhttp;
			xmlhttp=new XMLHttpRequest();	
			xmlhttp.onreadystatechange=function(){
			  if (xmlhttp.readyState==4 && xmlhttp.status==200){
			    document.getElementById("content").innerHTML=xmlhttp.responseText;
    			  }
  			}
			xmlhttp.open("POST","menu.php",true);
			xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
			xmlhttp.send(par);
		}
	</script>
</head>
<body>
	<div id="main">
		<div id="title">微信公众号自定义菜单</div>
		<div id="button">
				<input id="create" type="button" value="create"  onclick="ccreate()">
				<input id="select" type="button" value="select"  onclick="sselect()">
				<input id="delete" type="button" value="delete"  onclick="ddelete()">
				<input value="clear"  type="button" onclick="document.location='http://www.demonx.cn/weixin/test.php'">
		</div>
		<div id="content" contenteditable="true"></div>
	</div>
	
	<script>
		var content = document.getElementById("content");

		function ccreate(){
		  //获取content里的json
		  var str = content.innerHTML;
		  var a = str.indexOf('button');
		  var b = str.indexOf('name');
		  var c = str.indexOf('type');
		  if(a>-1 && b>-1 && c>-1){
		    //剔除所有html标签和空格
		    var data = str.replace(/<.*?>/ig,"");
		    data = data.replace(/&nbsp;/ig,"");
		    //url编码
		    alert(data);
		    var par = "option=create&data="+data;
		    loadXMLDoc(par);
		  }
		  else 
		    var par = "option=create";
		    loadXMLDoc(par);
		}
		
		function sselect(){
		    var par = "option=select";
		    loadXMLDoc(par);
		}
		
		function ddelete(){
		    confirm = confirm("确定要删除吗？");
		    if(confirm){
		       var par = "option=delete";
		       loadXMLDoc(par);
		    }
		    else return false;
		}		
	</script>
</body>
