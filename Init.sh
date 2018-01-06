#!/bin/bash
#做一些初始化的工作

#读取配置文件变量
source ./core/gohan.conf 2> /dev/null

#create user and homedir
sudo useradd -m judge


sudo mkdir $OJ_DATADIR
sudo chown -R judge $OJ_WORKDIR

#对服务器用户设置特殊权限
sudo setfacl -b -R $OJ_DATADIR
sudo setfacl -m u:$SERVER_USER:rwx -R $OJ_DATADIR
sudo setfacl -m u:$SERVER_USER:rwx /home/judge
