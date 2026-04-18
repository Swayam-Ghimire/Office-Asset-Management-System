<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";

const page = usePage();

// These are shared via HandleInertiaRequests middleware

const unreadCount = ref(page.props.unread_notifications_count ?? 0);
const notifications = ref(page.props.recent_notifications ?? []);

const open = ref(false);

function close(e) {
    if (!e.target.closest("#notif-bell")) open.value = false;
}
onMounted(() => document.addEventListener("click", close));
onUnmounted(() => document.removeEventListener("click", close));

function markRead(notif) {
    router.post(
        route("notifications.read", notif.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                notif.read_at = new Date().toISOString();
                unreadCount.value = Math.max(0, unreadCount.value - 1);
                open.value = false;
            },
        },
    );
}

function markAllRead() {
    router.post(
        route("notifications.read-all"),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                notifications.value.forEach(
                    (n) => (n.read_at = new Date().toISOString()),
                );
                unreadCount.value = 0;
            },
        },
    );
}

// Icon per notification type
const iconMap = {
    request_submitted: { icon: "clipboard-list", color: "text-blue-500" },
    request_approved: { icon: "circle-check", color: "text-emerald-500" },
    request_rejected: { icon: "circle-xmark", color: "text-rose-500" },
    asset_assigned: { icon: "link", color: "text-violet-500" },
    issue_reported: { icon: "triangle-exclamation", color: "text-amber-500" },
};

function iconFor(type) {
    const key = Object.keys(iconMap).find((k) => type?.includes(k));
    return iconMap[key] ?? { icon: "bell", color: "text-gray-400" };
}

function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60) return "just now";
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return `${Math.floor(diff / 86400)}d ago`;
}
</script>

<template>
    <div id="notif-bell" class="relative">
        <!-- Bell button -->
        <button
            @click="open = !open"
            class="relative flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
            aria-label="Notifications"
        >
            <fa-icon icon="bell" class="w-4.5 h-4.5" />
            <!-- Unread badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-red-600 text-white text-[9px] font-bold flex items-center justify-center"
            >
                {{ unreadCount > 99 ? "99+" : unreadCount }}
            </span>
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
                class="absolute right-0 mt-2 w-80 bg-white border border-gray-100 rounded-xl shadow-lg z-50 overflow-hidden"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between px-4 py-3 border-b border-gray-100"
                >
                    <span class="text-sm font-semibold text-gray-900"
                        >Notifications</span
                    >
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllRead"
                        class="text-xs text-red-600 hover:underline"
                    >
                        Mark all read
                    </button>
                </div>

                <!-- List -->
                <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                    <div
                        v-if="notifications.length === 0"
                        class="px-4 py-8 text-center text-sm text-gray-400"
                    >
                        <fa-icon
                            icon="bell"
                            class="w-8 h-8 mb-2 text-gray-200"
                        />
                        <p>No notifications yet</p>
                    </div>

                    <button
                        v-for="notif in notifications"
                        :key="notif.id"
                        @click="markRead(notif)"
                        class="flex items-start gap-3 w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                        :class="!notif.read_at ? 'bg-blue-50/40' : ''"
                    >
                        <!-- Type icon -->
                        <div
                            class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0 mt-0.5"
                        >
                            <fa-icon
                                :icon="iconFor(notif.type).icon"
                                :class="[
                                    'w-3.5 h-3.5',
                                    iconFor(notif.type).color,
                                ]"
                            />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 leading-snug">
                                {{ notif.data?.message }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ timeAgo(notif.created_at) }}
                            </p>
                        </div>

                        <!-- Unread dot -->
                        <span
                            v-if="!notif.read_at"
                            class="w-2 h-2 rounded-full bg-red-500 shrink-0 mt-1.5"
                        />
                    </button>
                </div>

                <!-- Footer -->
                <div class="border-t border-gray-100 px-4 py-2.5">
                    <Link
                        :href="route('notifications.index')"
                        class="text-xs text-red-600 font-medium hover:underline"
                        @click="open = false"
                    >
                        View all notifications →
                    </Link>
                </div>
            </div>
        </Transition>
    </div>
</template>
