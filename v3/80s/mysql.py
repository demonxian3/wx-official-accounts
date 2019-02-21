#!/usr/bin/python
#!coding:utf-8

import MySQLdb

db = MySQLdb.connect("localhost", "root", "root", "80s", charset="utf8");


cur = db.cursor();
cur.execute("desc movies");
data = cur.fetchone();
print data;

data = cur.fetchall();
print data;

db.close();
