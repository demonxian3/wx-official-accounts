#!/usr/bin/env python2
from __future__ import print_function
def cpr(content, fc="f0", bc="", e="\n"):
    rcs = "";

    if fc == "f0":
        rcs = "\033[30m" ;
    elif fc == "f1":
        rcs = "\033[31m" ;
    elif fc == "f2":
        rcs = "\033[32m" ;
    elif fc == "f3":
        rcs = "\033[33m" ;
    elif fc == "f4":
        rcs = "\033[34m" ;
    elif fc == "f5":
        rcs = "\033[35m" ;
    elif fc == "f6":
        rcs = "\033[36m" ;
    elif fc == "f7":
        rcs = "\033[37m" ;
    elif fc == "f8":
        rcs = "\033[38m" ;
    elif fc == "f9":
        rcs = "\033[39m" ;

    if bc == "b0":
        rcs += "\033[40m";
    if bc == "b1":
        rcs += "\033[41m";
    if bc == "b2":
        rcs += "\033[42m";
    if bc == "b3":
        rcs += "\033[43m";
    if bc == "b4":
        rcs += "\033[44m";
    if bc == "b5":
        rcs += "\033[45m";
    if bc == "b6":
        rcs += "\033[46m";
    if bc == "b7":
        rcs += "\033[47m";
    if bc == "b8":
        rcs += "\033[48m";
    if bc == "b9":
        rcs += "\033[49m";
    
    print(rcs + content + "\033[0m", end=e);

if __name__ == "__main__":
    cpr("hello world", "f6", "b5");
