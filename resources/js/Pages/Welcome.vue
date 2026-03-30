<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import HeroSection from '@/Components/HeroSection.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
    canLogin: Boolean,
});
</script>

<template>
    <Head title="Welcome" />

    <!-- FULL PAGE FLEX -->
    <div class="min-h-screen flex flex-col bg-gray-50 dark:bg-black">
        <!-- FLEX COLUMN LAYOUT -->
    
            <!-- Navbar -->
            <Navbar :canLogin="canLogin" :user="$page.props.auth.user" />
    
            <!-- Main Content -->
            <main class="flex-grow">
                <HeroSection />
            </main>
    
            <!-- Footer ALWAYS AT BOTTOM -->
            <Footer />
    </div>

</template>