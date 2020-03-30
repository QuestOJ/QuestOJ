# 3.2 题目类型

`QOJ` 支持 传统题、提交答案题、交互题

如果您的题目类型并非以上三种，您可以自行编写 `judger`

您需要在测试数据包内放置您的 `judger` 代码和 `Makefile`，您也可以直接引用 `judger/include/uoj_judger.h`

```makefile
export INCLUDE_PATH
CXXFLAGS = -I$(INCLUDE_PATH) -O2

all: chk judger

% : %.cpp
    $(CXX) $(CXXFLAGS) $< -o $@
```

当然既然要重新写 `judger` 了为什么不让管理员发布一下更新呢 