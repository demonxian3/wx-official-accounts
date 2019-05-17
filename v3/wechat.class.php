<?php
    class Wechat{
        public function __construct($token="", $appid="", $appsecret=""){
            $this->token = $token;
            $this->appid = $appid;
            $this->appsecret = $appsecret;
            
            $this->domain = "http://xxx.xxx.xxx";
            $this->info = "嗨, %s \n".
            "******************************\n".
            "*  欢迎来到Demon贤公众号  *\n".
            "******************************\n".
            "输入：点歌  可选择歌曲\n".
            "输入：学号  可查询成绩\n".
            "输入：斗图  和机器人斗图\n".
            "输入：笑话  看机器人讲笑话\n".
            "输入：快递单号 查快递\n".
            "输入：看电影名 获取电影种子\n".
            "输入：动车号码 查询火车售票\n".
            "...\n\n".
            "等等，更多功能如下\n支持语音聊天\n".
            "机器人支持：\n*****************************\n".
            "点歌,翻译,闲聊,笑话,斗图,星座,\n".
            "故事,顺口溜,急转弯,歇后语,绕口令\n".
            "查快递,天气,股票,市编号,日期\n".
            "查列车,成绩,排名,电影,新闻\n".
            "数学计算,生活百科,问答百科,成语接龙";
        }

        public function chkSig(){
            $token = $this->token;
            $nonce = $_GET['nonce'];
            $signature = $_GET['signature'];
            $timestamp = $_GET['timestamp'];
            $echostr = $_GET['echostr'];

            $tmpArr = array($token, $timestamp, $nonce);
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode($tmpArr);
            $tmpStr = sha1($tmpStr);

            if($signature === $tmpStr)
                return $echostr;
            else
                return false;
        }

        public function main(){
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
            if(!$postStr){
                echo "post data error";
                return false;
            }

            $postObj = simplexml_load_string($postStr, "SimpleXMLElement", LIBXML_NOCDATA);
            $this->type($postObj);
        }

        public function type($postObj){
            switch($postObj->MsgType){
                case 'text':
                    $this->procText($postObj);
                    break;
                case 'voice':
                    $this->getTulin($postObj);
                    break;
                case 'image':
                    $MediaId = $postObj->MediaId;
                    $this->sendImage($postObj,$MediaId);
                    break;
                case 'video':
                    $this->sendVideo($postObj);
                    break;
                case 'event':
                    $this->procEvent($postObj);
                    break;
            }
        }


        public function procText($postObj){
            switch($postObj->Content){
                case '点歌':
                    $musicArr = $this->getMusic();
                    $content = "输入: music + 数字编号 选择歌曲:\n如: music1\n";
                    for($i=0; $i<count($musicArr);$i++)
                        $content .= ($i+1) .".". $musicArr[$i][Title] ."\n";
                    $this->sendText($postObj, $content);
                    break;

                case '学号':
                    $content = "输入您的学号，即可查询成绩\n如：1601050132\n";
                    $this->sendText($postObj, $content);
                    break;

                case '新闻':
                    $newsArr = $this->getNews();
                    $this->sendNews($postObj, $newsArr);
                    break;

                case '排名':
                    $content = "输入班级简称，即可查询排名\n如：17应用3-2班\n";
                    $this->sendText($postObj, $content);
                    break;

                case '信息':
                    $userObj = $this->getUserInfo($postObj);
                    $content = $this->userObjToStr($userObj);
                    $this->sendText($postObj, $content);
                    break;

                case '短信':
                    $errObj= $this->sendSMS($postObj, 15602434504);
                    $this->sendText($postObj, $errObj->errmsg);
                    break;
                
                default:
                    # music selection 
                    if(preg_match('/^music(\d{1,})$/i', $postObj->Content, $matches)){
                        $musicArr = $this->getMusic();
                        $num = $matches[1] - 1;
                        if($num < 0 )return false;
                        $this->sendMusic($postObj, $musicArr[$num]);
                        break;
                    }
                
                    # class score rank
                    else if(preg_match_all("/(\d{2}[\x{4e00}-\x{9fa5}]{2}\d{1}-\d{1}班)/u", $postObj->Content, $matches)){;
                        $content = shell_exec("bash score/sortRank.sh score/".$matches[1][0].".txt");
                        $this->sendText($postObj, $content);
                        break;
                    }

                    # student score query
                    else if(preg_match('/^(\d{10})$/', $postObj->Content, $matches)){
                        $content = shell_exec("python score/stuMark.py ".$matches[1]);
                        $this->sendText($postObj, $content);
                        break;
                    }

                    # movie resource query
                    else if(preg_match('/^看(.*)$/', $postObj->Content, $matches)){
                        $content = "";
                        $res = $this->getSqlSearch($matches[1]);

                        while($row = mysql_fetch_assoc($res)){
                            $content .= $row[name] ."\n";
                            $content .= $this->domain ."/v3/80s/main.php?movieid=". $row[id] . "\n";
                        }

                        if(!$content)
                            $content = "找不到相关资源";
                        
                        $this->sendText($postObj, $content);
                        break;
                    }

                    # read data from file
                    else if(preg_match('/^::get (.*)$/', $postObj->Content, $matches)){
                        $content = $this->getFileContent($matches[1]);
                        $this->sendText($postObj, $content);
                        break;
                    }

                    # write data to file 
                    else if(preg_match('/^::set (.*?)$/', $postObj->Content, $matches)){
                        $matcheArr = explode("=>", $matches[1]);
                        $content = $this->setFileContent($matcheArr[0], $matcheArr[1]);
                        $this->sendText($postObj, $content);
                        break;
                    }
                    
                    # clear file
                    else if(preg_match('/^::clearall$/', $postObj->Content, $matches)){
                        $this->clearFileContent();
                        break;
                    }

                    # default output
                    else
                        $this->getTulin($postObj);
                    break;
            }
        }


        public function procEvent($postObj){
            switch($postObj->Event){
                case 'subscribe':
                    $newsArr = $this->getNews();
                    $userObj = $this->getUserInfo($postObj);
                    $content = sprintf($this->info, $userObj->nickname);
                    $this->sendText($postObj, $content);
                    break;

                case 'CLICK':
                    switch($postObj->EventKey){
                        case 'rank':
                            $content = "输入班号即可查询排名,如:\n17应用3-1班";
                            $this->sendText($postObj, $content);
                            break;
                        case 'support':
                            $content = "支付宝发红包啦！即日起还有机会额外获得余额宝";
                            $content .= "消费红包！长按复制此消息，打开最新版支付宝就能领取！AnqHPz17Kz";
                            $this->sendText($postObj, $content);
                            break;
                        case 'info':
                            $userObj = $this->getUserInfo($postObj);
                            $content = sprintf($this->info, $userObj->nickname);
                            $this->sendText($postObj, $content);
                            break;
                    }
            }
        }

        public function getMusic(){
            $id = 0;
            $musicArr = Array();
            $files = scandir("data/music");
            foreach($files as $k => $v){
                if($v === "." || $v === "..")   continue;
                $musicArr[$id] = Array();
                $musicArr[$id][Id] = $id;
                $musicArr[$id][Title] = $v;
                $musicArr[$id][Description] = $v;
                $musicArr[$id][MusicURL] = $this->domain . "/v3/data/music/". $v;
                $musicArr[$id][HQMusicUrl] = $this->domain . "/v3/data/music/". $v;
                ++$id;
            }
            return $musicArr;
        }

        public function getNews(){
            $id = 0;
            $newsArr = Array();
            $newsArr[Articles] = Array();
            $newsArr[Articles][0] = Array(
                "Title" => "新闻测试 1",
                "Description" => "新闻描述 1",
                "PicUrl" => $this->domain."/v3/data/image/timg.jpg",
                "Url" => "http://www.baidu.com",
            );
            $newsArr[Articles][1] = Array(
                "Title" => "新闻测试 2",
                "Description" => "新闻描述 2",
                "PicUrl" => $this->domain."/v3/data/image/timg3.jpg",
                "Url" => "http://www.qq.com",
            );
            $newsArr[Articles][2] = Array(
                "Title" => "新闻测试 3",
                "Description" => "新闻描述 3",
                "PicUrl" => $this->domain."/v3/data/image/timg2.jpg",
                "Url" => "http://www.qq.com",
            );
            $newsArr[ArticleCount] = 3;
            return $newsArr;
        }

        
        /***********************************************
         *                  simple reply               *
         ***********************************************/
        public function sendText($postObj, $content){
            $xml = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%d</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";
            echo sprintf($xml, $postObj->FromUserName, $postObj->ToUserName, time(), $content);
            exit;
        }

        public function sendImage($postObj, $MediaId){
            $xml = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%d</CreateTime>
            <MsgType><![CDATA[image]]></MsgType>
            <Image>
                <MediaId><![CDATA[%s]]></MediaId>
            </Image>
            </xml>";
            echo sprintf($xml, $postObj->FromUserName, $postObj->ToUserName, time(), $MediaId);
            exit;
        }

        public function sendMusic($postObj, $musicAss){
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
            echo sprintf($xml, $postObj->FromUserName, $postObj->ToUserName, time(), $musicAss[Title], 
                $musicAss[Description], $musicAss[MusicUrl], $musicAss[HQMusicUrl]);
            exit;
        }

        public function sendVideo($postObj, $MediaId){
            $xml = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%d</CreateTime>
            <MsgType><![CDATA[video]]></MsgType>
            <Video>
                <MediaId><![CDATA[%s]]></MediaId>
                <Title><![CDATA[title]]></Title>
                <Description><![CDATA[description]]></Description>
            </Video>
            </xml>";
            echo sprintf($xml, $postObj->FromUserName, $postObj->ToUserName, time(), $MediaId);
            exit;
        }

        public function sendNews($postObj, $newsArr){
            $xml = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%d</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>".$newsArr[ArticleCount]."</ArticleCount>";
            
            $xml .= "<Articles>";
            for($i=0; $i<$newsArr[ArticleCount]; $i++){
                $xml .= "<item>";
                $xml .= "<Title><![CDATA[       {$newsArr[Articles][$i][Title]}       ]]></Title>";
                $xml .= "<Description><![CDATA[ {$newsArr[Articles][$i][Description]} ]]></Description>";
                $xml .= "<PicUrl><![CDATA[      {$newsArr[Articles][$i][PicUrl]}      ]]></PicUrl>";
                $xml .= "<Url><![CDATA[         {$newsArr[Articles][$i][Url]}         ]]></Url>";
                $xml .= "</item>";
            }
            $xml .= "</Articles>";
            $xml .= "</xml>";
            echo sprintf($xml, $postObj->FromUserName, $postObj->ToUserName, time());
            exit;
        }

        /***********************************************
         *                Inner API call               *
         ***********************************************/
        public function getUserInfo($postObj){
            $openid = $postObj->FromUserName;
            $actoken = $this->getAcToken();
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$actoken&openid=$openid";
            $userObj = json_decode($this->httpGet($url));

            if($userObj->sex) $userObj->sex = "男";
            else $userObj->sex = "女";

            return $userObj;
        }

        public function userObjToStr($userObj){
            $content = "";
            foreach($userObj as $key => $value){
                $content .= "[$key]: $value\n";
            }   
            return $content;
        }

        public function mkMenu($menuJson){
            $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $this->getAcToken();
            $rep = $this->httpPost($url, $menuJson);
            $obj = json_decode($rep);
            if($obj->errmsg === "ok")
                return true;
            else
                var_dump($obj);
                return false;
        }

        public function lsMenu(){
            $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $this->getAcToken();
            $obj = json_decode($this->httpGet($url));
            return $obj;
        }

        public function rmMenu(){
            $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=" . $this->getAcToken();
            $obj = json_decode($this->httpGet($url));
            if($obj->errmsg === "ok")
                return true;
            else
                return false;
        }

        public function getAcToken(){
            $access_token = $this->getMemcache("access_token");
            if(!$access_token){
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appid&secret=$this->appsecret";
                $obj = json_decode($this->httpGet($url));
                $this->setMemcache("access_token", $obj->access_token, 7200);
                return $obj->access_token;
            }
            return $access_token;
        }


        /***********************************************
         *               Outer API call                *
         ***********************************************/
        public function getTulin($postObj){
            $userid = "demonxian3";
            $appurl = "http://openapi.tuling123.com/openapi/api/v2";
            $appkey = "5218c6192a7b49b5b36a0a921def3ffe";

            switch($postObj->MsgType){
                case 'text':
                    $content = $postObj->Content;           
                    break;
                case 'image':
                    $content = "图片";
                    break;
                case 'voice':
                    $content = $postObj->Recognition;
                    break;
            }

            $jsonData = '{
                "reqType": 0,
                "perception": {
                    "inputText": {
                        "text": "'.$content.'",
                    },
                    "inputImage": {
                        "url":  "'.$postObj->PicUrl.'"
                    },
                    "selfInfo": {
                        "location": {
                            "city": "深圳",
                            "province": "广东省",
                            "street": "龙翔大道2188号"
                        }
                    }
                },
                "userInfo": {
                    "apiKey": "'.$appkey.'",
                    "userId": "'.$userid.'",
                }
            }';
                       
            $jsonObj = json_decode($this->httpPost($appurl, $jsonData));

            $replyStr = $jsonObj->results[0]->values->text;
            $content = "";
            $hasPict = false;
            foreach($jsonObj->results as $replyObj){
                switch($replyObj->resultType){
                    case 'text':
                        $content .= $replyObj->values->text;
                        break;
                    case 'url':
                        $content .= $replyObj->values->url;
                        break;
                    case 'image':
                        $hasPict = true;
                        $picLink = $replyObj->values->image;
                        break;
                    case 'news':
                        for($i=0; $i<7 ; $i++){
                            $content .= "\n";
                            $content .= $replyObj->values->news[$i]->name;
                            $content .= $replyObj->values->news[$i]->detailurl."\n";
                            $content .= "------------------------";
                        }
                        break;
                }
            }

            if($hasPict){
                $newsArr = Array();
                $newsArr[Articles] = Array();
                $newsArr[Articles][0] = Array(
                    "Title" => "斗图卡",
                    "Description" => $content,
                    "PicUrl" => $picLink,
                    "Url" => $picLink,
                );
                $newsArr[ArticleCount] = 1;
                $this->sendNews($postObj, $newsArr);
            }else
                $this->sendText($postObj, $content);
        }

        public function sendSMS($postObj, $mobile){
            $tampleid = "145255";
            #$tampleid = "143101";
            $appid = "1400099389";
            $appkey = "5e4064711e7ef05080a10c1a3f97efd4";

            $timestamp = time();
            $random = rand(1010, 9899);
            $url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=$appid&random=$random";
            $sig = hash("sha256","appkey=$appkey&random=$random&time=$timestamp&mobile=$mobile");

            $data = '{
                "ext": "",
                "extend": "",
                "params": [
                    "'.$random.'",
                    "5"
                ],
                "sig": "'.$sig.'",
                "sign": "腾讯云",
                "tel": {
                    "mobile": "'.$mobile.'",
                    "nationcode": "86"
                },
                "time": '.$timestamp.',
                "tpl_id": '.$tampleid.'
            }';
            #$this->sendText($postObj, $data);
            $rep = $this->httpPost($url, $data);
            return json_decode($rep);
        }

        public function httpGet($url){
            $con = curl_init((string)$url);
            curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($con, CURLOPT_TIMEOUT, 5);
            return curl_exec($con);
        }

        public function httpPost($url, $data){
            $con = curl_init((string)$url);
            curl_setopt($con, CURLOPT_POST, true);
            curl_setopt($con, CURLOPT_POSTFIELDS, $data);
            curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($con, CURLOPT_TIMEOUT, 5);
            return curl_exec($con);
        }

        public function setMemcache($key, $value, $time=0){
            $mem = new Memcache();
            $mem->connect("127.0.0.1","9833") or die("cannot connect memcache");
            $mem->set($key, $value, 0, $time);
        }

        public function getMemcache($key){
            $mem = new Memcache();
            $mem->connect("127.0.0.1","9833") or die("cannot connect memcache");
            return $mem->get($key);
        }

        public function getSqlSearch($key){
            mysql_connect("localhost", "root", "root") or 
                die("cannot connect sqlserver");
            mysql_select_db("80s");
            mysql_set_charset("utf8");

            $key = str_replace("'", "", $key);
            $key = str_replace('"', "", $key);
            $key = str_replace('%', "", $key);
            $key = str_replace('_', "", $key);
            $key = str_replace('&', "", $key);
            $key = str_replace('|', "", $key);

            $sql = "select * from movies where name like '%".$key."%' limit 20";
            return mysql_query($sql);
        }

        public function getFileContent($key){
            $content =  unserialize(file_get_contents("data/data.txt"));
            if($key){
                return $content[$key];
            }else{
                $txt = "";
                foreach($content as $key => $value){
                    $txt .= "[".$key."]\n$value\n\n";
                }
                return $txt;
            }
            return $content;
        }

        public function setFileContent($key, $value){
            if(!$key || !$value) return 'no';
            $content = file_get_contents('data/data.txt');
            $data = unserialize($content);
            $data[$key] = $value;
            $data = serialize($data);
            file_put_contents('data/data.txt', $data);
            return 'ok';
        }

        public function clearFileContent(){
            file_put_contents('data/data.txt', '');
        }

    }
?>
