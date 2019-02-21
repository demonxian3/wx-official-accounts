#!coding: utf-8
import sys
import urllib2
import json
import time
import os

class AvgScoreSpider:
    def __init__(self, className, classCode, studentCount):
        self.result = "";
        self.className = className;
        self.classCode = classCode;
        self.studentCount = studentCount;
        self.stuNumber = [];
        self.MakeNum();
        self.isCrawl();
        

    def MakeNum(self):
        for i in range(1, self.studentCount + 1):
            if len(str(i)) < 2:
                self.stuNumber.append(self.classCode + "0" + str(i));
            else:
                self.stuNumber.append(self.classCode + str(i));
        pass


    def CrawlAvg(self):
        if self.hasCrawl:
            print "Already crawl the score";
            return;

        result = u"姓名,学期1,学期2,学年\n".encode("utf-8");
        for i in self.stuNumber:
            url = "http://score.sziitjx.cn/index/sziit/score/sid/" + i;
            req = urllib2.Request(url);
            rep = urllib2.urlopen(req, timeout=5);
            html = rep.read();
            avgDict = json.loads(html);
        
            try:
                name = avgDict["data"]["score"][0]["xingming"].encode("utf-8");
                for data in avgDict['data']['avgScore']:
                    if data["xuenian"] == "2017-2018":
                        if data["xueqi"] == "1":
                            xueqi1 = float(data["pingjunfen"]);
                        
                        if data["xueqi"] == "2":
                            xueqi2 = float(data["pingjunfen"]);
        
                xuenian = (xueqi1 + xueqi2) / 2;
                tmp = ",".join([name, i.encode("utf-8"), str(xueqi1), str(xueqi2), str(xuenian)]);
                result += tmp + "\n";
                print tmp;
            except:
                pass;
            #time.sleep(1);
        self.result = result


    def SaveFile(self, filename="", result=""):
        if not filename:
            filename = self.className + ".txt";
        if not result:
            result = self.result;

        if self.hasCrawl:
            print filename + " has finished crawl already";
            return
        else:
            fp = open(filename, "w");
            fp.write(result);
            fp.close();
            self.hasCrawl = True;


    def isCrawl(self, filename=""):
        if not filename:
            filename = self.className + ".txt";

        if os.path.exists(filename) and os.path.getsize(filename) > 0:
            self.hasCrawl = True;
        else:
            self.hasCrawl = False;


    def setUpdate(self):
        self.hasCrawl = False;
