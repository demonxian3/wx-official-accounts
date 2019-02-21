<?php
    class Handle{
        
        public function __construct(){
            $content = "嗨, %s \n";
            $content .= "******************************\n";
            $content .= "*  欢迎来到Demon贤公众号  *\n";
            $content .= "******************************\n";
            $content .= "输入：点歌  可选择歌曲\n";
            $content .= "输入：学号  可查询成绩\n";
            $content .= "输入：斗图  和机器人斗图\n";
            $content .= "输入：笑话  看机器人讲笑话\n";
            $content .= "输入：快递单号  查快递\n";
            $content .= "...\n\n";
            $content .= "等等，更多功能如下\n支持语音聊天\n";

            $content .= "机器人支持：\n*****************************";
            $content .= "\n翻译,闲聊,笑话,讲故事,斗图,星座,\n顺口溜,脑筋急转弯,";
            $content .= "数学计算,生活百科,问答百科,新闻资讯,成语接龙,歇后语,绕口令,";
            $content .= "查快递,查天气,查股票,查市编号,查日期,查列车,查成绩,查排名,语料库,点歌\n";
            $this->info = $content;
            $this->user = null;
        }

        public function main($postObj){
            switch($postObj->MsgType){
                case 'text':
                    return $this->textHandle($postObj);
                case 'voice':
                    return Array("robot");
                case 'image':
                    return Array("robot");
                case 'video':
                    return Array("video", $postObj->MediaId);
                case 'event':
                    return $this->eventHandle($postObj);
            }
        }

        public function textHandle($postObj){
            switch($postObj->Content){
                case '点歌':
                    $musicArr = $this->getMusic();
                    $content = "输入: music + 数字编号 选择歌曲:\n如: music1\n";
                    for($i=0; $i<count($musicArr);$i++)
                        $content .= ($i+1) .".". $musicArr[$i][Title] ."\n";
                    return Array("text", $content);

               #case '新闻':
               #     $newsArr = $this->getNews();
               #     return Array("news", $newsArr);

                case '学号':
                    $content = "输入您的学号，即可查询成绩\n如：1601050132\n";
                    return Array("text", $content);

                default:
                    if(preg_match('/^music(\d{1,})$/i', $postObj->Content, $matches)){
                        $musicArr = $this->getMusic();
                        $num = $matches[1] - 1;
                        if($num < 0 )return false;
                        return Array("music", $musicArr[$num]);
                    }

                    if(preg_match('/^(\d{10})$/', $postObj->Content, $matches)){
                        $content = shell_exec("python score/stuMark.py ".$matches[1]);
                        return Array("text", $content);
                    }

                    else
                        #$txt =  sprintf($this->info, $this->userObj->nickname);
                        #return Array("text", $txt);
                        return Array("robot");
            }
        }

        public function eventHandle($postObj){
            switch($postObj->Event){
                case 'subscribe':
                    $txt =  sprintf($this->info, $this->userObj->nickname);
                    return Array("text", $txt);

                case 'CLICK':
                    switch($postObj->EventKey){
                        case 'rank':
                            $content = "姓名\t平均分(2017-2018学年)\n";
                            $content .= shell_exec("bash score/avgRank.sh score/avgRank.txt" );
                            return Array("text", $content);

                        case 'support':
                            $content = "支付宝发红包啦！即日起还有机会额外获得余额宝";
                            $content .= "消费红包！长按复制此消息，打开最新版支付宝就能领取！AnqHPz17Kz";
                            return Array("text", $content);

                        case 'info':
                            $txt =  sprintf($this->info, $this->userObj->nickname);
                            return Array("text", $txt);
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
                $musicArr[$id][MusicURL] = "http://d3m0n.hopto.org/v3/data/music/". $v;
                $musicArr[$id][HQMusicUrl] = "http://d3m0n.hopto.org/v3/data/music/". $v;
                ++$id;
            }
            return $musicArr;
        }

        public function getNews(){
            $newsArr = Array();
            $newsArr[Articles] = Array();
            $newsArr[Articles][0] = Array(
                "Title" => "新闻测试 1",
                "Description" => "新闻描述 1",
                "PicUrl" => "http://d3m0n.hopto.org/v3/data/image/timg.jpg",
                "Url" => "http://www.baidu.com",
            );
            $newsArr[Articles][1] = Array(
                "Title" => "新闻测试 2",
                "Description" => "新闻描述 2",
                "PicUrl" => "http://d3m0n.hopto.org/v3/data/image/timg3.jpg",
                "Url" => "http://www.qq.com",
            );
            $newsArr[Articles][2] = Array(
                "Title" => "新闻测试 3",
                "Description" => "新闻描述 3",
                "PicUrl" => "http://d3m0n.hopto.org/v3/data/image/timg2.jpg",
                "Url" => "http://www.qq.com",
            );
            $newsArr[ArticleCount] = 3;
            return $newsArr;
        }

        public function setUserInfo($userObj){
            $userObj->sex = $userObj->sex == 1?"男":"女";
            $this->userObj = $userObj;
            #$content  = "名称：" . $userObj->nickname   ."\n";
            #$content .= "性别：" . $userObj->sex        ."\n";
            #$content .= "城市：" . $userObj->city       ."\n";
            #$content .= "省份：" . $userObj->province   ."\n";
            #$content .= "国家：" . $userObj->country    ."\n";
        }
    }
?>
