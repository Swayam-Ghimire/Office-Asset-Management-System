<script setup>
import { ref } from 'vue';

defineProps({
    label:      { type: String,  required: true },
    icon:       { type: String,  default: null },
    collapsible:{ type: Boolean, default: false },
    defaultOpen:{ type: Boolean, default: true },
});

const open = ref(true);
</script>

<template>
    <div class="mb-1">
        <!-- Section header -->
        <button
            v-if="collapsible"
            @click="open = !open"
            class="flex items-center w-full gap-2 px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-600 transition-colors"
        >
            <fa-icon v-if="icon" :icon="icon" class="w-3 h-3" />
            <span class="flex-1 text-left">{{ label }}</span>
            <fa-icon
                :icon="open ? 'chevron-down' : 'chevron-right'"
                class="w-3 h-3"
            />
        </button>

        <!-- Non-collapsible label -->
        <div
            v-else
            class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider"
        >
            <fa-icon v-if="icon" :icon="icon" class="w-3 h-3" />
            <span>{{ label }}</span>
        </div>

        <!-- Links slot -->
        <div v-show="!collapsible || open" class="space-y-0.5">
            <slot />
        </div>
    </div>
</template>