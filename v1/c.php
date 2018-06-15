<?php
class weChat{

////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////// initData /////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////
	public $appid,$appsecret;

	public function __construct($appid,$appsecret){
	  $this->appid = $appid;
	  $this->appsecret = $appsecret;

	  mysql_connect('localhost:3306','root','yourpassword');
	  mysql_set_charset('utf8');
	  mysql_select_db('weixin');
	}


	public function checkSignature(){
	  $signature = $_GET['signature'];
          $timestamp = $_GET['timestamp'];
          $nonce     = $_GET['nonce'];
          $echostr   = $_GET['echostr'];

          $tmpArr = array(TOKEN,$nonce,$timestamp);
          sort($tmpArr);
          $tmpStr = join($tmpArr);
          $tmpStr = sha1($tmpStr);

          if($tmpStr == $signature)echo $echostr;
	}

	public function responseMsg(){
	  $postStr   = $GLOBALS['HTTP_RAW_POST_DATA'];
	  if(!$postStr)return false;
	  $postObj   = simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
	  $MsgType   = $postObj->MsgType;
	  $this->checkMsgType($postObj,$MsgType);
	}

////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////// receiveData ////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////

	public function checkMsgType($postObj,$MsgType){
	  switch($MsgType){
	    case 'text':
		$this->receiveText($postObj);
		break;
	    case 'image':
		$this->receiveImage($postObj);
		break;
	    case 'voice':
		$this->receiveVoice($postObj);
		break;
	    case 'video':
		$this->receiveVideo($postObj);
		break;
	    case 'event':
		$this->checkEvent($postObj);
		break;
	  }
	}

	  public function checkEvent($postObj){
		$Event = $postObj->Event;
		switch($Event){
		  case 'subscribe':
		     $Content="欢迎您的关注";
		     $this->replyText($postObj,$Content);
		     //请求用户信息
		     $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$this->getAccessToken()}&openid={$postObj->FromUserName}&lang=zh_CN";
		     $arr = $this->httpRequest($url);
		     //存储用户数据
		     $sql = "select * from wcuser where openid='$postObj->FromUserName'"; 
		     $res =  mysql_query($sql);
		     $row = mysql_fetch_assoc($res);
		     if(!$row){	//判断是否已经存储过了，避免重复存储！
		     $sql = "insert into wcuser values(null,'$arr[openid]','$arr[nickname]','$arr[sex]','$arr[city]','$arr[country]','$arr[province]','$arr[headimgurl]','$arr[subscribe_time]');";
		     mysql_query($sql);
  		     }
		     break;

		  case 'unsubscribe':
		     //删除用户数据
		     $sql = "delete from wcuser where openid ='".$postObj->FromUserName."';";
		     mysql_query($sql);
		     break;

		  case 'SCAN':
		     $value = "$postObj->EventKey";
		     switch($value){
			case 2225:
			  $this->makeNews($postObj);
			exit;
		     }
	
		  case 'LOCATION':
		     //存储地理位置
		     $sql = "select * from location where openid = '$postObj->FromUserName'";
		     $res = mysql_query($sql);
		     $row = mysql_fetch_assoc($res);
		     if($row)//如果已经存在了那么更新它
                     $sql = "update location set latitude='$postObj->Latitude',longitude='$postObj->Longitude'where openid='$postObj->FromUserName'";
               	     else
		     $sql = "insert into location values(null,'$postObj->FromUserName','$postObj->Latitude','$postObj->Longitude')";
		     mysql_query($sql);
		     break;

		  case 'CLICK':
		     $key = $postObj->EventKey;
		     switch($key){
			case 'music':
			  $postObj->Content="点歌";
			  $this->receiveText($postObj);
			  break;
			case 'video':
			  $postObj->Content="视频";
			  $this->receiveText($postObj);
			  break;
			case 'news':
			  $postObj->Content="新闻";
			  $this->receiveText($postObj);
			  break;
			case 'map':
			  $url="http://www.demonx.cn/weixin/baidu/look.php?openid=$postObj->FromUserName";
			  $img="map.jpg";
			  $Content="点我看地图";
			  $this->makeFun($postObj,$Content,$url,$img);

			case 'support':
			  $Content="谢谢你的支持!!";
			  $this->replyText($postObj,$Content);
			  break;

			case 'chou':
			  $openid=$postObj->FromUserName;
			  $sql="select * from chou where openid = '$openid'";
			  $res=mysql_query($sql);
			  $row=mysql_fetch_assoc($res);
			  if(!$row){
			    $ins="insert into chou value(null,'$openid',10,'false')";
			    mysql_query($ins);	
			  }
			  $url="http://www.demonx.cn/weixin/choujiang/index.php?openid=$openid";
			  $img="chou.jpg";
			  $Content="欢迎来到贤哥抽奖系统";
			  $this->makeFun($postObj,$Content,$url,$img);
			  
		     }
		}
	}
	  
	public function receiveText($postObj){
	  $Content = $postObj->Content;
	  $file    = scandir('/var/www/html/wechat/musics');
	  switch($Content){
	    case '点歌':
	      $Content = '';
              foreach($file as $key => $value)
	        if($value != '.' && $value != '..')
		  $Content .= ($key-1) .'.'. $value ."\n";
	      break;
	    case '新闻':
	      $this->makeNews($postObj);
	      exit;
	    case '视频':
	      $this->makeVideo($postObj);
	      break;
	    default:
	      if(preg_match('/^\d{1,2}$/',$Content)){
	       	$this->makeMusic($postObj,$Content);
		break;
	      }
	      //用户留言
	      if(preg_match('/^ly([\x{4e00}-\x{9fa5}]+)/ui',$Content,$arr)){
		$sql = "insert into message value(null,'$postObj->FromUserName','$arr[1]')";
		mysql_query($sql);
		$Content="查看留言";
		$url = "http://www.demonx.cn/weixin/message.php";
		$img = "msg.jpg";
	        $this->makeFun($postObj,$Content,$url,$img);
	      }

	     //匹配百度地图搜索
	     if(preg_match('/^ss([\x{4e00}-\x{9fa5}]+)/ui',$Content,$arr)){
		$key = urlencode($arr[1]);
		$url = "http://www.demonx.cn/weixin/baidu/search.php?openid=$postObj->FromUserName&key=$key";
		$Content = "查看搜索结果";
		$img = "ss.jpg";
		$this->makeFun($postObj,$Content,$url,$img);
                break;
              }

	     //默认情况下把消息转发给机器人
	     $Content=$postObj->Content;
	     $this->makeRobot($postObj,$Content);
	     break;
	  }
		
	  $this->replyText($postObj,$Content);
	}

	  public function receiveImage($postObj){
		$MediaId = $postObj->MediaId;
		$this->replyImage($postObj,$MediaId);
	  }

	  public function receiveVoice($postObj){
		$Content = $postObj->Recognition;
		$this->makeRobot($postObj,$Content);
	  }
	  
	  public function receiveVideo($postObj){
		$Content = "你发送的是视频";
		$this->replyText($postObj,$Content);
	  }



////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////// makeData //////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////

	  public function makeMusic($postObj,$num){
	    $file = scandir("/var/www/html/wechat/musics");
	       foreach($file as $key => $value)
		 if($value != '.' && $value != '..')
		   if($num == ($key-1)){
		     $MusicData=array(
			'Title'      =>"Title:".$value,
			'Description'=>"Description".$value,
			'MusicUrl'   =>'www.demonx.cn/wechat/musics/'.$value,
			'HQMusicUrl' =>'www.demonx.cn/wechat/musics/'.$value
		     );
	             $this->replyMusic($postObj,$MusicData);
		   }
	  }

	  public function makeNews($postObj){
		$NewsData = array(
		  array(
		    'Title'	 =>"我的网站",
		    'Description'=>"不可描述",
		    'PicUrl'	 =>"http://www.demonx.cn/wechat/images/kali.jpg",
		    'Url'	 =>"http://www.demonx.cn"
		  ),
		  array(
		    'Title'	 =>"这是百度",
		    'Description'=>"无法描述",
		    'PicUrl'	 =>"http://www.demonx.cn/wechat/images/qier.jpg",
		    'Url'	 =>"http://www.baidu.com"
		  ),
		  array(
		    'Title'	 =>"QQ网站",
		    'Description'=>"怎么描述",
		    'PicUrl'	 =>"http://www.demonx.cn/wechat/iamges/time.jpg",
		    'Url'	 =>"http://www.qq.com"
                  ),
		);
		$this->replyNews($postObj,$NewsData);
	  }

	  public function makeFun($postObj,$Content,$url,$img){
		$NewsData = array(
                  array(
                    'Title'      =>$Content,
                    'Description'=>"",
                    'PicUrl'     =>"http://www.demonx.cn/wechat/images/$img",
                    'Url'        =>$url
                  ),
                );

		$this->replyNews($postObj,$NewsData);
	   }
	  public function makeVideo($postObj){
		$VideoData = array(
		    'Title'	 =>"跨屏啦！",
		    'Description'=>"哈哈kali被我分尸了",
		    'MediaId'	 =>"jQO9lSx-0VYo-bvASMiXyAx6BEj0j10wFLZ4CKLI9AzlLKodq7GHsK9ZIFbAWD5Y"
		);
		$this->replyVideo($postObj,$VideoData);
	  }

	 public function makeRobot($postObj,$Content){
		$key='b3c4ca104f2742d09d3b1489122bb0cc';
		$url='http://www.tuling123.com/openapi/api?key='.$key.'&info='.$Content;
		$arr= $this->httpRequest($url);
		$Content=$arr['text'];
		if($arr['code']==200000){
		  $Content.="\n".$arr['url'];
		}
		$this->replyText($postObj,$Content);
	 }


////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////// sendData ///////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////

	  public function replyText($postObj,$Content){
		$xml = '<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%d</CreateTime>
		<MsgType><![CDATA[text]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		</xml>';

		echo sprintf($xml,$postObj->FromUserName,$postObj->ToUserName,time(),$Content);
	  }

	  public function replyImage($postObj,$MediaId){
		$xml = '<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%d</CreateTime>
		<MsgType><![CDATA[image]]></MsgType>
		<Image>
		<MediaId><![CDATA[%s]]></MediaId>
		</Image>
		</xml>';

		echo sprintf($xml,$postObj->FromUserName,$postObj->ToUserName,time(),$MediaId);
	  }
	
	  public function replyVideo($postObj,$VideoData){
		$xml = '<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%d</CreateTime>
		<MsgType><![CDATA[video]]></MsgType>
		<Video>
		<MediaId><![CDATA[%s]]></MediaId>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		</Video> 
		</xml>';

		echo sprintf($xml,$postObj->FromUserName,$postObj->ToUserName,time(),$VideoData['MediaId'],$VideoData['Title'],$VideoData['Description']);
	  }

	  public function replyMusic($postObj,$MusicData){
		$xml = '<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%d</CreateTime>
		<MsgType><![CDATA[music]]></MsgType>
		<Music>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		<MusicUrl><![CDATA[%s]]></MusicUrl>
		<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
		</Music>
		</xml>';
		echo sprintf($xml,$postObj->FromUserName,$postObj->ToUserName,time(),$MusicData['Title'],$MusicData['Description'],$MusicData['MusicUrl'],$MusicData['HQMusicUrl']);
	  }
	
	  public function replyNews($postObj,$NewsData){
		foreach($NewsData as $key => $value){
		  $items .="<item>
		  <Title><![CDATA[".	  $value['Title']	."]]></Title> 
		  <Description><![CDATA[".$value['Description']	."]]></Description>
		  <PicUrl><![CDATA[".	  $value['PicUrl']	."]]></PicUrl>
		  <Url><![CDATA[".	  $value['Url']		."]]></Url>
		  </item>";	  
		}

		$xml = "<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%d</CreateTime>
		<MsgType><![CDATA[news]]></MsgType>
		<ArticleCount>%d</ArticleCount>
		<Articles>%s</Articles>
		</xml> ";

		echo sprintf($xml,$postObj->FromUserName,$postObj->ToUserName,time(),count($NewsData),$items);
	  }

////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////// function ///////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////

	public function getAccessToken(){
	   $mem = new Memcache();
	   $mem->connect("localhost",11211);
	   $access_token = $mem->get("acc");

	   if(!$access_token){
	      if(!$this->appid||!$this->appsecret){
                $this->appid='yourappid';
                $this->appsecret='yourappsecret';
              }
	      $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->appsecret}";
	      $var=$this->httpRequest($url);
	      if(is_array($var))$access_token=$var['access_token'];
	      $mem->set("acc",$access_token,0,7200);
	   }
	   return $access_token;
	}

	public function httpRequest($url,$data=''){
	  $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                if($data){
                  curl_setopt($ch,CURLOPT_POST,1);
                  curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
                }
                $result_str = curl_exec($ch);
                $result_arr = json_decode($result_str,true);

                if(is_array($result_arr))return $result_arr;
                else return $result_str;
                curl_close($ch);
	}
   }
?>
