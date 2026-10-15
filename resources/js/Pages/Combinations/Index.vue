<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    combinations: Array,
    platforms: Array,
});

const form = useForm({
    name: '',
    notes: '',
    platforms: ['wechat_oa', 'zhihu'],
});

function submit() {
    form.post(route('combinations.store'), {
        onSuccess: () => form.reset('name', 'notes'),
    });
}

const removeForm = useForm({});

function destroyCombination(combination) {
    removeForm.delete(route('combinations.destroy', combination.id));
}

function platformLabel(key) {
    return props.platforms.find((platform) => platform.key === key)?.label_zh || key;
}
</script>

<template>
    <Head title="平台组合" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">平台组合 Sets</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 lg:grid-cols-2 sm:px-6 lg:px-8">
                <section class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-gray-900">已保存</h3>
                    <p v-if="combinations.length === 0" class="mt-4 text-sm text-gray-500">还没有组合。可以先保存「公众号 + 知乎」。</p>
                    <ul v-else class="mt-4 divide-y">
                        <li v-for="combination in combinations" :key="combination.id" class="py-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ combination.name }}</p>
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ (combination.platforms || []).map(platformLabel).join('、') }}
                                    </p>
                                    <p v-if="combination.notes" class="mt-1 text-sm text-gray-500">{{ combination.notes }}</p>
                                </div>
                                <button type="button" class="text-sm text-red-600" @click="destroyCombination(combination)">
                                    删除
                                </button>
                            </div>
                        </li>
                    </ul>
                </section>

                <form @submit.prevent="submit" class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-gray-900">新建组合</h3>
                    <div class="mt-4">
                        <InputLabel for="name" value="名称" />
                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <div class="mt-4">
                        <InputLabel for="notes" value="备注" />
                        <TextInput id="notes" v-model="form.notes" type="text" class="mt-1 block w-full" />
                    </div>
                    <div class="mt-4 space-y-2">
                        <label v-for="platform in platforms" :key="platform.key" class="flex items-center gap-2 text-sm">
                            <input type="checkbox" :value="platform.key" v-model="form.platforms" class="rounded border-gray-300 text-indigo-600" />
                            {{ platform.label_zh }}
                            <span class="text-gray-400">{{ platform.label }}</span>
                        </label>
                        <InputError class="mt-2" :message="form.errors.platforms" />
                    </div>
                    <PrimaryButton class="mt-4" :disabled="form.processing">保存组合</PrimaryButton>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
