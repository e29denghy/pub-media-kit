<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    paginator: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div v-if="paginator.prev_page_url || paginator.next_page_url" class="mt-4 flex gap-4 text-sm">
        <Link v-if="paginator.prev_page_url" :href="paginator.prev_page_url" class="text-indigo-600 hover:text-indigo-800">
            上一页
        </Link>
        <Link v-if="paginator.next_page_url" :href="paginator.next_page_url" class="text-indigo-600 hover:text-indigo-800">
            下一页
        </Link>
    </div>
</template>
