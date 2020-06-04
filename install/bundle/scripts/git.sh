#!/bin/bash

res=`./switch`

echo switch to $res

if [ "$res" = "github" ] 
then git clone https://github.com/QuestOJ/QuestOJ.git qoj
else git clone https://gitee.com/QuestOJ/QuestOJ.git qoj
fi

cd qoj/ && git submodule init && git submodule update