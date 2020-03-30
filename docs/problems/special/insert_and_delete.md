# 增删试题

您可以使用 `QOJ Command Line Interface` 增删试题

**注意** 执行此操作前建议备份容器，以免造成不必要的损失

```bash
cd /var/www/qoj/app
php cli.php tools:insert 5 #新增 id = 5 的试题
php cli.php tools:delete 5 #删除 id = 5 的试题
```

在执行完成前切勿关闭程序