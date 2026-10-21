<?php

return [

    /*
    | WeChat Official Account web admin. Personal unverified accounts cannot
    | use the official draft API; phase 1 only records a browser fill plan.
    */
    'wechat_oa' => [
        'admin_origin' => env('WECHAT_MP_ORIGIN', 'https://mp.weixin.qq.com'),
        'editor_hint' => '内容管理 → 草稿箱 → 写新图文',
    ],

    /*
    | Zhihu column editor. Phase 1 fills a draft in the web admin.
    */
    'zhihu' => [
        'admin_origin' => env('ZHIHU_WRITE_ORIGIN', 'https://zhuanlan.zhihu.com'),
        'editor_hint' => '创作中心 → 写文章',
    ],

    /*
    | X official API stub. Leave the token empty. Phase 1 never sends it.
    */
    'x' => [
        'base_url' => env('X_API_BASE_URL', 'https://api.x.com'),
        'bearer_token' => env('X_API_BEARER_TOKEN'),
        'tweets_path' => '/2/tweets',
    ],

];
