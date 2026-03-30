<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: Boolean,
    user: Object,
});
</script>

<template>
    <nav class="w-full bg-white dark:bg-black shadow-sm">
        <div class="max-w-7xl mx-auto p-6 flex items-center justify-between">
            
            <!-- Logo -->
            <div class="text-xl font-bold text-gray-900 dark:text-white">
                <ApplicationLogo />
            </div>

            <!-- Auth -->
            <div v-if="canLogin">
                <Link
                    v-if="user"
                    :href="route('dashboard')"
                    class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600"
                >
                    Dashboard
                </Link>

                <Link
                    v-else
                    :href="route('login')"
                    class="border border-red-500 text-red-500 px-4 py-2 rounded-md hover:bg-red-500 hover:text-white"
                >
                    Log in
                </Link>
            </div>

        </div>
    </nav>
</template>