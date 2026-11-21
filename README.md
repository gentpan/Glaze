# Glaze

Glaze 是一款经典 WordPress 博客主题，支持明暗模式、多套配色、文章目录、图片灯箱、说说和订阅。

[查看在线站点](https://xifeng.net/) · [下载 WordPress 安装包](https://github.com/gentpan/Glaze/releases/latest/download/glaze.zip)

## 安装

1. 从 GitHub Releases 下载 `glaze.zip`。
2. 在 WordPress 后台进入「外观 → 主题 → 安装主题 → 上传主题」，上传 ZIP 并启用。
3. 在「外观 → 菜单」为主导航分配菜单，然后按需调整 Glaze 设置。

登录有发布权限的账号后，可在说说页直接发布、编辑和删除有权限管理的说说。编辑器支持关键词与配图，删除的说说会移入 WordPress 回收站。

## 布局与配色

正文默认使用最大 600px 的单栏，可在 Glaze 设置中调整。主菜单在宽屏上显示为右侧浮动导航，窄屏时显示在底部。

在 WordPress 的「外观 → 自定义 → Glaze 外观」设置默认色板、默认明暗外观和直角、超椭圆或 Apple 风格。访客也可从页脚切换十种强调色，并独立切换浅色或深色外观。选择会保存在当前浏览器。

新增组件可以使用 `.squircle` 和 `--sq-r`。支持 `corner-shape` 时使用原生超椭圆，其他浏览器使用设定半径的 0.54 倍，直角模式的半径为 0。

在「外观 → Glaze 设置 → 首页侧边栏」分别选择左侧和右侧的内容。支持订阅更新、最新评论、最新说说、随机友情链接，也可以隐藏一侧。每侧最多显示五张卡片。友链来自 WordPress 的「链接」，在首页生成时随机抽取。说说沿用站内已发布的记录，已有说说页时，点击卡片会定位到对应条目。

在「外观 → Glaze 设置 → 字体」分别设置全站与文章、页面正文的中英文字体。中文可选思源黑体（Noto Sans SC）或思源宋体（Noto Serif SC），英文可选 Noto Sans 或 Agrave Pro。正文默认跟随全站设置，选用的网络字体由 Google Fonts 提供。

## 主题模板

`home.php` 渲染文章首页，`single.php` 和 `page.php` 渲染文章及页面，`archive.php`、`search.php` 和 `404.php` 对应归档、搜索和找不到页面。共用模板位于 `template-parts/`，评论、右栏及搜索表单各有自己的模板。文章末尾的「Copy link」使用玻璃 Toast 显示结果。

## 本地开发

运行 `npm ci` 安装构建依赖，再用 `npm run dev` 监听源码改动。需要构建一次时运行 `npm run build`。

| 修改的内容 | 编辑位置 | 构建产物 |
| --- | --- | --- |
| Toast 等全站组件 | `src/js/app.js`，组件按需放入 `src/js/components/` | `assets/js/app.min.js` |
| 页面专用交互 | `src/js/main.js`，页面模块按需放入 `src/js/pages/` | `assets/js/main.min.js` |
| CSS | `src/css/`，组件样式按需放入 `src/css/components/`，由前台或后台入口导入 | `assets/css/` |
| 图片 | `assets/images/` | 原文件 |
| 字体 | `assets/fonts/` | 原文件 |
| PHP 模板 | 主模板放在主题根目录，文章内容等分块放在 `template-parts/` | 原文件 |

用 `import` 把 JS 模块接入对应入口。`app.min.js` 在页头加载，依赖页面元素的组件等到 `DOMContentLoaded` 再初始化。CSS 在源码中引用图片和字体时，用相对于成品样式表的路径，例如 `url("../fonts/example.woff2")`。

`assets/css/wordpress/` 保存 WordPress 在当前前台实际生成的全局、Emoji 和图片尺寸规则。只有文件内容与 WordPress 当次生成的 CSS 一致时，主题才会改用外部文件。升级 WordPress 或调整全局样式后，可从前台重新提取并验证这些文件。

编辑中文文案后，使用 `msgfmt -o languages/zh_CN.mo languages/zh_CN.po` 更新翻译文件。

## 构建安装包

运行 `npm ci` 后执行 `npm run package`，生成可上传到 WordPress 的 `dist/glaze.zip`。打包需要 `zip` 命令。资源来源见[第三方声明](THIRD_PARTY_NOTICES.md)。
