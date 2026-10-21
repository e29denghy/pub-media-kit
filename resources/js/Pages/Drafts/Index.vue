<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    drafts: Object,
});
</script>

<template>
    <Head title="草稿" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">草稿 Drafts</h2>
                <Link :href="route('drafts.create')" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                    新建
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <p v-if="drafts.data.length === 0" class="p-6 text-sm text-gray-500">还没有草稿。先写一篇，再选择公众号或知乎。</p>
                    <table v-else class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500">
                            <tr>
                                <th class="px-4 py-3 font-medium">标题</th>
                                <th class="px-4 py-3 font-medium">标签</th>
                                <th class="px-4 py-3 font-medium">状态</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="draft in drafts.data" :key="draft.id">
                                <td class="px-4 py-3">
                                    <Link :href="route('drafts.show', draft.id)" class="font-medium text-gray-900 hover:text-indigo-700">
                                        {{ draft.title }}
                                    </Link>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ (draft.tags || []).join('、') || '—' }}</td>
                                <td class="px-4 py-3">
                                    <StatusPill :status="draft.status" :label="draft.status_label" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :paginator="drafts" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
