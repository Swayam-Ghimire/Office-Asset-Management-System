<script setup>
import Modal from "@/Components/Modal.vue";
import { computed } from "vue";

const props = defineProps({
    show: Boolean,
    actionType: {
        type: String,
        default: "approve", // 'approve', 'reject', issue, return
    },
    title: String,
    subtitle: String,
    confirmText: String,
});

const emit = defineEmits(["close", "confirm"]);

// Computed properties to change styles based on the action type
const isApprove = computed(() => props.actionType === "approve");
const isReject = computed(() => props.actionType === "reject");
const isReturn = computed(() => props.actionType === "return");
const isIssue = computed(() => props.actionType === "issue");
</script>

<template>
    <Modal :show="show" @close="emit('close')">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div
                    :class="[
                        'w-10 h-10 rounded-full flex items-center justify-center shrink-0', {
                            'bg-emerald-100' : isApprove, 
                            'bg-red-100' : isReject,
                            'bg-rose-100' : isReturn,
                            'bg-amber-100' : isIssue
                        }
                    ]"
                >
                    <fa-icon
                        v-show="isApprove"
                        :icon="['fas', 'check-circle']"
                        class="text-emerald-600 text-lg"
                    />
                    <fa-icon
                        v-show="isReject"
                        :icon="['fas', 'circle-xmark']"
                        class="text-red-600 text-lg"
                    />
                    <fa-icon
                        v-show="isReturn"
                        :icon="['fas', 'arrow-rotate-left']"
                        class="text-rose-600 text-lg"
                        />
                    <fa-icon
                        v-show="isIssue"
                        :icon="['fas', 'triangle-exclamation']"
                        class="text-amber-600 text-lg"
                        />

                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">
                        {{ title }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ subtitle }}
                    </p>
                </div>
            </div>

            <div class="text-sm text-gray-700 mb-6">
                <slot />
            </div>

            <div class="flex justify-end gap-3">
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
                    :class="[
                        'px-4 py-2 text-sm text-white rounded-lg transition-colors font-medium', {
                            'bg-emerald-600 hover:bg-emerald-700' : isApprove,
                            'bg-red-600 hover:bg-red-700' : isReject,
                            'bg-rose-600 hover:bg-rose-700' : isReturn,
                            'bg-amber-600 hover:bg-amber-700' : isIssue
                        } 
                    ]"
                >
                    {{ confirmText }}
                </button>
            </div>
        </div>
    </Modal>
</template>
