# 提交答案题

### problem.conf

您可以通过编写 `problem.conf` 配置评测数据

```text
use_builtin_judger on
submit_answer on
n_tests 10
input_pre www
input_suf in
output_pre www
output_suf out
```

### 解析

conf | 解析
--- | ---
`n_tests` | 标准测试点个数
`input_pre` | 输入数据前缀
`input_suf` | 输入数据扩展名
`output_pre` | 输出数据前缀
`output_suf` | 输出数据扩展名 
`use_builtin_judger` | 使用内部评测机
`use_builtin_checker` | 使用内部答案比较器

标准测试点的文件名应当形如：`www1.in`、`www1.out`、`www2.in`、`www2.out`……