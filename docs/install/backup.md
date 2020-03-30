# 1.2 备份与迁移
### 备份容器

由于使用了 `docker` 技术，服务的备份非常容易进行

在执行重要操作前，请务必备份容器

```bash
sudo docker commit qoj qoj_back:202001
```

其中，`qoj_back` 是镜像名，`202001` 是镜像 `tag`，同一镜像名下 `tag` 不能重复

这条命令会创建一个新的镜像，您同样可以使用 `sudo docker images` 查看所有镜像

### 恢复容器

您需要停止运行原有容器并删除，从镜像中重新创建容器

```bash
sudo docker stop qoj
sudo docker rm qoj
sudo docker run --name qoj -dit -p 8081:80 --cap-add SYS_PTRACE qoj_back:202001 --restart=always
```

### 导出镜像

```bash
docker save -o qoj.tar qoj_back:202001
```

备份的镜像会被导出至 `qoj.tar` 文件

镜像文件通常较大，您可以通过 `gzip` 或 `pigz` (`gzip` 多线程版) 对文件进行压缩后传输

### 导入镜像

```bash
sudo docker load --input qoj.tar
```