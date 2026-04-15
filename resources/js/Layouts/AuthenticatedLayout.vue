<script setup>
import { ref, watch, onMounted } from 'vue';
import Sidebar from '@/Components/Navigation/Sidebar.vue';
import TopBar  from '@/Components/Navigation/TopBar.vue';
import { useFlash } from '@/Composables/useFlash.js';

defineProps({
    pageTitle: { type: String, default: '' },
});

useFlash();

const sidebarOpen = ref(false);
</script>

<template>
    
    <div class="flex h-screen overflow-hidden bg-gray-50">

        <!-- Sidebar  -->
        <Sidebar
            :open="sidebarOpen"
            @close="sidebarOpen = false"
        />

        <!-- Main area  -->
        <div class="flex flex-col flex-1 min-w-0 overflow-y-auto">

            <!-- Top bar -->
            <TopBar
                :pageTitle="pageTitle"
                @toggle-sidebar="sidebarOpen = !sidebarOpen"
            />

            <!-- Optional page header slot (breadcrumbs + action buttons) -->
            <header v-if="$slots.header" class="bg-white border-b border-gray-100 px-6 py-4">
                <slot name="header" />
            </header>

            <!-- Page content -->
            <main class="flex-1 px-4 sm:px-6 py-6">
                <slot />
            </main>
        </div>
    </div>
</template>