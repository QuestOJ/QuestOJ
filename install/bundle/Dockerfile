FROM ubuntu:20.04
LABEL maintainer="QuestOJ postmaster@questoj.cn"

WORKDIR /opt

#Change sources mirror
COPY ./scripts/sources.list /etc/apt/sources.list
RUN rm -rf /var/lib/apt/lists/* && apt-get update && apt-get upgrade -y && apt-get install -y git

#Clone the QuestOJ
RUN git clone https://github.com/QuestOJ/QuestOJ.git qoj && cd qoj/ && git submodule init && git submodule update

#Install
RUN cd qoj/install/bundle/scripts && sh install.sh -p && echo "\
#!/bin/sh\n\
\n\
service ntp start\n\
service apache2 start\n\
service mysql start\n\
\n\
su local_main_judger -c '~/judge_client/judge_client start' \n\
\n\
exec bash\
" >/opt/up && chmod +x /opt/up

ENV LANG=C.UTF-8 TZ=Asia/Shanghai
EXPOSE 80
CMD /opt/up