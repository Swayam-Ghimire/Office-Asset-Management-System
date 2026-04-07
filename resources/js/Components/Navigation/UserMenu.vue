<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import RoleBadge from "@/Components/Navigation/RoleBadge.vue";

const page = usePage();
const user = page.props.auth.user;
const roles = page.props.auth.roles ?? []; // shared from HandleInertiaRequests
const primaryRole = roles[0] ?? "employee";

const open = ref(false);
const photoPreview = ref(
    user.img_path ? "/storage/" + user.img_path : avatarUrl(user.name),
);
function avatarUrl(name) {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&color=ffffff&background=DC143C`;
}

function close(e) {
    if (!e.target.closest("#user-menu")) open.value = false;
}
onMounted(() => document.addEventListener("click", close));
onUnmounted(() => document.removeEventListener("click", close));
</script>

<template>
    <div id="user-menu" class="relative">
        <!-- Trigger -->
        <button
            @click="open = !open"
            class="flex items-center gap-2.5 rounded-lg px-2 py-1.5 hover:bg-gray-100 transition-colors"
        >
            <img
                :src="photoPreview"
                :alt="user.name"
                class="w-8 h-8 rounded-full object-cover border border-gray-200"
            />
            <div class="hidden md:block text-left">
                <p class="text-sm font-medium text-gray-900 leading-none">
                    {{ user.name }}
                </p>
                <RoleBadge :role="primaryRole" class="mt-1" />
            </div>
            <fa-icon
                icon="chevron-down"
                class="w-3 h-3 text-gray-400 hidden md:block"
            />
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-show="open"
                class="absolute right-0 mt-2 w-52 bg-white border border-gray-100 rounded-xl shadow-lg z-50 overflow-hidden"
            >
                <!-- User info header -->
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                    <p class="text-sm font-semibold text-gray-900 truncate">
                        {{ user.name }}
                    </p>
                    <p class="text-xs text-gray-400 truncate">
                        {{ user.email }}
                    </p>
                </div>

                <!-- Links -->
                <div class="py-1">
                    <Link
                        :href="route('profile.edit')"
                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                        @click="open = false"
                    >
                        <fa-icon
                            icon="user-pen"
                            class="w-4 h-4 text-gray-400"
                        />
                        Edit profile
                    </Link>
                </div>

                <!-- Logout -->
                <div class="border-t border-gray-100 py-1">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 transition-colors"
                        @click="open = false"
                    >
                        <fa-icon
                            icon="arrow-right-from-bracket"
                            class="w-4 h-4"
                        />
                        Log out
                    </Link>
                </div>
            </div>
        </Transition>
    </div>
</template>
