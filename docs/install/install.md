# 1.1 安装
### 安装 docker
在多数 `Linux` 发行版中，您都可以在官方源中便捷的安装 `docker`

如有问题，请参阅 [docker](https://www.docker.com/)

请不要忘记将 `docker` 服务添加到开机自动启动
　 
### 从 Github 上下载源码

```bash
git clone https://github.com/QuestOJ/QOJ.git
```

中国大陆地区可以访问 Gitee

```bash
git clone https://gitee.com/QuestOJ/QOJ.git
```

### 构建 docker 镜像

```bash
cd QOJ/install/bundle
sudo docker build -t qoj:latest .
```

您可以使用以下命令查看构建完成的镜像

```bash
sudo docker images
```

### 运行容器

```bash
sudo docker run --name qoj -dit -p 8080:80 --cap-add SYS_PTRACE --restart=always qoj:latest
```

上述命令新建了一个 `qoj` 容器，监了 8081 端口，并设置了自动启动

`-dit`: 非常重要，执行完启动 `COMMAND` 后容器不退出

`-p 8080:80`: 转发 8080 端口至容器内 80 端口，您可以对外部端口号进行修改 `-p port:80`

`--restart-always`: 自动启动、退出自动重启

您可以使用以下命令查看所有容器信息

```bash
sudo docker ps -a
```


### docker 相关操作
#### 启动与暂停容器

```bash
sudo docker start qoj
sudo docker stop qoj
sudo docker restart qoj
```

#### 删除容器
```bash
sudo docker rm qoj
```

#### 删除容器镜像
```bash
sudo docker rmi qoj:latest
```

#### 进入容器
```bash
sudo docker exec -it qoj /bin/bash
```
