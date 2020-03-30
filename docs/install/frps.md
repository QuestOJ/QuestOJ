# 1.4 内网穿透

推荐使用 [frp](https://github.com/fatedier/frp) 进行内网穿透

### 客户端设置

```javascript
[common]
server_addr = 103.99.178.***
server_port = 7000
tcp_mux = true

[web_proxy]
type = tcp
local_ip = 127.0.0.1
local_port = 8079
remote_port = 8079
use_compression = true
proxy_protocol_version = v2

[ssh]
type = tcp
local_ip = 127.0.0.1
local_port = 22
remote_port = 8081
use_compression = true
```

### 服务器端设置
```javascript
[common]
bind_port = 7000
tcp_mux = true
```

那么就完成了内网穿透外网服务器相关端口

访问外网服务器 8081 端口即相当于访问内部服务器 22 端口

其中 `proxy_protocol_version = v2` 表示使用 `Proxy Protocol` 传递真实 IP，请不要在其他端口上开启，因为其会修改 TCP 数据包内容

需要在内部服务器 Nginx 上新增一个处理 `Proxy Protocol` 反向代理

```javascript
server {
	listen 8079 proxy_protocol;

    client_max_body_size 512M;
    add_header Access-Control-Allow-Origin *;

    real_ip_header  proxy_protocol;	
    location / {
        root /www/wwwroot/questoj;
        try_files $uri @questoj_proxy;
    }

	location @questoj_proxy {
		proxy_pass http://127.0.0.1:8080;
		proxy_redirect off;

        proxy_read_timeout 610s;
        proxy_send_timeout 610s;

		proxy_set_header Host $host;
        proxy_set_header X-Real-IP       $proxy_protocol_addr;
        proxy_set_header X-Forwarded-For $proxy_protocol_addr;
		index index.htm index.html index.php;
	}

	error_page 502 /502.html;
	error_page 504 /504.html;

	access_log /www/wwwlogs/questoj_proxy.log;
	error_log  /www/wwwlogs/questoj_proxy.error.log;
}
```

那么请求就会从 `8079` 端口进入服务器，经过 `Nginx` 处理 `proxy_protocol` 获得真实 IP 后，转到 `8080` 端口再次由 `Nginx` 处理

