<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    assets: Object,
    isAdmin: Boolean,
});

</script>

<template>
    <AuthenticatedLayout :isAdmin="isAdmin">
        
    </AuthenticatedLayout>
</template>