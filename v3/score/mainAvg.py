#!coding: utf-8

from AvgCrawlClass import AvgScoreSpider

def startCrawl(className, classCode, studentCount, needUpdate):
    spider = AvgScoreSpider(className, classCode, studentCount);
    if needUpdate:
        spider.setUpdate();
    spider.CrawlAvg();
    spider.SaveFile();


if __name__ == "__main__":
    dataList = [];
    startCrawl(u"16网技3-1班", u"16010501", 50, 1);
    startCrawl(u"16网技3-2班", u"16010502", 50, 0);
    startCrawl(u"16网技3-3班", u"16010503", 50, 0);

    startCrawl(u"16应用3-1班", u"16030101", 50, 0);
    startCrawl(u"16应用3-2班", u"16030102", 50, 0);
    startCrawl(u"16应用3-3班", u"16030103", 50, 0);

    startCrawl(u"16信安3-1班", u"16090301", 50, 0);
    startCrawl(u"16信安3-2班", u"16090302", 50, 0);
    startCrawl(u"16信安3-3班", u"16090303", 50, 0);

    startCrawl(u"16网管3-1班", u"16090201", 50, 0);
    startCrawl(u"16网管3-2班", u"16090202", 50, 0);
    startCrawl(u"16网管3-3班", u"16090203", 50, 0);


    startCrawl(u"17网技3-1班", u"17010501", 50, 0);
    startCrawl(u"17网技3-2班", u"17010502", 50, 0);
    startCrawl(u"17网技3-3班", u"17010503", 50, 0);

    startCrawl(u"17应用3-1班", u"17030101", 50, 0);
    startCrawl(u"17应用3-2班", u"17030102", 50, 0);
    startCrawl(u"17应用3-3班", u"17030103", 50, 0);

    startCrawl(u"17信安3-1班", u"17090301", 50, 0);
    startCrawl(u"17信安3-2班", u"17090302", 50, 0);
    startCrawl(u"17信安3-3班", u"17090303", 50, 0);

    startCrawl(u"17网管3-1班", u"17090201", 50, 0);
    startCrawl(u"17网管3-2班", u"17090202", 50, 0);
    startCrawl(u"17网管3-3班", u"17090203", 50, 0);

