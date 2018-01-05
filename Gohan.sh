#!/bin/bash

SERVER_USER=www-data #www-data对应的是apache
OJ_PROGRAM=/home/judge/Gohan

#create user and homedir
sudo useradd -m judge

source ./core/gohan.conf 2> /dev/null

sudo mkdir $OJ_DATADIR
sudo chown -R judge $OJ_WORKDIR

#对服务器用户设置特殊权限
sudo setfacl -b -R $OJ_DATADIR
sudo setfacl -m u:$SERVER_USER:rwx -R $OJ_DATADIR
setfacl -m u:nginx:rwx /home/judge

sudo gcc ./core/gohan_compiler.c -o $OJ_COMPILER

sudo gcc ./core/gohan_judger.c -o $OJ_JUDGER

sudo gcc ./core/gohan_comparer.c -o $OJ_COMPARER

sudo gcc -o $OJ_PROGRAM ./core/gohan_redis.c ./core/json/cJSON.c -lhiredis -lpthread

sudo $OJ_PROGRAM
