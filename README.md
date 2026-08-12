# External Link — WordPress 外链跳转插件

<p align="center">
  <img src="assets/img/external-link-default.png" alt="External Link Preview" width="640" />
</p>

<p align="center">
  <a href="https://github.com/Jacky088/External-Link/stargazers"><img src="https://img.shields.io/github/stars/Jacky088/External-Link?style=flat-square" alt="Stars"></a>
  <a href="https://github.com/Jacky088/External-Link/network/members"><img src="https://img.shields.io/github/forks/Jacky088/External-Link?style=flat-square" alt="Forks"></a>
  <a href="https://github.com/Jacky088/External-Link/releases"><img src="https://img.shields.io/github/v/release/Jacky088/External-Link?style=flat-square" alt="Release"></a>
  <a href="https://github.com/Jacky088/External-Link/issues"><img src="https://img.shields.io/github/issues/Jacky088/External-Link?style=flat-square" alt="Issues"></a>
  <a href="LICENSE"><img src="https://img.shields.io/github/license/Jacky088/External-Link?style=flat-square" alt="License"></a>
</p>

<p align="center">
  一款功能完善、可视化定制的 <strong>WordPress 外链安全跳转插件</strong>。<br>
  自动拦截文章/评论/圈子中的外链，强制经过可控的提示页跳转；<br>
  内置 <strong>8 套精美提示页样式</strong>、<strong>两种加密验证机制</strong>、<strong>子比 / 7b2 主题深度适配</strong>、<strong>GitHub Releases 一键更新</strong>。
</p>

---

## 目录

- [预览](#预览)
- [核心特性](#核心特性)
- [环境要求](#环境要求)
- [安装](#安装)
- [使用方法](#使用方法)
- [后台设置一览](#后台设置一览)
- [提示页样式](#提示页样式)
- [安全机制](#安全机制)
- [主题适配](#主题适配)
- [GitHub Releases 自动更新](#github-releases-自动更新)
- [目录结构](#目录结构)
- [常见问题](#常见问题)
- [贡献](#贡献)
- [作者与许可](#作者与许可)

---

## 预览

插件后台可视化选择提示页样式：

<p align="center">
  <img src="assets/img/external-link-default.png" width="200" alt="默认样式-茉莉小栈" />
  <img src="assets/img/external-link-bilibili.png" width="200" alt="哔哩哔哩" />
  <img src="assets/img/external-link-csdn.png" width="200" alt="CSDN" />
  <img src="assets/img/external-link-tiktok.png" width="200" alt="TikTok" />
  <img src="assets/img/external-link-moxing.jpg" width="200" alt="墨星博客" />
</p>

---

## 核心特性

- **自动拦截外链**：`the_content`、`get_comment_author_link`、`comment_text` 过滤器全方位接管，文章/评论外链统一改写为跳转 URL。
- **白名单系统**：支持域名后缀、完整 URL 两种形式，每行一个，无需带协议头。
- **8 套精美提示页样式**：默认(茉莉小栈)毛玻璃 / 哔哩哔哩 / 腾讯云社区 / CSDN / 知乎 / 通用跳转(紫蓝渐变) / 墨星博客 / TikTok 海外版。后台可视化预览，一键切换。
- **两种加密验证机制**：
  - 随机字符串 Token + 过期机制（默认 5 分钟，可自定义 1–1440 分钟）
  - AES-256-CBC 自包含加密 URL（永久有效、无需数据库）
- **Referer 防护**：开启后禁止站外直接访问跳转页（403），支持空 Referer 放行与白名单。
- **可自定义跳转页 Slug**：默认 `dinterception`，后台修改即自动刷新 Rewrite。
- **Logo 自定义**：支持上传自定义 Logo，未设置时优雅回退。
- **主题深度适配**：子比(zibll)主题自动关闭其原生外链重定向并接管相关过滤器；7b2 主题圈子支持自定义 CSS 选择器。
- **AJAX 动态转链**：基于 `MutationObserver` + `requestIdleCallback` 扫描选择器内新增的链接，配合公开 AJAX 接口(Nginx 缓存友好)完成转换。
- **GitHub Releases 一键更新**：通过 WordPress 原生 update 机制，30 分钟缓存 + 限流降级。
- **优雅卸载**：自动清理 options、transient、Rewrite、GitHub 缓存，无残留。

---

## 环境要求

| 项 | 要求 |
| --- | --- |
| WordPress | 6.4 及以上（测试至 7.0） |
| PHP | 7.4 及以上 |
| 固定链接 | 需启用（默认 `/%postname%/` 即可） |
| 主题 | 任意，子比(zibll) / 7b2 主题有额外适配 |
| 浏览器 | 支持 ES6 / `requestIdleCallback` 的现代浏览器 |

---

## 安装

### 方式一：WordPress 后台上传

1. 进入 **插件 → 安装插件 → 上传插件**
2. 选择 `external-link.zip`（或从 [Releases](https://github.com/Jacky088/External-Link/releases) 下载）
3. 安装并启用插件
4. 进入 **设置 → 固定链接**，点击一次「保存更改」以刷新 Rewrite 规则

### 方式二：手动上传

1. 将整个 `external-link` 目录上传至 `wp-content/plugins/`
2. 在「插件」列表启用 **外链跳转插件**
3. 同样建议刷新一次固定链接

### 方式三：从 GitHub 拉取开发版

```bash
cd wp-content/plugins/
git clone https://github.com/Jacky088/External-Link.git external-link
```

---

## 使用方法

1. 后台左侧菜单点击 **「外链跳转插件」** 进入设置面板。
2. 在 **基本设置** 中确认总开关已启用，按需修改跳转页路径(Slug)。
3. **白名单设置** 中按行填入信任的链接或域名。
4. **样式设置** 中选择喜欢的提示页样式（下方有预览）。
5. **安全设置** 中选择验证方式：随机 Token（推荐）或 AES 加密。
6. 保存设置后立即生效，无需手动刷新。

---

## 后台设置一览

| 分区 | 设置项 | 类型 | 默认值 | 说明 |
| --- | --- | --- | --- | --- |
| **基本设置** | 启用插件功能 | switcher | ✅ | 关闭后所有拦截与样式停用 |
| | 跳转页路径(Slug) | text | `dinterception` | 仅允许小写字母/数字/短横线，保存即自动 flush rewrite |
| **白名单设置** | 白名单链接 | textarea | — | 每行一个，支持域名后缀与完整 URL |
| **样式设置** | 提示页面样式 | image_select | `external-link-default` | 8 套可视化选择 |
| **主题社区功能** | 选择社区功能类型 | radio | `none` | `none` / `circle`(7b2) / `forums`(子比) |
| | 圈子内容选择器 | text | `.topic-content` | 仅 circle 类型显示 |
| | 社区帖子选择器 | text | `.forum-article` | 仅 forums 类型显示 |
| **Logo 设置** | Logo 图片 | upload | — | 上传自定义 Logo |
| **安全设置** | 链接验证方式 | radio | `random_string` | `random_string` / `aes_encryption` |
| | 过期时间(分钟) | number | `5` | 1–1440，仅 random_string 类型显示 |
| | AES 加密密钥 | text | 自动随机 32 位 hex | 仅 aes_encryption 类型显示 |
| | 启用 Referer 防护 | switcher | ❌ | 开启后禁止站外直接访问跳转页 |
| | 允许空 Referer | switcher | ✅ | 仅 Referer 防护开启时显示 |
| | Referer 白名单 | textarea | — | 每行一个域名/URL，仅 Referer 防护开启时显示 |

---

## 提示页样式

| 标识 | 中文名 | 风格 |
| --- | --- | --- |
| `external-link-default` | 默认样式（茉莉小栈） | 粉胶囊按钮 + 紫粉渐变背景 + 毛玻璃卡片 |
| `external-link-bilibili` | 哔哩哔哩 | 浅灰底 + 白卡 + 角落装饰 |
| `external-link-tencent` | 腾讯云社区 | 极简白卡 + 蓝色「继续访问」按钮 |
| `external-link-csdn` | CSDN | 浅灰底 + 米黄图标 + 橙色描边按钮 |
| `external-link-zhihu` | 知乎 | 灰白底 + 蓝色实心按钮 + 长 URL 区域 |
| `external-link-jump` | 通用跳转 | 紫蓝渐变 + 圆形警示图标 + slideIn 动画 |
| `external-link-moxing` | 墨星博客 | 整张背景图 + 双色渐变胶囊按钮 |
| `external-link-tiktok` | TikTok | 海外版 + 387×48 描边按钮 + mobile safe-area |

后台切换后立即生效，所选样式通过 `dmy_link_style` 选项持久化。

---

## 安全机制

### 随机字符串 Token（默认）

- 形态：`<token>` 由 `[A-Za-z0-9]{16}_<timestamp>` 组成
- 存储：通过 `set_transient('dmy_link_<key>', $url, $expiration*60)` 写入 options
- 有效期：默认 **5 分钟**，可后台自定义 1–1440 分钟
- 过期后访问触发 404 提示页 + 返回首页按钮

### AES-256-CBC 自包含加密

- 形态：URL 自带 `IV(16B) + 密文`，整体 base64
- 存储：理论上**无需**数据库；为防 options 表膨胀仍写入 30 天 transient 兜底
- 有效期：永久有效（依赖密钥不变）
- 兜底机制：AES 失效/缺密钥时自动回退到随机字符串方式

### Referer 防护

- 默认关闭，开启后访问跳转页若 Referer 非本站且不在白名单，直接 403
- 支持「允许空 Referer」与自定义白名单，兼顾 SEO 与安全

---

## 主题适配

### 子比(zibll) / 子比比子主题

- ✅ 自动 `_spz('go_link_s', false)` / `_spz('go_link_nonce_s', false)` 强制关闭主题原生外链跳转
- ✅ `remove_filter` 接管 `the_content`、`comment_text`、`get_comment_author_link`
- ✅ `remove_action` 接管用户详情模态框 AJAX(个人网站字段走插件转链)
- ✅ 后台面板在子比环境下使用 `light` 主题色，更协调

### 7b2 主题

- ✅ **圈子功能(circle)**：通过 CSS 选择器(默认 `.topic-content`，可后台自定义)扫描动态加载的外链
- ✅ 基于 `MutationObserver` + `requestIdleCallback` 异步处理，不阻塞首屏渲染
- ✅ 防重复处理：已处理链接标记 `dataset.externalLinkDone='1'`

### 其他主题

任意主题下都可使用基础外链拦截与跳转功能。CSF 加载失败时会显示备用菜单与错误提示。

---

## GitHub Releases 自动更新

- 仓库地址：`https://github.com/Jacky088/External-Link`
- 缓存策略：`site_transient('external_link_latest_release')`，**30 分钟**有效期
- 限流处理：遇 GitHub API 429/403 自动降级为 15 分钟短缓存
- 跳过规则：`draft` / `prerelease` 不推送
- 版本解析：自动去掉 tag 前的 `v` 前缀，校验 semver
- 包下载：优先匹配 Release 中的 `.zip` 附件，回退 `zipball_url`
- 详情弹窗：`plugins_api` 注入完整 changelog(Release Body 经 `nl2br(esc_html())`)

发布新版本只需：

```bash
git tag v1.1.0
git push origin v1.1.0
# 在 GitHub Releases 中创建 Release，上传 .zip 附件
```

---

## 目录结构

```
external-link/
├── external-link.php          # 插件主入口
├── external-link-template.php # 跳转页模板调度器
├── README.md
├── .gitignore
├── assets/
│   └── img/                   # 样式预览与提示页素材
├── js/
│   └── external-link-circle.js
├── css/
│   ├── external-link.css               # 基础样式
│   ├── external-link-default.css
│   ├── external-link-bilibili.css
│   ├── external-link-tencent.css
│   ├── external-link-csdn.css
│   ├── external-link-zhihu.css
│   ├── external-link-jump.css
│   ├── external-link-moxing.css
│   └── external-link-tiktok.css
├── templates/
│   ├── header.php
│   ├── default-style.php
│   ├── bilibili-style.php
│   ├── tencent-style.php
│   ├── csdn-style.php
│   ├── zhihu-style.php
│   ├── jump-style.php
│   ├── moxing-style.php
│   └── tiktok-style.php
├── src/
│   └── Update/
│       └── GitHubReleaseUpdater.php
└── codestar-framework/        # 第三方配置面板框架(vendor)
    └── admin-settings/
        └── external-link-settings.php
```

---

## 常见问题

**Q: 启用插件后文章外链没变？**
A: 请进入 `设置 → 固定链接`，点击「保存更改」刷新 Rewrite；并确认总开关已启用。

**Q: 提示页样式切换后没变化？**
A: 浏览器可能有缓存，硬刷新（Ctrl+Shift+R）即可。检查 `dmy_link_style` 是否已保存。

**Q: 子比主题下与主题原生外链冲突？**
A: 插件会自动 `_spz('go_link_s', false)` 关闭主题外链重定向；后台会在「基本设置」顶部显示当前状态。

**Q: 跳转链接过期怎么办？**
A: 重新生成即可，过期仅意味原始文章页面打开跳转页时会失效，文章外链本身不受影响。

**Q: AES 密钥丢失怎么办？**
A: 加密过的 URL 将无法还原。插件会在 AES 失效时自动回退到随机字符串机制，保证插件可用。

---

## 贡献

欢迎通过 Issue 反馈问题、Pull Request 提交改进：

1. Fork 本仓库
3. 从 `master` 创建特性分支 (`git checkout -b feature/amazing`)
4. 提交改动 (`git commit -m 'feat: add amazing feature'`)
5. 推送分支 (`git push origin feature/amazing`)
6. 提交 Pull Request

---

## 作者与许可

- **作者**：木木
- **主页**：<https://github.com/Jacky088/External-Link>
- **许可协议**：基于 **GPLv3** 开源，并采用 **CC BY-NC-SA 4.0** 协议发布——**免费使用，禁止商业用途，须保留原作者信息**。

> 如果这个项目对你有帮助，欢迎 ⭐ Star 与 Fork，你的支持是持续维护的动力。