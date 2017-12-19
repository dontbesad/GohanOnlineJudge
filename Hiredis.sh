#!/bin/bash
cd ./core
git clone https://github.com/redis/hiredis
cd hiredis
sudo make
sudo make install
sudo cp libhiredis.so /usr/local/lib/
