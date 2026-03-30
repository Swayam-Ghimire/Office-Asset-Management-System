<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout :isAdmin="true">
        <template #header>
        </template>
        // sidebar
        // card components
    </AuthenticatedLayout>
</template>
