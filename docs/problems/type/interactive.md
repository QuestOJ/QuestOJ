# 交互题

您需要为三种语言编写 `implementer.cpp`、`implementer.c` 和 `implementer.pas`，并将它们放在 `requre` 文件夹下

相关的 `grader.h` 文件也需要放在 `require` 文件夹下

为了防止选手通过伪造交互库输出方式骗取得分，您还需要设置 `token`，交互库应当在输出第一行结果前输出这个 `token`，在答案比较器比较前，评测机会先检查 `token` 

### problem.conf

您可以通过编写 `problem.conf` 配置评测数据

```text
n_tests 10
n_ex_tests 1
n_sample_tests 1
input_pre www
input_suf in
output_pre www
output_suf out
time_limit 1
memory_limit 512
output_limit 64
use_builtin_judger on
with_implementer on
token qaqqaqqaqqaq
```

### 解析
conf | 解析
--- | ---
`n_tests` | 标准测试点个数
`n_ex_tests` | 额外测试点个数
`n_sample_tests` | 样例测试点个数
`input_pre` | 输入数据前缀
`input_suf` | 输入数据扩展名
`output_pre` | 输出数据前缀
`output_suf` | 输出数据扩展名 
`time_limit` | 时间限制
`memory_limit` | 空间限制
`output_limit` | 输出限制
`use_builtin_judger` | 使用内部评测机
`with_implementer` | 交互题
`token` | token

其中 `extra_test` 是 `QOJ` 额外测试机制，若在测试点通过情况下，未通过额外测试会被扣除 3 分

样例数据必须是 `extra_test`

标准测试点的文件名应当形如：`www1.in`、`www1.out`、`www2.in`、`www2.out`……

额外测试点的文件名应当形如：`ex_www1.in`、`ex_www1.out`、`ex_www2.in`、`ex_www2.out`……