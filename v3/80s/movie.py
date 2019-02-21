#!/usr/bin/python2
#!coding:utf-8

import sys
import time
import MySQLdb
import requests
from lxml import etree
from mycolor import cpr


def goPage(url):
    lnkSpt = "|||";
    rep = requests.get(url);
    html = rep.content;
    tree = etree.HTML(html);

    resource = "";
    inputs = tree.xpath("//input[@value]");
    links = tree.xpath("//a[@href][@thunderpid]");

    for li in inputs:
        if "://" in li.xpath("@value")[0]:
            resource += li.xpath("@value")[0].encode("utf-8") + lnkSpt;

    for li in links:
        resource += li.xpath("@href")[0].encode("utf-8") + lnkSpt;
    
    return resource;


def stgTorrent(code, name, refer, torrent, imgurl):
    try:
        db = MySQLdb.connect("localhost", "root", "root", "80s", charset="utf8");
        sql = 'insert into movies values('+code+', "'+name+'","'+refer+'","'+torrent+'","'+imgurl+'")';

        cur = db.cursor();
        res = cur.execute(sql);
        
        if res:
            print "insert ok!";
        else:
            print "insert failed";
        time.sleep(1);
    except:
        print "maybe movie is exists";



def main():
    for i in range(300):
        domain = "https://www.80s.tw/";
        url =  "https://www.80s.tw/movie/list/-----p/"+str(i);
        
        rep = requests.get(url);
        html = rep.content;
        tree = etree.HTML(html);
        links = tree.xpath("//ul[@class='me1 clearfix']/li");
        
        
        for li in links:
            name = li.xpath("a/@title")[0].encode("utf-8");
            href = domain + li.xpath("a/@href")[0];
            pict = "http:" + li.xpath("a/img/@src")[0];
        
            print "==============================";
            cpr(name, "f1", e="\t");
            cpr(href, "f2", e="\t");
            cpr(pict, "f3")

            code = href.split("/")[-1];

            res = goPage(href);
            stgTorrent(code, name, href, res, pict);

    time.sleep(1);

if __name__ == "__main__":
    main();
