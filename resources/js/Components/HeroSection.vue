<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="text-center py-20 bg-gray-50 dark:bg-black">
        <div class="max-w-5xl mx-auto px-6">

            <!-- Heading -->
            <h1 class="text-4xl md:text-6xl font-bold text-gray-900 dark:text-white leading-tight">
                Office Asset Management System
            </h1>

            <!-- Subtext -->
            <p class="mt-6 text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                Track, manage, and monitor all your office assets in one place. 
                From laptops to furniture, stay organized and reduce losses with real-time insights.
            </p>

            <!-- Features Highlights -->
            <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                <div class="p-5 rounded-lg bg-white dark:bg-gray-900 shadow">
                    <h3 class="font-semibold text-lg text-gray-900 dark:text-white">
                        📦 Asset Tracking
                    </h3>
                    <p class="text-sm mt-2 text-gray-600 dark:text-gray-400">
                        Easily monitor asset location, status, and ownership.
                    </p>
                </div>

                <div class="p-5 rounded-lg bg-white dark:bg-gray-900 shadow">
                    <h3 class="font-semibold text-lg text-gray-900 dark:text-white">
                        🧾 Asset Management
                    </h3>
                    <p class="text-sm mt-2 text-gray-600 dark:text-gray-400">
                        Maintain accurate records of all company assets.
                    </p>
                </div>

                <div class="p-5 rounded-lg bg-white dark:bg-gray-900 shadow">
                    <h3 class="font-semibold text-lg text-gray-900 dark:text-white">
                        📊 Reports 
                    </h3>
                    <p class="text-sm mt-2 text-gray-600 dark:text-gray-400">
                        Generate reports using asset logs.
                    </p>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div class="mt-10 flex justify-center gap-4 flex-wrap">
                <Link
                    :href="route('login')"
                    class="bg-red-500 text-white px-6 py-3 rounded-lg hover:bg-red-600"
                >
                    LogIn to Portal
                </Link>
            </div>

        </div>
    </section>
</template>