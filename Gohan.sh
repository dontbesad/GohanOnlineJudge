#!/bin/bash
#运行核心

#读取配置文件变量
source ./core/gohan.conf 2> /dev/null

sudo gcc ./core/gohan_compiler.c -o $OJ_COMPILER

sudo gcc ./core/gohan_judger.c -o $OJ_JUDGER

sudo gcc ./core/gohan_comparer.c -o $OJ_COMPARER

sudo gcc -o $OJ_PROGRAM ./core/gohan_redis.c ./core/json/cJSON.c -lhiredis -lpthread

sudo $OJ_PROGRAM
