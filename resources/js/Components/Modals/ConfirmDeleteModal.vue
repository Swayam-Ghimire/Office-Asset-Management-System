<script setup>
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    show: Boolean,
    title: String,
    subtitle: String,
    confirmText: String,
});

const emit = defineEmits(["close", "confirm"]);
</script>

<template>
    <Modal :show="show" @close="emit('close')">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center shrink-0"
                >
                    <fa-icon icon="trash" class="w-5 h-5 text-rose-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">
                        {{ title }}
                    </h3>
                    <p v-if="itemName" class="text-sm text-gray-500">
                        {{ itemName }}
                    </p>
                </div>
            </div>

            <p class="text-sm text-gray-700 mb-2">
                <slot>
                    {{ warningText }}
                </slot>
            </p>

            <div class="flex justify-end gap-3 mt-6">
                <button
                    @click="emit('close')"
                    type="button"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Cancel
                </button>
                <button
                    @click="emit('confirm')"
                    type="button"
                    class="px-4 py-2 text-sm bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-medium transition-colors"
                >
                    {{ confirmButtonText }}
                </button>
            </div>
        </div>
    </Modal>
</template>
