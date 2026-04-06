<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    href:   { type: String,  required: true },
    icon:   { type: String,  required: true },   // FA icon name e.g. 'box'
    label:  { type: String,  required: true },
    badge:  { type: Number,  default: null },     // unread count etc.
    exact:  { type: Boolean, default: false },    // exact match vs startsWith
});

const page = usePage();

const isActive = computed(() => {
    const current = page.url;
    if (props.exact) return current === props.href;
    return current.startsWith(props.href);
});
</script>

<template>
    <Link
        :href="href"
        :class="[
            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 group',
            isActive
                ? 'bg-red-50 text-red-700'
                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
        ]"
    >
        <!-- Icon -->
        <fa-icon
            :icon="icon"
            :class="[
                'w-4 h-4 shrink-0',
                isActive ? 'text-red-600' : 'text-gray-400 group-hover:text-gray-600',
            ]"
        />

        <!-- Label -->
        <span class="flex-1 truncate">{{ label }}</span>

        <!-- Badge (e.g. pending count) -->
        <span
            v-if="badge && badge > 0"
            class="ml-auto inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-semibold bg-red-600 text-white"
        >
            {{ badge > 99 ? '99+' : badge }}
        </span>
    </Link>
</template>