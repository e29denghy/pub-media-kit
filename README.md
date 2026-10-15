# pub-media-kit

自托管的多平台内容发布台。一份草稿，选择平台组合，生成发布任务。阶段一只有两条真正要操作的线路：微信公众号和知乎。两条都是「在你已经登录的网页后台里填写草稿，人确认后才发表」。X 官方 API 只有占位，现在不会发请求。

Self-hosted hub for publishing one draft to more than one platform. Phase 1 operates two lines, WeChat Official Account and Zhihu. Both fill a draft in a browser session you are already logged into, then wait for a person to publish. The X official API is a stub and sends nothing.

## 技术栈 Stack

- Laravel 13、PHP 8.3、SQLite
- Vue 3 + Inertia（Laravel Breeze）
- Tailwind CSS
- 认证用 Breeze 的 session 登录。这是给自托管后台用的，不是多租户 SaaS。

Breeze session auth is included for the admin UI. This is a single-operator app, not a multi-tenant product.

## 三条线路 Three lines

```
草稿 Draft
  └─ 发布任务 Publish job
       ├─ A  微信公众号   网页会话填草稿 → 人确认发表
       ├─ B  知乎         网页会话填草稿 → 人确认发表
       └─ C  X            官方 API 占位（不发 HTTP）
```

| 线路 | 键 | 模式 | 阶段一行为 |
| --- | --- | --- | --- |
| 微信公众号 WeChat Official Account | `wechat_oa` | `browser_session_draft` | 生成填写计划，停在发表之前 |
| 知乎 Zhihu | `zhihu` | `browser_session_draft` | 生成填写计划，停在发布之前 |
| X | `x` | `official_api` | 只记录预定的 `POST /2/tweets` 载荷，不发送 |

适配器注册表在 `App\Publishing\PlatformAdapterRegistry`。每条线路实现 `PlatformAdapter`。这个接口只有 `prepareDraftFill()`：它返回一份 `DraftFillPlan`，不能发表，也不能访问网络。计划里的步骤使用稳定的动作名（`App\Publishing\FillAction`），方便以后接 Playwright 或浏览器扩展：

The registry lives in `App\Publishing\PlatformAdapterRegistry`. Each line implements `PlatformAdapter`. The only operation is `prepareDraftFill()`, which returns a `DraftFillPlan`. It cannot publish and it cannot touch the network. Steps use stable action names in `App\Publishing\FillAction` so a Playwright runner or browser extension can be attached later:

| Action | 含义 |
| --- | --- |
| `ensure_session` | 使用操作者自己已经登录的浏览器会话。不采集密码，不导出 Cookie。 |
| `open_editor` | 打开网页后台编辑器。选择器不写死。 |
| `fill_field` | 填写标题、正文或标签。 |
| `attach_media` | 把本地素材交给以后的 runner。阶段一不上传。 |
| `await_human_publish` | 停住。人在平台后台检查并点击发表。 |
| `build_payload` | X 占位：组装但保存于本系统。 |
| `skip_network` | X 占位：明确不发请求。 |

人在本系统里点击「我已在后台确认发表」时，只更新本地任务状态。没有任何适配器会去点平台上的发表按钮。

Clicking “我已在后台确认发表” only updates the local job. No adapter clicks a platform publish button.

公众号和知乎的共同流程在 `BrowserSessionDraftAdapter`。两个子类只提供入口文案和警告。X 在 `XApiAdapter`，即使设置了 `X_API_BEARER_TOKEN` 也不会发送，token 也不会写进计划。

WeChat and Zhihu share `BrowserSessionDraftAdapter`. The subclasses only supply the editor hint and warnings. `XApiAdapter` does not send a request when `X_API_BEARER_TOKEN` is set, and it does not copy the token into the plan.

## 为什么个人公众号要走网页自动化

未认证的个人订阅号不能使用微信草稿箱 / 发布官方接口。`cgi-bin/draft/add` 这类接口要求已认证、并且开通了对应权限的公众号。个人号通常达不到这个条件。

所以阶段一不把公众号实现成官方 API，也不假装调用成功。它描述的是另一条可操作的路径：你自己在 `mp.weixin.qq.com` 登录，之后的 runner 只在这个会话里把标题和正文填进草稿，然后停下来。发表仍由人完成。

这不是绕过微信的登录，也不是代持别人的账号。密码和 Cookie 不应该进入这个仓库。

### Why a personal WeChat account uses the web admin

An unverified personal subscription account cannot call the WeChat draft/publish API. Endpoints such as `cgi-bin/draft/add` require a verified account with that permission enabled. Personal accounts usually do not have it.

Phase 1 therefore does not implement WeChat as an official API, and it does not pretend a call succeeded. The operable path is the one you already use: sign in yourself at `mp.weixin.qq.com`. A future runner may fill the title and body into that logged-in draft, then stop. A person publishes.

This is not a login bypass and it is not a way to operate someone else's account. Passwords and cookies do not belong in this repository.

知乎普通创作者同样没有一条稳定、可公开依赖的官方发布 API。阶段一用同样的「网页编辑器填草稿 + 人点发布」。标签对应知乎话题。

Ordinary Zhihu creators also do not have a stable public publish API. Phase 1 uses the same pattern: fill the web editor, then a person publishes. Tags map to Zhihu topics.

## 领域模型 Domain model

- `drafts`：标题、Markdown 正文、标签、状态。
- `media_assets`：挂在草稿上的图片。存在 `local` 磁盘，不进 `public`。
- `publish_combinations`：可复用的平台组合，例如「图文双发 = 公众号 + 知乎」。
- `publish_jobs`：一次发布尝试，保存当时勾选的平台快照。
- `publish_targets`：每个平台一条。浏览器线路的状态是 `awaiting_confirmation`，X 占位是 `plan_ready`。

任务状态：有待确认目标时是 `awaiting_confirmation`；确认了一部分是 `partially_confirmed`；浏览器线路都已人工确认、占位线路只是计划就绪时是 `completed`。`completed` 表示本系统没有剩余步骤，不表示 X 已经发出。

`completed` means this app has nothing left to do. It does not mean an X post was sent.

## 本地运行 Run locally

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run dev
```

另开一个终端：

```bash
php artisan serve
```

打开 <http://localhost:8000>。

种子数据只适合本机：

- 邮箱 `admin@example.com`
- 密码 `password`

不要把这个账号用到公网。Breeze 默认还开着注册页。放到别人能访问的机器上时，请关掉 `routes/auth.php` 里的 `register`，或把整个站点放在内网。

The seeder is local-only (`admin@example.com` / `password`). Do not expose it. Breeze registration is enabled; disable the `register` routes or keep the site on a private network before anyone else can reach it.

生产构建用 `npm run build`。测试不依赖前端构建：

```bash
php artisan test
```

`.env.example` 里的平台变量都是空的或公开的站点地址。不要把真实 token 写进仓库。X 的 token 即使填了，阶段一也不会使用。

Platform variables in `.env.example` are empty or public origins. Do not commit real tokens. A filled X token is still unused in phase 1.

## 路线图 Roadmap

1. Playwright 持久化浏览器配置，或浏览器扩展，按 `FillAction` 执行公众号和知乎的填写计划。runner 只复用操作者自己登录好的会话。
2. 已认证公众号如果以后要接官方草稿 API，做成单独的适配器，不替换个人号的网页线路。
3. X 在明确开启之后才实现 `POST /2/tweets`。
4. 小红书、抖音、视频号：优先继续走网页会话填草稿，不假设存在个人开发者可用的官方发布 API。
5. Markdown 转各平台编辑器格式、封面图实际上传。

Later: a Playwright persistent profile or a browser extension that executes `FillAction` steps for WeChat and Zhihu; a separate official-API adapter only for verified WeChat accounts; a real X client only when explicitly enabled; Xiaohongshu, Douyin, and WeChat Channels as further browser-session lines; Markdown conversion and real cover uploads.

## License

MIT. See `LICENSE`.
