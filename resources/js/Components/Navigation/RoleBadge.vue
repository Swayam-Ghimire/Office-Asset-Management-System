<script setup>
defineProps({
    role: { type: String, required: true }, // 'admin' | 'employee'
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full uppercase tracking-wide',
            role === 'admin'
                ? 'bg-red-100 text-red-700'
                : 'bg-blue-100 text-blue-700',
        ]"
    >
        <fa-icon
            :icon="role === 'admin' ? 'shield-halved' : 'user'"
            class="w-2.5 h-2.5"
        />
        {{ role }}
    </span>
</template>