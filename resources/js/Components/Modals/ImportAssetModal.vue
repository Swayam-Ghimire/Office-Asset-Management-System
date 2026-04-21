<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    show: Boolean,
});

const emit = defineEmits(['close']);

const form = useForm({
    file: null,
});

const fileInput = ref(null);

const submit = () => {
    form.post(route('assets.import'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const closeModal = () => {
    form.errors.file = null;
    emit('close');
    form.reset();
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="md">
        <form @submit.prevent="submit" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Bulk Import Assets
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Upload an Excel (.xlsx) or CSV file. Ensure your columns match: 
                <span class="font-mono bg-gray-100 px-1">model_name, brand, category, purchase_date, condition, status</span>.
            </p>

            <div class="mt-6">
                <input
                    type="file"
                    ref="fileInput"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                    @input="form.file = $event.target.files[0]"
                    required
                />
                <InputError :message="form.errors.file" class="mt-2" />
                
                <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full mt-4 h-2 rounded overflow-hidden">
                    {{ form.progress.percentage }}%
                </progress>
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>
                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Upload & Import
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>