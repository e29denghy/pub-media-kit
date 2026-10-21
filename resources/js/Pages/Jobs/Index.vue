<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    jobs: Object,
});
</script>

<template>
    <Head title="发布任务" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">发布任务 Jobs</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <p v-if="jobs.data.length === 0" class="p-6 text-sm text-gray-500">还没有发布任务。</p>
                    <table v-else class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-gray-500">
                            <tr>
                                <th class="px-4 py-3 font-medium">草稿</th>
                                <th class="px-4 py-3 font-medium">平台</th>
                                <th class="px-4 py-3 font-medium">状态</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="job in jobs.data" :key="job.id">
                                <td class="px-4 py-3">
                                    <Link :href="route('jobs.show', job.id)" class="font-medium text-gray-900 hover:text-indigo-700">
                                        {{ job.draft?.title || '草稿 #' + job.draft_id }}
                                    </Link>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ (job.targets || []).map((target) => target.platform_label).join('、') }}
                                </td>
                                <td class="px-4 py-3">
                                    <StatusPill :status="job.status" :label="job.status_label" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :paginator="jobs" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
