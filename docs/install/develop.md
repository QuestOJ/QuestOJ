# 1.5 二次开发

### 目录介绍

`/opt/qoj` 目录是程序的主目录

`/opt/qoj/web/qoj` 是 OJ 的网页部分，用 `PHP` 进行编写

`/opt/qoj/web/manage` 是 OJ 的管理平台，用 `PHP` 进行编写`

这两个文件夹均被链接至 `/var/www`

`/opt/qoj/judger` 是评测机， 用 `C` 进行编写，链接至 `/home/local_main_judger`

`/var/uoj_data` 储存了试题数据

### 使用 Git 开发

`QOJ` 使用了 `Git` 作为版本管理工具

`/web/qoj` 和　`/web/manage` 被设置为了 `submodule`

您可以更改 `.gitsubmodules` 将他们修改至您的 `git` 仓库，通过

```bash
git submodule init
git submodule update
```

进行初始化和更新