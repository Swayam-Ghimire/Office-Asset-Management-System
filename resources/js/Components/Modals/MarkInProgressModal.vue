<script setup>
import Modal from "@/Components/Modal.vue";
import { useForm } from "@inertiajs/vue3";
import { watch } from "vue";
const props = defineProps({
    show: Boolean,
    record: Object,
});

const emit = defineEmits(["close", "success"]);

const form = useForm({});

function submit() {
    form.patch(route("maintenance.in_progress", props.record.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit("success");
        },
    });
}
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div
                    class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center"
                >
                    <fa-icon icon="spinner" class="text-blue-600" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">
                        Mark as In Progress
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ record?.asset?.model_name }}
                    </p>
                </div>
            </div>

            <p class="text-sm text-gray-700 mb-2">
                Are you sure you want to mark this maintenance record as "In
                Progress"?
            </p>

            <div class="flex justify-end gap-3 mt-5">
                <button
                    @click="$emit('close')"
                    class="px-4 py-2 text-sm border rounded-lg"
                >
                    Cancel
                </button>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg"
                >
                    <fa-icon
                        v-if="form.processing"
                        icon="circle-notch"
                        class="animate-spin mr-1"
                    />
                    Confirm
                </button>
            </div>
        </div>
    </Modal>
</template>
