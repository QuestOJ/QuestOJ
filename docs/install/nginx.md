# 1.3 Nginx 与 HTTPS
### Nginx 下载

建议通过编译安装的方式安装最新版 Nginx

您可以点击此处下载 [Nginx](https://nginx.org/en/download.html) 

### 安装依赖

您需要安装 C++ 所需相关依赖

```bash
sudo apt-get install build-essential libtool
```

您需要安装 PCRE 

```bash
sudo apt-get install libpcre3 libpcre3-dev
```

您需要安装 OpenSSL

```bash
sudo apt-get install openssl
```

您需要安装 zlib

```bash
sudo apt-get install zlib1g-dev
```

### 编译安装

将下载后的 Nginx 解压

```bash
tar -cvzf nginx-1.17.9.tar.gz
```

### 下载模块

```bash
cd nginx-1.17.9
git clone https://github.com/yaoweibin/ngx_http_substitutions_filter_module.git
```

### 设置编译选项

```bash
./configure --prefix=/etc/nginx --sbin-path=/usr/sbin/nginx --modules-path=/usr/lib64/nginx/modules --conf-path=/etc/nginx/nginx.conf --error-log-path=/var/log/nginx/error.log --http-log-path=/var/log/nginx/access.log --pid-path=/var/run/nginx.pid --lock-path=/var/run/nginx.lock --http-client-body-temp-path=/var/cache/nginx/client_temp --http-proxy-temp-path=/var/cache/nginx/proxy_temp --http-fastcgi-temp-path=/var/cache/nginx/fastcgi_temp --http-uwsgi-temp-path=/var/cache/nginx/uwsgi_temp --http-scgi-temp-path=/var/cache/nginx/scgi_temp --user=nginx --group=nginx --with-compat --with-file-aio --with-threads --with-http_addition_module --with-http_auth_request_module --with-http_dav_module --with-http_flv_module --with-http_gunzip_module --with-http_gzip_static_module --with-http_mp4_module --with-http_random_index_module --with-http_realip_module --with-http_secure_link_module --with-http_ssl_module --with-http_slice_module --with-http_ssl_module --with-http_stub_status_module --with-http_sub_module --with-http_v2_module --with-mail --with-mail_ssl_module --with-stream --with-stream_realip_module --with-stream_ssl_module --with-stream_ssl_preread_module --with-cc-opt='-O2 -g -pipe -Wall -Wp,-D_FORTIFY_SOURCE=2 -fexceptions -fstack-protector-strong --param=ssp-buffer-size=4 -grecord-gcc-switches -m64 -mtune=generic -fPIC' --with-ld-opt='-Wl,-z,relro -Wl,-z,now -pie' --add-module=./ngx_http_substitutions_filter_module
```

如果遇到相关依赖缺失，您可以 Google/Bing 解决

### 编译与安装

```bash
make -j4
make install
```

使用 4 线程编译（可按需修改），并安装

### 新建 www 用户组

```bash
useradd www
```

### 站点配置 

`nginx` 配置文件目录在 `/etc/nginx`，修改 `nginx.conf`

```javascript
user  www;
worker_processes  1;

events {
    worker_connections  1024;
}

http {
    include       mime.types;
    default_type  application/octet-stream;

    sendfile        on;
    keepalive_timeout  65;

    server_tokens off;

    include /etc/nginx/conf.d/*.conf;
}
```

新建 conf.d 和 ssl 文件夹

```bash
sudo mkdir -p /etc/nginx/conf.d
sudo mkdir -p /etc/nginx/ssl
```

以下是配置示例

#### questoj.conf
```javascript
server {
	listen 80;
	listen 443 ssl http2;

	server_name local.questoj.cn;

	ssl_certificate      /etc/nginx/ssl/local.questoj.cn/fullchain.cer;
	ssl_certificate_key  /etc/nginx/ssl/local.questoj.cn/local.questoj.cn.key;
    ssl_protocols        TLSv1.1 TLSv1.2;
    ssl_ciphers          ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:AES128+EECDH:AES128+EDH:EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH;
    ssl_prefer_server_ciphers  on;
    ssl_session_cache    shared:SSL:10m;
    ssl_session_timeout  10m;
    error_page 497 https://$host$request_uri;

    if ($server_port !~ 443){
        rewrite ^(/.*)$ https://$host$1 permanent;
    }

    client_max_body_size 512M;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload;";

    add_header Access-Control-Allow-Origin *;

    location / {
        root /www/wwwroot/questoj;
        try_files $uri @questoj;
    }

	location @questoj {
		proxy_pass http://127.0.0.1:8080;
		proxy_redirect off;

        proxy_read_timeout 610s;
        proxy_send_timeout 610s;

		proxy_set_header Host $host;
		proxy_set_header X-Real-IP $remote_addr;
		proxy_set_header X-Forward-For $proxy_add_x_forwarded_for;
	
		index index.htm index.html index.php;
	}

	error_page 502 /502.html;
	error_page 504 /504.html;

	access_log /www/wwwlogs/questoj.log;
	error_log  /www/wwwlogs/questoj.error.log;
}
```

#### questoj_manage.conf

```javascript
server {
	listen 80;
	listen 443 ssl http2;

	server_name manage.local.questoj.cn;

	ssl_certificate      /etc/nginx/ssl/local.questoj.cn/fullchain.cer;
	ssl_certificate_key  /etc/nginx/ssl/local.questoj.cn/local.questoj.cn.key;
    ssl_protocols        TLSv1.1 TLSv1.2;
    ssl_ciphers          ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:AES128+EECDH:AES128+EDH:EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH;
    ssl_prefer_server_ciphers  on;
    ssl_session_cache    shared:SSL:10m;
    ssl_session_timeout  10m;
    error_page 497 https://$host$request_uri;

    if ($server_port !~ 443){
            rewrite ^(/.*)$ https://$host$1 permanent;
    }

    client_max_body_size 512M;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload;";

    add_header Access-Control-Allow-Origin *;

    location / {
        root /www/wwwroot/questoj;
        try_files $uri @questoj;
    }

	location @questoj {
		proxy_pass http://127.0.0.1:8080;
		proxy_redirect off;

		proxy_set_header Host $host;
		proxy_set_header X-Real-IP $remote_addr;
		proxy_set_header X-Forward-For $proxy_add_x_forwarded_for;
	
		index index.htm index.html index.php;
	}

	error_page 502 /502.html;
	error_page 504 /504.html;

	access_log /www/wwwlogs/questoj_manage.log;
	error_log  /www/wwwlogs/questoj_manage.error.log;
}
```

### 开机自启

在 `/usr/lib/systemd/system/` 中新建 `nginx.service`

```javascript
[Unit]
Description=nginx - high performance web server
Documentation=http://nginx.org/en/docs/
After=network-online.target remote-fs.target nss-lookup.target
Wants=network-online.target

[Service]
Type=forking
PIDFile=/var/run/nginx.pid
ExecStart=/usr/sbin/nginx -c /etc/nginx/nginx.conf
ExecReload=/bin/kill -s HUP $MAINPID
ExecStop=/bin/kill -s TERM $MAINPID

[Install]
WantedBy=multi-user.target
```

通过以下命令启动 `nginx` 服务并注册开机启动

```bash
sudo systemctl start nginx
sudo systemctl enable nginx
```

### HTTPS 设置

在上述示例配制中，`nginx` 已经支持了 SSL

您还需要修改 QOJ 的设置 `/var/www/qoj/app/.config.php`

将其中

```php
  'web' => 
  array (
    'domain' => NULL,
    'main' => 
    array (
      'protocol' => 'http',
      'host' => UOJContext::httpHost(),
      'port' => 80,
    ),
    'blog' => 
    array (
      'protocol' => 'http',
      'host' => UOJContext::httpHost(),
      'port' => 80,
    ),
  ),
```

对应的 `protocol` 和 `port` 修改为 `https` 和 `443`