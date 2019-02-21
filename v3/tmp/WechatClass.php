<?php
    class Wechat{
        public function __construct($token="", $appid="", $appsecret=""){
            $this->token = $token;
            $this->appid = $appid;
            $this->appsecret = $appsecret;
        }


        public function chkSig(){
            $token = $this->token;
            $signature = $_GET['signature'];
            $timestamp = $_GET['timestamp'];
            $nonce = $_GET['nonce'];
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
            return $postObj;
        }


        public function sendText($postObj, $content){
            $xml = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%d</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";
            echo sprintf($xml, $postObj->FromUserName, $postObj->ToUserName, time(), $content);
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
        }

        public function sendRotbot($postObj){
            $url = "http://openapi.tuling123.com/openapi/api/v2";
            $apiKey = "5218c6192a7b49b5b36a0a921def3ffe";

            if($postObj->MsgType == "image"){
                $idx = rand(0,2);
                $arr = Array(
                    0 => "斗图", 1 => "斗图!",
                    2 => "来斗图",
                );
                $content = $arr[$idx];
            }
            if($postObj->MsgType == "voice"){
                $content = $postObj->Recognition;
            }else
                $content = $postObj->Content;

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
                    "apiKey": "'.$apiKey.'",
                    "userId": "demonxian3"
                }
            }';

            $jsonObj = json_decode($this->httpPost($url, $jsonData));
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

        public function getUserInfo($postObj){
            $openid = $postObj->FromUserName;
            $actoken = $this->getAcToken();
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$actoken&openid=$openid";
            $obj = json_decode($this->httpGet($url));
            return $obj;
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

        public function httpGet($url){
            $con = curl_init((string)$url);
            curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($con, CURLOPT_TIMEOUT, 5);
            return curl_exec($con);
        }

        public function httpPost($url, $data, $headers=""){
            $con = curl_init((string)$url);
            curl_setopt($con, CURLOPT_POST, true);
            curl_setopt($con, CURLOPT_POSTFIELDS, $data);
            curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($con, CURLOPT_TIMEOUT, 5);
            if($headers){
                curl_setopt($con, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($con, CURLOPT_HEADER, false);
            }
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
    }
?>
