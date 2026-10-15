<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    job: Object,
});

const form = useForm({});

function confirmTarget(target) {
    form.post(route('targets.confirm', target.id));
}
</script>

<template>
    <Head :title="'任务 #' + job.id" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        <Link :href="route('drafts.show', job.draft_id)" class="hover:text-indigo-700">
                            {{ job.draft?.title }}
                        </Link>
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">发布任务 #{{ job.id }}</p>
                </div>
                <StatusPill :status="job.status" :label="job.status_label" />
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl space-y-4 px-4 sm:px-6 lg:px-8">
                <article v-for="target in job.targets" :key="target.id" class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ target.platform_label }}</h3>
                            <p class="text-xs text-gray-500">{{ target.mode_label }} · {{ target.platform }}</p>
                        </div>
                        <StatusPill :status="target.status" :label="target.status_label" />
                    </div>

                    <p class="mt-4 text-sm text-gray-800">{{ target.plan?.summary }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ target.plan?.summary_en }}</p>

                    <ul class="mt-4 space-y-2">
                        <li v-for="warning in target.plan?.warnings || []" :key="warning" class="rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-900">
                            {{ warning }}
                        </li>
                    </ul>

                    <ol class="mt-4 space-y-3">
                        <li v-for="step in target.plan?.steps || []" :key="step.order" class="rounded-md border border-gray-100 px-3 py-3">
                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                <span class="font-mono">{{ step.order }}. {{ step.action }}</span>
                                <span>{{ step.field }}</span>
                            </div>
                            <p v-if="step.value" class="mt-2 whitespace-pre-wrap text-sm text-gray-800">{{ step.value }}</p>
                            <p class="mt-2 text-sm text-gray-700">{{ step.note }}</p>
                            <p class="mt-1 text-xs text-gray-500">{{ step.note_en }}</p>
                        </li>
                    </ol>

                    <div v-if="target.status === 'awaiting_confirmation'" class="mt-5">
                        <button
                            type="button"
                            class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                            :disabled="form.processing"
                            @click="confirmTarget(target)"
                        >
                            我已在后台确认发表
                        </button>
                        <p class="mt-2 text-xs text-gray-500">
                            这只把本系统标成已确认，不会远程点击平台的发表按钮。
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
