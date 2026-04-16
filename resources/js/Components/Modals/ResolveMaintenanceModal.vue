<script setup>
import Modal from "@/Components/Modal.vue";
import { useForm } from "@inertiajs/vue3";
import { watch } from "vue";

const props = defineProps({
    show: Boolean,
    record: Object,
});

const emit = defineEmits(["close", "success"]);

const form = useForm({
    resolution_note: "",
    new_condition: "good",
});

// Reset when record changes (important)
watch(
    () => props.record,
    () => {
        form.reset();
        form.new_condition = "good";
    }
);

function submit() {
    form.patch(route("maintenance.resolve", props.record.id), {
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
            <!-- Header -->
            <div class="flex items-center gap-3 mb-5">
                <div
                    class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0"
                >
                    <fa-icon
                        icon="screwdriver-wrench"
                        class="w-5 h-5 text-emerald-600"
                    />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">
                        Resolve Maintenance
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ record?.asset?.model_name }}
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Issue -->
                <div>
                    <label
                        class="block text-xs font-semibold text-gray-500 uppercase mb-1.5"
                    >
                        Issue Description
                    </label>
                    <p
                        class="text-sm text-gray-700 bg-gray-50 rounded-lg px-3 py-2.5 border"
                    >
                        {{ record?.description }}
                    </p>
                </div>

                <!-- Note -->
                <div>
                    <label
                        class="block text-xs font-semibold text-gray-500 uppercase mb-1.5"
                    >
                        Resolution Note
                    </label>
                    <textarea
                        v-model="form.resolution_note"
                        rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm"
                        placeholder="Describe what was fixed..."
                    />
                </div>

                <!-- Condition -->
                <div>
                    <label
                        class="block text-xs font-semibold text-gray-500 uppercase mb-1.5"
                    >
                        Asset Condition <span class="text-rose-500">*</span>
                    </label>

                    <select
                        v-model="form.new_condition"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"
                    >
                        <option value="new">New</option>
                        <option value="good">Good</option>
                        <option value="damaged">Damaged</option>
                    </select>

                    <p
                        v-if="form.errors.new_condition"
                        class="text-xs text-rose-600 mt-1"
                    >
                        {{ form.errors.new_condition }}
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 mt-6">
                <button
                    @click="$emit('close')"
                    class="px-4 py-2 text-sm border rounded-lg"
                >
                    Cancel
                </button>

                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg"
                >
                    <fa-icon
                        v-if="form.processing"
                        icon="circle-notch"
                        class="animate-spin mr-1"
                    />
                    Mark as Resolved
                </button>
            </div>
        </div>
    </Modal>
</template>