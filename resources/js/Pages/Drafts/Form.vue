<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    draft: {
        type: Object,
        default: null,
    },
    platforms: Array,
    combinations: Array,
});

const form = useForm({
    title: props.draft?.title ?? '',
    body: props.draft?.body ?? '',
    tags: (props.draft?.tags ?? []).join(', '),
    platforms: props.draft ? [] : ['wechat_oa', 'zhihu'],
    publish_combination_id: '',
    queue: !props.draft,
    media: [],
    remove_media: [],
});

function onMedia(event) {
    form.media = Array.from(event.target.files || []);
}

function submit() {
    const options = { forceFormData: true };

    if (props.draft) {
        form.transform((data) => ({ ...data, _method: 'put' }));
        form.post(route('drafts.update', props.draft.id), options);
        return;
    }

    form.post(route('drafts.store'), options);
}
</script>

<template>
    <Head :title="draft ? '编辑草稿' : '新建草稿'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ draft ? '编辑草稿' : '新建草稿' }}
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6 rounded-lg bg-white p-6 shadow-sm">
                    <div>
                        <InputLabel for="title" value="标题 Title" />
                        <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="body" value="正文 Body（Markdown）" />
                        <textarea
                            id="body"
                            v-model="form.body"
                            rows="12"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.body" />
                    </div>

                    <div>
                        <InputLabel for="tags" value="标签 Tags" />
                        <TextInput id="tags" v-model="form.tags" type="text" class="mt-1 block w-full" placeholder="公众号, 知乎" />
                        <p class="mt-1 text-xs text-gray-500">用逗号分隔。知乎线路会把它们当作话题。</p>
                        <InputError class="mt-2" :message="form.errors.tags" />
                    </div>

                    <div v-if="draft?.media_assets?.length">
                        <InputLabel value="已有素材" />
                        <label v-for="asset in draft.media_assets" :key="asset.id" class="mt-2 flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" :value="asset.id" v-model="form.remove_media" />
                            删除 {{ asset.original_name }}
                        </label>
                    </div>

                    <div>
                        <InputLabel for="media" value="图片素材 Media" />
                        <input id="media" type="file" accept="image/*" multiple class="mt-1 block w-full text-sm" @change="onMedia" />
                        <p class="mt-1 text-xs text-gray-500">最多 8 张，单张 5MB。只存在本机 local 磁盘，阶段一不上传到平台。</p>
                        <InputError class="mt-2" :message="form.errors.media" />
                    </div>

                    <fieldset>
                        <legend class="text-sm font-medium text-gray-700">平台 Platforms</legend>
                        <div class="mt-3 space-y-3">
                            <label v-for="platform in platforms" :key="platform.key" class="flex gap-3 rounded-md border border-gray-200 p-3">
                                <input type="checkbox" :value="platform.key" v-model="form.platforms" class="mt-1 rounded border-gray-300 text-indigo-600" />
                                <span>
                                    <span class="font-medium text-gray-900">{{ platform.label_zh }}</span>
                                    <span class="text-gray-500"> {{ platform.label }}</span>
                                    <span class="ms-2 rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ platform.mode_label_zh }}</span>
                                    <span v-if="platform.phase !== 1" class="ms-2 text-xs text-violet-700">占位</span>
                                    <span class="mt-1 block text-sm text-gray-600">{{ platform.description }}</span>
                                </span>
                            </label>
                        </div>
                        <InputError class="mt-2" :message="form.errors.platforms" />
                    </fieldset>

                    <div>
                        <InputLabel for="combination" value="平台组合（可选）" />
                        <select id="combination" v-model="form.publish_combination_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">不使用组合</option>
                            <option v-for="combination in combinations" :key="combination.id" :value="combination.id">
                                {{ combination.name }}
                            </option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">组合里的平台会和上面勾选的平台合并。</p>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" v-model="form.queue" class="rounded border-gray-300 text-indigo-600" />
                        保存后生成发布任务（不会自动发表）
                    </label>

                    <PrimaryButton :disabled="form.processing">
                        {{ draft ? '保存修改' : '保存草稿' }}
                    </PrimaryButton>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
