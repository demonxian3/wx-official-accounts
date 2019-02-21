<?php

        include("WechatClass.php");
        $wc = new Wechat();
        $url = "http://openapi.tuling123.com/openapi/api/v2";

        $userId = "Demonxian3";
        $apikey = "5218c6192a7b49b5b36a0a921def3ffe";


        $jsonData = '{
        "reqType": 0,
        "perception": {
            "inputText": {
                "text": "新闻",
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
            "apiKey": "'.$apikey.'",
            "userId": "'.$userId.'"
        }
        }';

        $res = json_decode($wc->httpPost($url, $jsonData));   

        for($i=0; $i<10; $i++){
            $v = $res->results[1]->values->news[$i];
            echo $v->name ."\n";
            echo $v->detailurl."\n";
        }
?>
