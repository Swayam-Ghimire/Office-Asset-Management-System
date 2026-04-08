<script setup>

// asset edit

import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    asset:      { type: Object, required: true },
    categories: { type: Array,  required: true },
});

const form = useForm({
    model_name:    props.asset.model_name,
    category_id:   props.asset.category_id,
    brand:         props.asset.brand ?? '',
    purchase_date: props.asset.purchase_date ?? '',
    condition:     props.asset.condition,
    status:        props.asset.status,
    img_path:      null,
    _method: 'PATCH'
});

function submit() {
    form.post(route('assets.update', props.asset.id), {
        forceFormData: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Edit Asset" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Asset</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ asset.model_name }}</p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('assets.show', asset.id)"
                        class="inline-flex items-center gap-1.5 text-sm text-gray-600 border border-gray-200 px-3 py-2 rounded-lg hover:bg-gray-50">
                        <fa-icon icon="eye" class="w-3.5 h-3.5" /> View
                    </Link>
                    <Link :href="route('home')"
                        class="inline-flex items-center gap-1.5 text-sm text-gray-600 border border-gray-200 px-3 py-2 rounded-lg hover:bg-gray-50">
                        <fa-icon icon="arrow-left" class="w-3.5 h-3.5" /> Back
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div v-if="asset.img_path" class="border-b border-gray-100">
                    <img :src="'/storage/' + asset.img_path" :alt="asset.model_name" class="w-full h-44 object-cover" />
                </div>

                <form @submit.prevent="submit" class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Model Name" />
                            <input v-model="form.model_name" type="text" required
                                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none" />
                            <InputError :message="form.errors.model_name" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Brand" />
                            <input v-model="form.brand" type="text" placeholder="Optional"
                                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none" />
                            <InputError :message="form.errors.brand" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Category" />
                            <select v-model="form.category_id" required
                                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                                <option value="" disabled>Select category</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <InputError :message="form.errors.category_id" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Purchase Date" />
                            <input v-model="form.purchase_date" type="date"
                                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none" />
                            <InputError :message="form.errors.purchase_date" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel value="Condition" />
                            <select v-model="form.condition"
                                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                                <option value="new">New</option>
                                <option value="good">Good</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Status" />
                            <select v-model="form.status"
                                class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none">
                                <option value="available">Available</option>
                                <option value="not_available">Not Available</option>
                                <option value="under_maintenance">Under Maintenance</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <InputLabel :value="asset.img_path ? 'Replace Image (optional)' : 'Asset Image (optional)'" />
                        <input type="file" accept="image/*" @change="form.img_path = $event.target.files[0]"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-red-50 file:text-red-700 hover:file:bg-red-100" />
                        <InputError :message="form.errors.img_path" class="mt-1" />
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <Link :href="route('home')" class="text-sm text-gray-500 hover:text-gray-700">Cancel</Link>
                        <button type="submit" :disabled="form.processing"
                            :class="form.processing ? 'opacity-60 cursor-not-allowed' : 'hover:bg-red-700'"
                            class="inline-flex items-center gap-2 bg-red-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            <fa-icon v-if="form.processing" icon="circle-notch" class="w-4 h-4 animate-spin" />
                            <fa-icon v-else icon="floppy-disk" class="w-4 h-4" />
                            {{ form.processing ? 'Saving…' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>