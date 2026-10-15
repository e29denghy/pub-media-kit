<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    draft: Object,
    platforms: Array,
    combinations: Array,
});

const form = useForm({
    platforms: ['wechat_oa', 'zhihu'],
    publish_combination_id: '',
});

function queueJob() {
    form.post(route('drafts.jobs.store', props.draft.id));
}

function destroyDraft() {
    if (confirm('删除这篇草稿？已生成的发布任务会一起删除。')) {
        router.delete(route('drafts.destroy', props.draft.id));
    }
}
</script>

<template>
    <Head :title="draft.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ draft.title }}</h2>
                <div class="flex gap-3 text-sm">
                    <Link :href="route('drafts.edit', draft.id)" class="text-indigo-600">编辑</Link>
                    <button type="button" class="text-red-600" @click="destroyDraft">删除</button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 lg:grid-cols-3 sm:px-6 lg:px-8">
                <section class="space-y-4 lg:col-span-2">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <StatusPill :status="draft.status" :label="draft.status_label" />
                            <span v-for="tag in draft.tags || []" :key="tag" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                                {{ tag }}
                            </span>
                        </div>
                        <pre class="mt-4 whitespace-pre-wrap font-sans text-sm leading-6 text-gray-800">{{ draft.body }}</pre>
                    </div>

                    <div v-if="draft.media_assets?.length" class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="font-semibold text-gray-900">素材</h3>
                        <ul class="mt-3 space-y-1 text-sm text-gray-600">
                            <li v-for="asset in draft.media_assets" :key="asset.id">
                                {{ asset.original_name }} · {{ asset.mime_type }} · {{ asset.size }} bytes
                            </li>
                        </ul>
                    </div>
                </section>

                <aside class="space-y-4">
                    <form @submit.prevent="queueJob" class="rounded-lg bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-gray-900">生成发布任务</h3>
                        <p class="mt-1 text-xs text-gray-500">只生成填写计划。公众号和知乎都要等人在网页后台确认发表。</p>
                        <div class="mt-4 space-y-2">
                            <label v-for="platform in platforms" :key="platform.key" class="flex items-start gap-2 text-sm">
                                <input type="checkbox" :value="platform.key" v-model="form.platforms" class="mt-1 rounded border-gray-300 text-indigo-600" />
                                <span>
                                    {{ platform.label_zh }}
                                    <span class="text-gray-400">{{ platform.phase === 1 ? '' : '（占位）' }}</span>
                                </span>
                            </label>
                        </div>
                        <select v-model="form.publish_combination_id" class="mt-4 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                            <option value="">不使用组合</option>
                            <option v-for="combination in combinations" :key="combination.id" :value="combination.id">
                                {{ combination.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.platforms" />
                        <PrimaryButton class="mt-4" :disabled="form.processing">生成任务</PrimaryButton>
                    </form>

                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-gray-900">已有任务</h3>
                        <p v-if="!draft.publish_jobs?.length" class="mt-3 text-sm text-gray-500">还没有。</p>
                        <ul v-else class="mt-3 space-y-2 text-sm">
                            <li v-for="job in draft.publish_jobs" :key="job.id" class="flex items-center justify-between gap-2">
                                <Link :href="route('jobs.show', job.id)" class="text-indigo-600">任务 #{{ job.id }}</Link>
                                <StatusPill :status="job.status" :label="job.status_label" />
                            </li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
