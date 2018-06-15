<?php
	mysql_connect('localhost:3306','root','yourpasswd');
        mysql_set_charset('utf8');
        mysql_select_db('weixin');

	$openid = $_GET['openid'];

	$sql = "select latitude,longitude from location where openid = '$openid'";
	$res = mysql_query($sql);
	$row = mysql_fetch_assoc($res);
	$longitude = $row[longitude];
	$latitude  = $row[latitude];

	$apikey = "yourapikey";	

?>


<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf8">
	<style type="text/css">
		html,body{height:100%;margin:0px;padding:0px;font-family:"微软雅黑";font-size:14px;}
		#allmap{height:500px;width:100%;}
		.optionpanel{margin: 10px;}
		#r-result{width:100%;}
		#r-result p{margin:5px 0 0 10px;}
	</style>
	<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?php echo $apikey?>"></script>
	<script src="http://libs.baidu.com/jquery/1.9.0/jquery.js"></script>
	<title>贤哥霸气地图</title>
</head>
<body>
	<div id="allmap"></div>
	<div id="r-result">
		<div class="optionpanel">
			<label>选择主题</label>
			<select id="stylelist" onchange="changeMapStyle(this.value)"></select>
		</div>
	</div>   
</body>
</html>
<script type="text/javascript" src="http://developer.baidu.com/map/custom/stylelist.js"></script>
<script type="text/javascript">
	//初始化模板选择的下拉框
	var sel = document.getElementById('stylelist');
	for(var key in mapstyles){
		var style = mapstyles[key];
		var item = new  Option(style.title,key);
		sel.options.add(item);
	}
	var map = new BMap.Map("allmap");
	window.map = map;
	var point = new BMap.Point(<?php echo "$longitude,$latitude";?>);

	map.addControl(new BMap.NavigationControl());               // 添加平移缩放控件
	map.addControl(new BMap.ScaleControl());                    // 添加比例尺控件
	map.addControl(new BMap.OverviewMapControl());              //添加缩略地图控件
	map.enableScrollWheelZoom();                            //启用滚轮放大缩小
	map.addControl(new BMap.MapTypeControl());          //添加地图类型控件
	map.disable3DBuilding();
	map.centerAndZoom(point, 14); 

	//代码使用如下,即可. 模板页可以查看http://developer.baidu.com/map/custom/list.htm      
	//map.setMapStyle({style:'midnight'});

	changeMapStyle('midnight')
	sel.value = 'midnight';

	function changeMapStyle(style){
		map.setMapStyle({style:style});
		$('#desc').html(mapstyles[style].desc);
	}
</script>

