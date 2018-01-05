#!/bin/bash
cd ./core
git clone https://github.com/redis/hiredis
cd hiredis
sudo make
sudo make install
sudo cp libhiredis.so /usr/local/lib/

if [ -d /usr/local/lib64 ];then
  sudo cp /usr/local/lib/libhiredis.so.0.13 /usr/local/lib64
fi

sudo ldconfig
