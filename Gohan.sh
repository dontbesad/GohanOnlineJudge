#!/bin/bash
source ./core/gohan.conf 2> /dev/null

if [ ! -d $OJ_WORKDIR ]; then
    mkdir -p $OJ_WORKDIR
fi

if [ ! -d $OJ_DATADIR ]; then
    mkdir -p $OJ_DATADIR
fi
sudo chmod -R 0777 $OJ_DATADIR

gcc ./core/gohan_compiler.c -o $OJ_COMPILER

gcc ./core/gohan_judger.c -o $OJ_JUDGER

gcc ./core/gohan_comparer.c -o $OJ_COMPARER

gcc ./core/gohan.c -o ./Gohan -lpthread -lmysqlclient

./Gohan
