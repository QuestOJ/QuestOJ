# 传统比较器

`problem.conf` 中 `use_builtin_checker` 有以下几种选择

name | 说明
--- | ---
`ncmp` | （单行整数序列）比较有序64位整数序列
`wcmp` | （单行字符串序列）比较字符串序列
`fcmp` | （多行数据）逐行进行全文比较，不忽略行末空格，忽略文末回车。
`icmp` | 比较单个整数
`ncmp` | （单行整数序列）比较有序64位整数序列
`uncmp` | （单行整数序列）比较无序64位整数序列，即排序后比较
`acmp`或`rcmp` | 比较单个双精度浮点数，最大绝对误差为 1.5e-6
`dcmp` | 比较单个双精度浮点数，最大绝对或相对误差为 1.0e-6
`rcmp4` | 比较双精度浮点数序列，最大绝对或相对误差为 1.0e-4
`rcmp6` | 比较双精度浮点数序列，最大绝对或相对误差为 1.0e-6
`rcmp9` | 比较双精度浮点数序列，最大绝对或相对误差为 1.0e-9
`rncmp` | 比较双精度浮点数序列，最大绝对误差为 1.5e-5
`hcmp` | 比较单个有符号大整数
`lcmp` | 逐行逐字符串进行全文比较，多个空白字符视为一个
`caseicmp` | 多组数据，比较单个整数，输出形如：`Case <caseNumber>: <number>`
`casencmp` | 多组数据，比较整数序列，输出形如：`Case <caseNumber>: <number> <number> ... <number>`
`casewcmp` | 多组数据，比较字符串序列，输出形如：`Case <caseNumber>: <token> <token> ... <token>`
`yesno` | 比较单个YES和NO