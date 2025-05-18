# koishi-plugin-njcard

超级无敌炫酷的 Koishi 插件 —— `koishi-plugin-njcard`！✨

## 简介

`koishi-plugin-njcard` 是为 [Koishi](https://koishi.chat/) 生态精心打造的一款插件，轻松集成各类卡牌、抽卡玩法，助力你的机器人变得更有趣、更智能！本插件功能强大、配置灵活，支持多种后端接口调用方式，满足各种个性化需求。

---

## 功能特色

- 🚀 **一键安装，极致便捷**  
  直接通过 [npm](https://www.npmjs.com/package/koishi-plugin-njcard) 安装，零门槛接入。

- 🃏 **丰富的卡牌玩法**  
  内置多种卡牌功能，支持抽卡、卡包管理等娱乐玩法。

- 🔌 **灵活的后端对接**  
  插件核心功能依赖 `/api` 目录下的 PHP 后端服务，支持多种调用格式（如 RESTful、GET/POST），可根据需求自由扩展或修改。

- 🛠️ **高度可定制化**  
  无论是玩法规则还是接口细节，均可按需自定义，打造专属于你的卡牌系统。

---

## 快速开始

### 1. 插件安装

在你的 Koishi 项目根目录下运行：

```bash
npm install koishi-plugin-njcard
```

### 2. 配置插件

在 `koishi.config.ts` 或 `koishi.yml` 中添加插件：

```yaml
plugins:
  - name: koishi-plugin-njcard
    # 你可以在此处配置插件的参数
```

### 3. 部署 PHP 后端服务

将 `api` 文件夹部署到支持 PHP 的服务器，配置好相关接口即可。  
接口支持多种调用格式，可根据你的需求进行修改或扩展。

---

## 目录结构

```
├── koishi-plugin-njcard/    # 插件主目录，npm 安装即用
├── api/                     # PHP 后端接口服务，支持多种调用方式
```

---

## 进阶玩法

- 支持自定义卡牌内容、概率、活动等
- 可对接数据库，实现卡牌永久化存储
- 方便扩展更多有趣的娱乐功能

---

## 社区与支持

欢迎前往 [Koishi 官方文档](https://koishi.chat/) 探索更多玩法，若有疑问或建议，欢迎在本仓库提 Issue 或 PR，一起让插件变得更强大！

---

## License

MIT

---

> 让你的 Koishi 机器人，酷炫到飞起！  
> —— by [koishi-plugin-njcard](https://www.npmjs.com/package/koishi-plugin-njcard)
