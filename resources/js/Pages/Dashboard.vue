<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    adapters: Array,
    drafts: Array,
    jobs: Array,
});
</script>

<template>
    <Head title="概览" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">概览</h2>
                <Link :href="route('drafts.create')" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                    新建草稿
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <section class="grid gap-4 md:grid-cols-3">
                    <article v-for="adapter in adapters" :key="adapter.key" class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ adapter.label_zh }}</h3>
                                <p class="text-xs text-gray-500">{{ adapter.label }}</p>
                            </div>
                            <StatusPill :status="adapter.phase === 1 ? 'confirmed' : 'stub'" :label="adapter.phase === 1 ? '阶段一' : '占位'" />
                        </div>
                        <p class="mt-3 text-sm text-gray-600">{{ adapter.description }}</p>
                        <p class="mt-3 text-xs text-gray-500">{{ adapter.mode_label_zh }} · {{ adapter.mode_label }}</p>
                    </article>
                </section>

                <section class="grid gap-6 lg:grid-cols-2">
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">最近草稿</h3>
                            <Link :href="route('drafts.index')" class="text-sm text-indigo-600">全部</Link>
                        </div>
                        <p v-if="drafts.length === 0" class="mt-4 text-sm text-gray-500">还没有草稿。</p>
                        <ul v-else class="mt-4 divide-y">
                            <li v-for="draft in drafts" :key="draft.id" class="flex items-center justify-between py-3">
                                <Link :href="route('drafts.show', draft.id)" class="font-medium text-gray-800 hover:text-indigo-700">
                                    {{ draft.title }}
                                </Link>
                                <StatusPill :status="draft.status" :label="draft.status_label" />
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">最近发布任务</h3>
                            <Link :href="route('jobs.index')" class="text-sm text-indigo-600">全部</Link>
                        </div>
                        <p v-if="jobs.length === 0" class="mt-4 text-sm text-gray-500">还没有发布任务。</p>
                        <ul v-else class="mt-4 divide-y">
                            <li v-for="job in jobs" :key="job.id" class="flex items-center justify-between gap-3 py-3">
                                <Link :href="route('jobs.show', job.id)" class="font-medium text-gray-800 hover:text-indigo-700">
                                    {{ job.draft?.title || '草稿 #' + job.draft_id }}
                                </Link>
                                <StatusPill :status="job.status" :label="job.status_label" />
                            </li>
                        </ul>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
