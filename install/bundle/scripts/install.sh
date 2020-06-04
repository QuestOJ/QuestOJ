#!/bin/bash
set -e
genRandStr(){
    cat /dev/urandom | tr -dc [:alnum:] | head -c $1
}
#Set some vars
_database_password_=$(genRandStr 15)
_judger_socket_port_=2333
_judger_socket_password_=$(genRandStr 32)
_main_judger_password_=$(genRandStr 32)
_api_token_=$(genRandStr 32)
_api_secret_=$(genRandStr 64)

getAptPackage(){
    printf "\n\n==> Getting environment packages\n"
    #Set MySQL root password
    export DEBIAN_FRONTEND=noninteractive
    (echo "mysql-server mysql-server/root_password password $_database_password_";echo "mysql-server mysql-server/root_password_again password $_database_password_") | debconf-set-selections
    #Update apt sources and install
    dpkg -i /opt/qoj/install/bundle/3rdparty/libv8-7.5_7.5.288.30-ppa1_bionic_amd64.deb
    dpkg -i /opt/qoj/install/bundle/3rdparty/libv8-7.5-dev_7.5.288.30-ppa1_bionic_amd64.deb
    apt-get install -y sudo vim ntp zip unzip curl wget apache2 libapache2-mod-xsendfile libapache2-mod-php php php-curl php-dev php-pear php-zip php-mysql php-mbstring mysql-server cmake fp-compiler re2c libyaml-dev python3 python3-requests
    #Install PHP extensions
    pecl install /opt/qoj/install/bundle/3rdparty/yaml-2.0.4.tgz 
    unzip /opt/qoj/install/bundle/3rdparty/v8js-a3eab09e96496fe232447f38780fb3f6c17876ef.zip -d /opt/qoj/install/bundle/3rdparty
    cd /opt/qoj/install/bundle/3rdparty/v8js-php7
    phpize && ./configure --with-php-config=/usr/bin/php-config --with-v8js=/opt/libv8-7.5 && make install && cd -
}

setLAMPConf(){
    printf "\n\n==> Setting LAMP configs\n"
    #Set Apache QOJ site conf
    cat >/etc/apache2/sites-available/000-qoj.conf <<QOJEOF
<VirtualHost *:80>
	# The ServerName directive sets the request scheme, hostname and port that
	# the server uses to identify itself. This is used when creating
	# redirection URLs. In the context of virtual hosts, the ServerName
	# specifies what hostname must appear in the request's Host: header to
	# match this virtual host. For the default virtual host (this file) this
	# value is not decisive as it is used as a last resort host regardless.
	# However, you must set it for any further virtual host explicitly.
	#ServerName www.example.com

    ServerName 127.0.0.1
	ServerAdmin postmaster@questoj.cn
	DocumentRoot /var/www/qoj

	# Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
	# error, crit, alert, emerg.
	# It is also possible to configure the loglevel for particular
	# modules, e.g.
	#LogLevel info ssl:warn

	ErrorLog ${APACHE_LOG_DIR}/uoj_error.log
	CustomLog ${APACHE_LOG_DIR}/uoj_access.log combined

        XSendFile On
        XSendFilePath /var/uoj_data
        XSendFilePath /var/www/qoj/app/storage
        XSendFilePath /home/local_main_judger/judge_client/uoj_judger/include

	RemoteIPHeader X-Forward-For
	RemoteIPProxiesHeader X-Forwarded-By

	# For most configuration files from conf-available/, which are
	# enabled or disabled at a global level, it is possible to
	# include a line for only one particular virtual host. For example the
	# following line enables the CGI configuration for this host only
	# after it has been globally disabled with "a2disconf".
	#Include conf-available/serve-cgi-bin.conf
</VirtualHost>
QOJEOF
    cat >/etc/apache2/sites-available/001-manage.conf <<QOJEOF
<VirtualHost *:80>
	# The ServerName directive sets the request scheme, hostname and port that
	# the server uses to identify itself. This is used when creating
	# redirection URLs. In the context of virtual hosts, the ServerName
	# specifies what hostname must appear in the request's Host: header to
	# match this virtual host. For the default virtual host (this file) this
	# value is not decisive as it is used as a last resort host regardless.
	# However, you must set it for any further virtual host explicitly.
	#ServerName www.example.com

    ServerName 127.0.0.2
	ServerAdmin postmaster@questoj.cn
	DocumentRoot /var/www/qoj_manage

	# Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
	# error, crit, alert, emerg.
	# It is also possible to configure the loglevel for particular
	# modules, e.g.
	#LogLevel info ssl:warn

	ErrorLog ${APACHE_LOG_DIR}/qoj_manage_error.log
	CustomLog ${APACHE_LOG_DIR}/qoj_manage_access.log combined

	# For most configuration files from conf-available/, which are
	# enabled or disabled at a global level, it is possible to
	# include a line for only one particular virtual host. For example the
	# following line enables the CGI configuration for this host only
	# after it has been globally disabled with "a2disconf".
	#Include conf-available/serve-cgi-bin.conf
</VirtualHost>
QOJEOF
    #Enable modules and make QOJ site conf enabled
    a2ensite 000-qoj.conf && a2ensite 001-manage.conf && a2dissite 000-default.conf
    cat >/etc/apache2/mods-available/remoteip.conf << QOJEOF
RemoteIPHeader X-Forward-For
RemoteIPProxiesHeader X-Forwarded-By
QOJEOF
    a2enmod remoteip 
    a2enmod rewrite headers && sed -i -e '172s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
    #Create UOJ session save dir and make PHP extensions available
    mkdir --mode=733 /var/lib/php/uoj_sessions && chmod +t /var/lib/php/uoj_sessions
    sed -i -e '912a\extension=v8js.so\nextension=yaml.so' /etc/php/7.4/apache2/php.ini
    #Set MySQL user directory and connection config
    usermod -d /var/lib/mysql/ mysql
    cat >/etc/mysql/mysql.conf.d/qoj_mysqld.cnf <<QOJEOF
[mysqld]
default-time-zone='+8:00'
character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci
init_connect='SET NAMES utf8mb4'
init_connect='SET collation_connection = utf8mb4_unicode_ci'
skip-character-set-client-handshake
sql-mode=ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
QOJEOF
}

setWebConf(){
    printf "\n\n==> Setting web files\n"
    #Set webroot path
    ln -sf /opt/qoj/src/web_oj /var/www/qoj
    ln -sf /opt/qoj/src/web_manage /var/www/qoj_manage
    mkdir -p /var/www/qoj/app/storage/submission
    mkdir -p /var/www/qoj/app/storage/tmp
    chown -R www-data /var/www/qoj/app/storage
    chown -R www-data /var/www/qoj_manage
    #Set web config file
    php -a <<QOJEOF
\$config = include '/var/www/qoj/app/.default-config.php';
\$config['database']['password']='$_database_password_';
\$config['judger']['socket']['port']='$_judger_socket_port_';
file_put_contents('/var/www/qoj/app/.config.php', "<?php\nreturn ".str_replace('\'_httpHost_\'','UOJContext::httpHost()',var_export(\$config, true)).";\n");
QOJEOF


    cat >/opt/qoj/src/web_manage/data/config/config.inc.php <<QOJEOF
<?php
    
    define("MYSQL_IP", "localhost");
    define("MYSQL_PORT", "3306");
    define("MYSQL_USERNAME", "root");
    define("MYSQL_PASSWORD", "$_database_password_");
    define("MYSQL_DATABASE", "manage");
    define("MYSQL_TABLE_PREFIX", "manage");

    define("AUTH_KEY", "$(genRandStr 64)");
    define("SESSION_SALT", "$(genRandStr 64)");
    define("COOKIE_SALT", "$(genRandStr 64)");
    define("LOG_SALT", "$(genRandStr 64)");

    define("OJ_URL", "http://127.0.0.1");
    define("OJ_MYSQL_IP", "localhost");
    define("OJ_MYSQL_PORT", "");
    define("OJ_MYSQL_USERNAME", "root");
    define("OJ_MYSQL_PASSWORD", "$_database_password_");
    define("OJ_MYSQL_DATABASE", "app_uoj233");

    define("API_TOKEN", "$_api_token_");
    define("API_SECRET", "$_api_secret_");
?>
QOJEOF
    cp /opt/qoj/src/web_manage/data/config/config.inc.php  /opt/qoj/src/web_manage/event/data/config.inc.php
    chown -R www-data:www-data /opt/qoj/src/web_oj
    chown -R www-data:www-data /opt/qoj/src/web_manage

    #Import MySQL database
    service mysql restart
    mysql -u root --password=$_database_password_ < /opt/qoj/install/bundle/sql/app_uoj233.sql
    mysql -u root --password=$_database_password_ < /opt/qoj/install/bundle/sql/manage.sql
    mysql -u root --password=$_database_password_ manage < /opt/qoj/install/bundle/sql/manage_system.sql
    mysql -uroot -p$_database_password_ app_uoj233 -e "insert into api (\`token\`, \`secret\`, \`description\`) VALUES ('$_api_token_', '$_api_secret_', 'Server Manage Platform');"
    echo "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$_database_password_';" | mysql -u root --password=$_database_password_
}

setJudgeConf(){
    printf "\n\n==> Setting judger files\n"
    #Add local_main_judger user
    useradd -m local_main_judger && usermod -a -G www-data local_main_judger
    #Set uoj_data path
    mkdir -p /var/uoj_data/upload
    chown -R www-data:www-data /var/uoj_data
    #Compile uoj_judger and set runtime
    mkdir -p /home/local_main_judger
    ln -sf /opt/qoj/src/judger /home/local_main_judger/judge_client
    mkdir -p /home/local_main_judger/judge_client/uoj_judger/result
    mkdir -p /home/local_main_judger/judge_client/uoj_judger/work
    chown -R local_main_judger:local_main_judger /home/local_main_judger
    chown -R local_main_judger:local_main_judger /opt/qoj/src/judger
    su local_main_judger <<EOD
ln -s /var/uoj_data /home/local_main_judger/judge_client/uoj_judger/data
cd /home/local_main_judger/judge_client && chmod +x judge_client
cat >uoj_judger/include/uoj_work_path.h <<QOJEOF
#define UOJ_WORK_PATH "/home/local_main_judger/judge_client/uoj_judger"
#define UOJ_JUDGER_BASESYSTEM_UBUNTU1804
QOJEOF
cd uoj_judger && make -j$(($(nproc) + 1))
EOD
    #Set judge_client config file
    cat >/home/local_main_judger/judge_client/.conf.json <<QOJEOF
{
    "uoj_protocol": "http",
    "uoj_host": "127.0.0.1",
    "judger_name": "main_judger",
    "judger_password": "_main_judger_password_",
    "socket_port": $_judger_socket_port_,
    "socket_password": "_judger_socket_password_"
}
QOJEOF
    chmod 600 /home/local_main_judger/judge_client/.conf.json && chown local_main_judger /home/local_main_judger/judge_client/.conf.json
}

initProgress(){
    printf "\n\n==> Doing initial config and start service\n"
    #Replace password placeholders
    sed -i -e "s/_main_judger_password_/$_main_judger_password_/g" -e "s/_judger_socket_password_/$_judger_socket_password_/g" /opt/qoj/src/judger/.conf.json
    sed -i -e "s/salt0/$(genRandStr 32)/g" -e "s/salt1/$(genRandStr 16)/g" -e "s/salt2/$(genRandStr 16)/g" -e "s/salt3/$(genRandStr 16)/g" -e "s/_judger_socket_password_/$_judger_socket_password_/g" /var/www/qoj/app/.config.php
    #Import judge_client to MySQL database
    service mysql start
    echo "insert into judger_info (judger_name, password) values (\"main_judger\", \"$_main_judger_password_\")" | mysql app_uoj233 -u root --password=$_database_password_
    #Using cli upgrade to latest
    php /var/www/qoj/app/cli.php upgrade:latest
    #Start services
    service ntp restart
    service mysql restart
    service apache2 restart
    su local_main_judger -c '/home/local_main_judger/judge_client/judge_client start'
    #Touch SetupDone flag file
    touch /var/uoj_data/.UOJSetupDone
    rm -rf /opt/git.sh /opt/libv8-7.5 switch
    printf "\n\n***Installation complete. Enjoy!***\n"
}

prepProgress(){
    getAptPackage;setLAMPConf;setWebConf;setJudgeConf
}

prepProgress;initProgress