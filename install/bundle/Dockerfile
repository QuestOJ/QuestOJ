FROM ubuntu:18.04
LABEL maintainer="QuestOJ postmaster@questoj.cn"
ARG CLONE_ADDFLAG

WORKDIR /opt
RUN mkdir /var/www

#Change sources mirror
COPY ./sources.list /etc/apt/sources.list
RUN rm -rf /var/lib/apt/lists/*

#Clone the QuestOJ
RUN apt-get update && apt-get upgrade -y && apt-get install -y git
COPY ./git.sh /opt
COPY ./switch /opt
RUN bash ./git.sh

WORKDIR /opt
RUN cd qoj/install/bundle && sh install.sh -p && echo "\
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