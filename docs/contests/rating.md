# 2.2 Rating 系统

### 总结

此 Rating 系统使用类似 [Elo 等级分制度](https://zh.wikipedia.org/wiki/%E7%AD%89%E7%BA%A7%E5%88%86)，在每一场比赛中，您会得到一个表现分 `performance`，这代表了您在这场比赛中的表现。

总的来说，您的 Rating 由表现分 `performance` 加权平均后，减去 $$f(n)$$，其中 $$n$$ 是您参加比赛的次数，$$f(1) = 800$$，并且将随着 $$n$$ 的增大逐渐减小，直至收敛至 $$0$$

这意味着如果您一直获得 $$X$$ 分的表现分，您的 Rating 将从 $$X-1200$$ 逐渐增加至 $$X$$。如果您在第一场比赛中获得很低的 Rating，请不要担心。您的 Rating 会随着您参加比赛次数增多快速增长。通常在 $$10$$ 场比赛后您的 Rating 将和您的真实水平相近。

### Performance 的计算

首先，我们计算每一位参赛者自身的平均表现分 `Average Performance` $$APerf$$

假设 $$Perf_1, Perf_2, \cdots, Perf_n$$ 是一个参赛者历史表现分，其中 $$Perf_1$$ 是最近一场比赛，那么

$$
APerf = \frac{\sum_{i=1}^n Perf_i \times 0.9^i}{\sum_{i=1}^n 0.9^i}
$$

对于首次参加比赛的参赛者，不同比赛有不同初始等级分 $$CENTER$$ 分别为 $$1200, 1000, 800$$

在一场比赛中，第 $$r$$ 名参赛者的等级分 $$X$$ 满足

$$
\sum \frac{1}{1+{6.0}^{(X-APref_i)/400}} = r - 0.5
$$

这个 $$X$$ 可以二分得到

同样，为避免第一次参赛者的表现分方差过小，这些参赛者的表现分将根据以下关系放大

$$
Perf = (Perf - Center) * 1.75 + Center
$$

不同的比赛有不同的表现分上限 $$RATEBOUND$$ 分别为 $$\inf, 2800, 2000$$

最终的表现分 $$RPerf$$ 由以下关系确定

$$
RPerf = \max(Perf, RATEBOUND + 400)
$$

### 计算 Rating

有以下函数

$$
F(n) = \frac{\sqrt{\sum_{i=1}^n 0.81^i}}{\sum_{i=1}^n 0.9^i}
$$

$$
f(n) = \frac{F(n) - F(\infty)}{F(1) - F(\infty)} \times 400
$$

$$
g(x) = 2.0^{\frac{X}{800}}
$$

您的 Rating 将等于

$$
Rating = g^{-1}(\frac{\sum_{i=1}^n g(RPref_i) \times 0.9^i}{\sum_{i=1}^n 0.9^i})
$$

若最终 Rating 低于 $$200$$ 分，则根据下列关系调整

$$
Rating = \frac{1.003^{Rating - 372.71}}{\ln 1.003} + 1
$$


### 用户名颜色

- $$[0, 200)$$ 灰色
- $$[200, 600)$$ 棕色
- $$[600, 1000)$$ 绿色
- $$[1000, 1300)$$ 青色
- $$[1300, 1600)$$ 蓝色
- $$[1600, 1900)$$ 紫色
- $$[1900, 2200)$$ 橙色
- $$[2200, \infty)$$ 红色

### 原文地址
本文由 [AtCoder Rating System](https://www.dropbox.com/sh/zpgcogxmmu84rr8/AADcw6o7M9tJFDgtpqEQQ46Ua?dl=0&preview=rating.pdf) 翻译修改