#!/usr/bin/python
#!coding:utf-8

import MySQLdb

db = MySQLdb.connect("localhost", "root", "root", "80s", charset="utf8");

cur = db.cursor();
cur.execute("select * from movies");
datas = cur.fetchall();

for data in datas:
    print "======================";
    print data[0];
    print data[1];
    print data[2];
    for i in   data[3].split("|||"):
        print i;
    print data[4];
    print 


db.close();
