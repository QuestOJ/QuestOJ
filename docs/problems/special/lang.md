# 提交语言限制

在题目数据管理界面，存在一个提交文件配置

```json
[
    {
        "name": "answer",
        "type": "source code",
        "file_name": "answer.code"
    }
]
```

您可以添加 `languages` 字段限制提交语言

```json
[
    {
        "name": "answer",
        "type": "source code",
        "file_name": "answer.code",
        "languages": [
            "C++11",
            "C",
            "Pascal"
        ]
    }
]
```