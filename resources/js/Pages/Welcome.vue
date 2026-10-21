<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    adapters: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="pub-media-kit" />

    <div class="min-h-screen bg-gray-50 text-gray-900">
        <header class="mx-auto flex max-w-5xl items-center justify-between px-6 py-6">
            <div class="text-sm font-semibold tracking-wide">pub-media-kit</div>
            <nav v-if="canLogin" class="flex items-center gap-4 text-sm">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="rounded-md bg-gray-900 px-4 py-2 text-white"
                >
                    进入后台
                </Link>
                <template v-else>
                    <Link :href="route('login')" class="text-gray-700 hover:text-gray-900">登录</Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-md bg-gray-900 px-4 py-2 text-white"
                    >
                        注册
                    </Link>
                </template>
            </nav>
        </header>

        <main class="mx-auto max-w-5xl px-6 pb-16">
            <p class="text-sm font-medium text-indigo-700">自托管多平台内容发布台</p>
            <h1 class="mt-3 max-w-3xl text-4xl font-semibold tracking-tight">
                一份草稿，三条发布线路。
            </h1>
            <p class="mt-4 max-w-3xl text-lg text-gray-600">
                阶段一只做微信公众号和知乎：在你已经登录的网页后台里填写草稿，由人确认后再发表。
                X 官方 API 只保留接口占位，现在不会发请求。
            </p>
            <p class="mt-2 max-w-3xl text-sm text-gray-500">
                One draft, three lines. Phase 1 fills WeChat and Zhihu web-admin drafts and waits for a person to publish. The X official API is a stub and sends nothing.
            </p>

            <div class="mt-10 grid gap-4 md:grid-cols-3">
                <article v-for="adapter in adapters" :key="adapter.key" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold">{{ adapter.label_zh }}</h2>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                            {{ adapter.phase === 1 ? '阶段一' : '占位' }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs uppercase tracking-wide text-gray-400">{{ adapter.label }}</p>
                    <p class="mt-3 text-sm text-gray-600">{{ adapter.description }}</p>
                    <p class="mt-4 text-xs text-gray-500">{{ adapter.mode_label_zh }}</p>
                </article>
            </div>
        </main>
    </div>
</template>
