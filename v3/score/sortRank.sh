cat $1 | awk -F, '{avg[$1]=$5;}END{for(i in avg)print i"\t"avg[i]|"sort -r -n -k2";}'
