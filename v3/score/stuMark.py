#!coding: utf-8
import sys
import urllib2
import json



if len(sys.argv) < 2:
    print "please specail student number";
    sys.exit();

url = "http://score.sziitjx.cn/index/sziit/score/sid/" + sys.argv[1];
req = urllib2.Request(url);
rep = urllib2.urlopen(req);
html = rep.read();
avgDict = json.loads(html);

studentName = avgDict["data"]["score"][0]["xingming"];
studentCode = sys.argv[1];

resultStr = studentName + "(" + studentCode + ")\n";
resultStr += "======= 2016-2017 =======\n";
resultStr += u"学期    成绩    名称\n";
for i in avgDict["data"]["score"]:
    if i["xuenian"] == "2016-2017":
        resultStr += "    ".join([i["xueqi"],i["zongpingchengji"],i["kechengmingcheng"]]) + "\n";
        

resultStr += "======= 2017-2018 =======\n";
for i in avgDict["data"]["score"]:
    if i["xuenian"] == "2017-2018":
        resultStr += "    ".join([i["xueqi"],i["zongpingchengji"],i["kechengmingcheng"]]) + "\n";

print resultStr.encode("utf-8");
