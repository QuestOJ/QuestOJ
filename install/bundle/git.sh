#!/bin/bash

res=`./switch`

if [ "$res" = "github" ] 
then git clone https://github.com/QuestOJ/QOJ.git qoj
else git clone https://gitee.com/QuestOJ/QOJ.git qoj
fi
cd qoj/ && git submodule init && git submodule update