<script setup>
import NotificationBell from "@/Components/Navigation/NotificationBell.vue";
import UserMenu from "@/Components/Navigation/UserMenu.vue";

defineProps({
    pageTitle: { type: String, default: "" },
});

const emit = defineEmits(["toggle-sidebar"]);
</script>

<template>
    <header
        class="sticky top-0 z-20 flex items-center justify-between h-16 px-4 sm:px-6 bg-white border-b border-gray-100 shadow-sm"
    >
        <!-- Left: mobile hamburger + page title -->
        <div class="flex items-center gap-3">
            <!-- Hamburger — only visible on mobile -->
            <button
                @click="emit('toggle-sidebar')"
                class="lg:hidden flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
                aria-label="Toggle sidebar"
            >
                <fa-icon icon="bars" class="w-4 h-4" />
            </button>

            <h1
                v-if="pageTitle"
                class="text-base font-semibold text-gray-800 truncate"
            >
                {{ pageTitle }}
            </h1>
        </div>

        <!-- Right: bell + user -->
        <div class="flex items-center gap-2">
            <NotificationBell />
            <div class="w-px h-6 bg-gray-200 mx-1" />
            <UserMenu />
        </div>
    </header>
</template>
