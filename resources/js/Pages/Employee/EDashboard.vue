<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';


defineProps({
    stats: Object,
    myAssignments: Array,
    myRequests: Array,
})

function returnAsset(assignmentId) {
    router.post(route('asset-assignments.return', assignmentId));
}

</script>

<template>
    <Head title="Employee Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            

        </template>

    </AuthenticatedLayout>
</template>