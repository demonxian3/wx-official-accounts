<?php
class weChat{

	public $appid;
	public $appsecret;

	public function __construct($appid,$appsecret){
		$this->appid = $appid;
		$this->appsecret = $appsecret;
		mysql_connect("localhost:3306","root","yourpasswd");

		mysql_set_charset('utf8');

		mysql_select_db('app');

	}

	public function checkSignature(){
		$signature=$_GET['signature'];
		$timestamp=$_GET['timestamp'];
		$nonce=$_GET['nonce'];
		$echostr=$_GET['echostr'];
		$tmpArr=array(TOKEN,$timestamp,$nonce);
		sort($tmpArr);
		$tmpStr=join($tmpArr);
		$tmpStr=sha1($tmpStr);
		if($tmpStr==$signature){echo $echostr;}
		else{echo "error";}
    }
    
	public function responseMsg(){
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if(!$postStr){
			echo "post data error";
			exit;
		}
		$postObj = simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
		$MsgType = $postObj->MsgType;
		$this->checkMsgType($postObj,$MsgType);
	}
	
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
			case 'event':
			  $Event=$postObj->Event;
			  $this->checkEvent($postObj,$Event);
			  break;
		}
	}
	
	public function receiveText($postObj){
		$Content=$postObj->Content;
		$files  = scandir('musics');
		switch($Content){
			case "点歌":
			  $str   ="下面菜单,请输入编号\n";
        		  foreach($files as $key => $values){
                	    if($values != "." and $values != ".."){
                              $str.=($key-1).".{$values}\n";
                            }
                          }          
			  $this->replyText($postObj,$str);
			  break;


			case '啥课':
			  $Content = $this->replySubject($Content);
			  $this->replyText($postObj,$Content);
			  break;
			
			case '新闻':
			  $data=array(
			      array(
				'Title'=>'互联网内你不知道的世界---暗网',
				'Description'=>'linux的故事',
				'PicUrl'=>'http://www.demonz.cn/wechat/images/deep.jpg',
				'Url'=>'http://baike.baidu.com/link?url=a9gdMUTV2XjcMGDCoyjWjk_G3jKeFqbaV0kPBBJhXImVNcdPJh3d8El9aLx_BnLvdIB1EVCHHyaov4s5KFDXlfPOJteFJ6eMw6DA_tjmHYK',
			      ),
			      array(
                                'Title'=>'linux的故事2',
                                'Description'=>'linux的故事2',
                                'PicUrl'=>'http://www.demonz.cn/wechat/images/qier2.jpg',
                                'Url'=>'http://www.baidu.com',
                              ),
			      array(
                                'Title'=>'linux的故事3',
                                'Description'=>'linux的故事3',
                                'PicUrl'=>'http://www.demonz.cn/wechat/images/kali.jpg',
                                'Url'=>'http://www.baidu.com',
                              ),

			  );
			  $this->replyNews($postObj,$data);
			  break;

			default:
			  if(preg_match("/^sq([\x{4e00}-\x{9fa5}]+)/ui",$Content,$arr)){
			    $sql="insert into content values(NULL,'$postObj->FromUserName','$arr[1]');";
			    mysql_query($sql);
			    break;			  
			  }
			
			  if(preg_match("/^ss([\x{4e00}-\x{9fa5}]+)/ui",$Content,$arr)){
                            $sql="select * from position where openid = '$postObj->FromUserName';";
			    $res=mysql_query($sql);
                            $row=mysql_fetch_assoc($res);
			    $url="http://www.demonz.cn/wechat/baidu/index.php?x=".$row[longitude]."&y=".$row[latitude]."&search=".urlencode($arr[1]);
                            $eurl=urlencode($url);
			    $this->replyText($postObj,$url);
			    break;
                          }

			  if(preg_match('/^\d{1,2}$/',$Content)){
                             foreach($files as $key => $values){
                               if($values != "." and $values != ".."){
				 if($Content==($key-1)){
				    $data = array(
					'Title' => $values,
					'Description' => $values,
					'MusicUrl' => 'www.demonz.cn/wechat/musics/'.$values,
					'HQMusicUrl' => 'www.demonz.cn/wechat/musics/'.$values
				    );
				    $this->replyMusic($postObj,$data);
				 }
                               }
                             }    
			  }
			  $Content = "想知上啥课请输入啥课\n想听听歌曲请输入点歌\n想看看文章请输入新闻\n想和机器人聊天发语音\n搜索周边输入ss+周边";
			  $this->replyText($postObj,$Content);
			  break;
		}
#		//维修公告
#		$Content = "其他功能还未完善，其耐心等待更新";
#		$this->replyText($postObj,$Content);	
	}

	public function receiveVoice($postObj){
		$info=$postObj->Recognition;
		$this->robot($postObj,$info);
	}

	public function robot($postObj,$info){
		$key="b3c4ca104f2742d09d3b1489122bb0cc";
		$url="http://www.tuling123.com/openapi/api?key=".$key."&info=".$info;
		$arr=$this->send_request($url);
		$code=$arr['code'];

		switch($code){
		  case '100000':
		    $this->replyText($postObj,$arr['text']);
		    break;

		  case '200000':
		    $this->replyText($postObj,"<a href='".$arr['url']."'>".$arr['url']."</a>");
		    break;
		}

	}

	public function checkEvent($postObj,$Event){
		switch($Event){
        	  case 'subscribe':
		    $Content = "想知上啥课请输入啥课\n想听听歌曲请输入点歌\n想看看文章请输入新闻\n想和机器人聊天发语音";
		    $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$this->get_access_token()."&openid=".$postObj->FromUserName."&lang=zh_CN";
		    
		    $userInfo=$this->send_request($url);
		    $sql="insert into user values(null,'$userInfo[openid]','$userInfo[nickname]','$userInfo[sex]','$userInfo[city]','$userInfo[country]','$userInfo[province]','$userInfo[headimgurl]','$userInfo[subscribe_time]')";
		    mysql_query($sql);
		    
		    $this->replyText($postObj,$Content);
                    break;

                  case 'unsubscribe':
		    $openid = $postObj->FromUserName;
		    $sql = "delete from user where openid = '$openid'";
		    mysql_query($sql);
		    break;

          	  case 'CLICK':
                    $EventKey = $postObj->EventKey;
                    $this->checkClick($postObj,$EventKey);
                    break;
		
		  case 'LOCATION':
		    $req = "select openid  from position where openid = '$postObj->FromUserName'";
		    $res = mysql_query($req);
		    $val = mysql_fetch_assoc($res);
		
		    if(!$val[openid]){
		    	$sql = "insert into position values(NULL,'".$postObj->FromUserName."','".$postObj->Latitude."','".$postObj->Longitude."');";
		    }else{
			$sql = "update  position set Latitude='$postObj->Latitude' , Longitude='$postObj->Longitude' where openid='$postObj->FromUserName'";
		    }
		    mysql_query($sql);
        	}
	}

	public function checkClick($postObj,$EventKey){
		switch($EventKey){
		  case 'musics': 
		    $files = scandir('musics');
		    $str   ="下面菜单,请输入编号\n";
                    foreach($files as $key => $values){
                      if($values != "." and $values != ".."){
                        $str.=($key-1)  .  ".{$values}\n";
                      }
                    }
                    $this->replyText($postObj,$str);
		    break;

		  case 'subject':
		    $Content = $this->replySubject($Content);
                    $this->replyText($postObj,$Content);
                    break;

		  case 'zan':
		    $Content = "谢谢你的支持!!!";
		    $this->replyText($postObj,$Content);

		  case 'sq':
		    $Content = "回复sq+留言可以将你的留言发送到下面的链接地址:\nhttp://www.demonz.cn/wechat/qiang/wall.php";
		    $this->replyText($postObj,$Content);

		  case 'ss':
		    $Content = "回复ss+搜索地点 如:\n'ss网咖' 搜索附近网咖\n这需要获取你的地理位置";
		    $this->replyText($postObj,$Content);

		  case 'wu':
		    $Content = "想知上啥课请输入啥课\n想听听歌曲请输入点歌\n想看看文章请输入新闻\n想和机器人聊天发语音\n搜索周边输入ss+周边";
		    $this->replyText($postObj,$Content);
		}
	}

	public function receiveImage($postObj){
		$MediaId=$postObj->MediaId;
		$this->replyImage($postObj,$MediaId);
	}
	
	public function replyText($postObj,$Content){
		$xml = "<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%d</CreateTime>
			<MsgType><![CDATA[text]]></MsgType>
			<Content><![CDATA[%s]]></Content>
			</xml>";
		echo sprintf($xml,$postObj->FromUserName,$postObj->ToUserName,time(),$Content);
	}

	public function replyImage($postObj,$MediaId){
		$xml= "<xml>
		       <ToUserName><![CDATA[%s]]></ToUserName>
		       <FromUserName><![CDATA[%s]]></FromUserName>
		       <CreateTime>%d</CreateTime>
		       <MsgType><![CDATA[image]]></MsgType>
		       <Image>
		       <MediaId><![CDATA[%s]]></MediaId>
		       </Image>
		       </xml>";
		echo sprintf($xml,$postObj->FromUserName,$postObj->ToUserName,time(),$MediaId);
	}


	public function replyMusic($postObj,$data){
		$xml = "<xml>
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
			</xml>";
		echo sprintf($xml,$postObj->FromUserName,$postObj->ToUserName,time(),$data['Title'],$data['Description'],$data['MusicUrl'],$data['HQMusicUrl']);

	}

	public function replyNews($postObj,$data){

		foreach($data as  $values){
		   $str.="
		      <item>
		      <Title><![CDATA[".$values[Title]."]]></Title> 
		      <Description><![CDATA[".$values[Description]."]]></Description>
		      <PicUrl><![CDATA[".$values[PicUrl]."]]></PicUrl>
		      <Url><![CDATA[".$values[Url]."]]></Url>
		      </item>";
		   }

		$xml="<xml>
		      <ToUserName><![CDATA[%s]]></ToUserName>
		      <FromUserName><![CDATA[%s]]></FromUserName>
		      <CreateTime>%d</CreateTime>
		      <MsgType><![CDATA[news]]></MsgType>
		      <ArticleCount>".count($data)."</ArticleCount>
		      <Articles>".$str."</Articles>
		      </xml> ";
		echo sprintf($xml,$postObj->FromUserName,$postObj->ToUserName,time());

	}


	public function replySubject($Content){		
	  $a = date("w");
 	  switch($a){
       		 case 0:$Content= "今天没课,去嗨吧！";break;
	         case 1:
         	   if(date('H')<= 9)                      {$Content= "上午英语学思楼3-A202 (08:30-09:55)";break;}
                   else if(date('H')> 9 and date('H')<14) {$Content= "下午linux知行楼7-303 (13:30-16:30)";break;}
                   else                                   {$Content= "明天上午网页制作, 下午体育课\n具体时间地点明天告诉你";break;}
         	 case 2:
                   if(date('H')<= 9)                      {$Content= "上午网页知行楼5-207C (8:30-11:35)";break;}
                   else if(date('H')> 9 and date('H')<14) {$Content= "下午体育课 (13:30-14:55)";break;}
                   else                                   {$Content= "晚上选修课知行楼7-302(6:30-8:00)\n明天上午思政, 下午没课\n具体时间地点明天说";break;}
                 case 3:
                   if(date('H')<= 9)                      {$Content= "上午思政学思楼3-B308 (8:30-09:55)";break;}
                   else if(date('H')> 9 and date('H')<14) {$Content= "下午没课";break;}
                   else                                   {$Content= "明天上午思政课, 下午英语\n具体时间地点明天告诉你";break;}
                 case 4:
                   if(date('H')<= 9)                      {$Content= "上午思政学思楼3-C208 (08:30-09:55)";break;}
                   else if(date('H')> 9 and date('H')<14) {$Content= "下午英语学思楼3-B203 (13:30-14:55)";break;}
                   else                                   {$Content= "明天上午心理课, 下午网络课\n具体时间地点明天告诉你";break;}
                 case 5:
                   if(date('H')<= 11)                     {$Content= date('Y/m/d H-i-s')."上午心理学思楼3-c213 (10:10-11:35)";break;}
                   else if(date('H')> 11 and date('H')<14){$Content= "下午网络知行楼7-303内(13:30-16:30)";break;}
                   else                                   {$Content= "明天放假";break;}
                 case 6:$Content="今天没课,去浪吧!";break;
          } 
	  return $Content;
	}

	public function send_request($url,$data=""){
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		if($data){
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		}
			
		$result_str=curl_exec($ch);
		$result_arr=json_decode($result_str,TRUE);
		
		if(is_array($result_arr)){
			return  $result_arr;
		}else{
			return  $result_str;
		}
		curl_close($ch);
	}

	public function get_access_token(){
		$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appid}&secret={$this->appsecret}";
		$arr = $this->send_request($url);
		$access_token = $arr['access_token'];
		return $access_token;
	}

	public function create_menu($data){
		$access_token = $this->get_access_token();
	    	$url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
	    	return $this->send_request($url,$data);
	}

	public function select_menu(){
		$access_token = $this->get_access_token();
		var_dump($access_token);
		$url="https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$access_token}";
		return $this->send_request($url);
	}

	public function delete_menu(){
		$access_token = $this->get_access_token();
		$url="https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$access_token}";
		return $this->send_request($url);
	}

}

?>

