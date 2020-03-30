# 自定义比较器

您可以使用自定义答案比较器 (SPJ) 来评判选手输出，并给出结果

自定义答案比较器应用 `C++` 编写，保存为 `chk.cpp` 放置在测试数据中上传

在使用自定义比较器时，应去除 `problem.conf` 中 `use_builtin_checker` 一行

关于 `testlib` 的说明，请查看 [testlib](https://codeforces.com/testlib)

### 示例
```cpp
#include "testlib.h"

int main(int argc, char* argv[])
{
    registerTestlibCmd(argc, argv);

    int pans,jans;
    pans=ouf.readInt();      // 读取选手输出
    jans=ans.readInt();      // 读取答案

    if (pans == jans)
        quitf(_ok, "Correct.");
    else
        quitf(_wa, "WA! expect=%d recieve=%d", jans, pans);
}
```

您可以从三个流中读取数据

- `inf` : 输入数据
- `ouf` : 选手输出数据
- `ans` : 答案数据

对于特殊分数的返回，您应该使用

```cpp
quitp(ceil(100.0 * p / a) / 100, "QAQ");
```

注意第一个返回数据必须是浮点数，`QAQ` 是可定义的返回信息，与 `quitf` 类似

### 常用函数
function | 说明
--- | ---
`int InStream::readInt(int minv, int maxv)` | 读取一个 `[minv, maxv]` 范围内的 32 位整数（忽略空白字符）
`long long InStream::readLong(long long minv, long long maxv)` | 读取一个 `[minv, maxv]` 范围内的 64 位整数（忽略空白字符）
`double InStream::readDouble(double minv, double maxv)` | 读取一个 `[minv, maxv]` 范围内的实数（忽略空白字符）
`std::string InStream::readToken()` | 读取一个不包含空白字符的连续字符串（忽略空白字符）
`std::string InStream::readToken(const std::string& ptrn)` | 读取一个匹配给定模式的不包含空白字符的连续字符串（忽略空白字符）
`char InStream::readChar()` | 读取单个字符
`char InStream::readSpace()` | 读取一个空格
`void InStream::readEoln()` | 读取一个换行符
`void InStream::readEof()` | 读取一个文末符