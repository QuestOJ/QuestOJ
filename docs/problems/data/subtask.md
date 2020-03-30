# 捆绑测试

### problem.conf

您可以通过编写 `problem.conf` 配置评测数据

```text
n_tests 40
n_ex_tests 1
n_sample_tests 1
n_subtasks 6
subtask_end_1 5
subtask_score_1 10
subtask_end_2 10
subtask_score_2 10
subtask_end_3 15
subtask_score_3 10
subtask_end_4 20
subtask_score_4 20
subtask_end_5 25
subtask_score_5 20
subtask_end_6 40
subtask_score_6 30
```

### 解析
conf | 解析
--- | ---
`n_tests` | 标准测试点个数
`n_ex_tests` | 额外测试点个数
`n_sample_tests` | 样例测试点个数
`subtask_end_n` | 第 n 个 `subtask` 结束测试点编号
`subtask_score_n` | 第 n 个 `subtask` 结束测试点分值

`QOJ` 默认在第一个非 AC 的评测点停止该 `Subtask` 的评测，如果您需要对测试点所有得分取最小值，您可以加入

conf | 解析
--- | ---
`subtask_type_n min` | 第 n 个 `subtask` 得分取最小值