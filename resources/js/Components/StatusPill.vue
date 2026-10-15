<script setup>
defineProps({
    status: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
});

const tones = {
    awaiting_confirmation: 'bg-amber-100 text-amber-900',
    partially_confirmed: 'bg-sky-100 text-sky-900',
    confirmed: 'bg-emerald-100 text-emerald-900',
    completed: 'bg-emerald-100 text-emerald-900',
    plan_ready: 'bg-slate-100 text-slate-700',
    failed: 'bg-red-100 text-red-800',
    draft: 'bg-gray-100 text-gray-700',
    queued: 'bg-gray-100 text-gray-700',
    stub: 'bg-violet-100 text-violet-900',
};
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
        :class="tones[status] || 'bg-gray-100 text-gray-700'"
    >
        {{ label || status }}
    </span>
</template>
