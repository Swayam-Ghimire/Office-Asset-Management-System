<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    notifications: Object, // paginated Laravel notifications
});

function markRead(id) {
    router.post(route("notifications.read", id), {}, { preserveScroll: true });
}

function markAllRead() {
    router.post(route("notifications.read-all"), {}, { preserveScroll: true });
}

function fmt(date) {
    if (!date) return "";
    return new Date(date).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60) return "just now";
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return `${Math.floor(diff / 86400)}d ago`;
}

function notifMessage(notif) {
    try {
        const data =
            typeof notif.data === "string"
                ? JSON.parse(notif.data)
                : notif.data;
        return data?.message ?? "—";
    } catch {
        return "—";
    }
}

function notifData(notif) {
    try {
        return typeof notif.data === "string"
            ? JSON.parse(notif.data)
            : notif.data;
    } catch {
        return {};
    }
}

// Map notif.data.type → icon + colour.
// Each toDatabase() in every notification class must return a 'type' key
// matching one of these entries, and a 'message' key for the display text.
const iconMap = {
    // Employee notifications
    request_approved: {
        icon: "circle-check",
        color: "bg-emerald-100 text-emerald-600",
    },
    request_rejected: {
        icon: "circle-xmark",
        color: "bg-rose-100 text-rose-600",
    },
    return_requested: {
        icon: "rotate-left",
        color: "bg-amber-100 text-amber-600",
    },
    maintenance_in_progress: {
        icon: "screwdriver-wrench",
        color: "bg-blue-100 text-blue-600",
    },
    maintenance_resolved: {
        icon: "screwdriver-wrench",
        color: "bg-emerald-100 text-emerald-600",
    },

    // Admin notifications
    issue_reported: {
        icon: "triangle-exclamation",
        color: "bg-amber-100 text-amber-600",
    },
    request_submitted: {
        icon: "clipboard-list",
        color: "bg-blue-100 text-blue-600",
    },
    asset_returned: { icon: "rotate-left", color: "bg-gray-100 text-gray-600" },
};

function iconFor(notif) {
    const data = notifData(notif);
    return (
        iconMap[data?.type] ?? {
            icon: "bell",
            color: "bg-gray-100 text-gray-500",
        }
    );
}

const hasUnread = props.notifications.data.some((n) => !n.read_at);
</script>

<template>
    <Head title="Notifications" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Notifications
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Your activity alerts and updates
                    </p>
                </div>
                <button
                    v-if="hasUnread"
                    @click="markAllRead"
                    class="inline-flex items-center gap-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg transition-colors"
                >
                    <fa-icon
                        icon="circle-check"
                        class="w-4 h-4 text-emerald-500"
                    />
                    Mark all read
                </button>
            </div>
        </template>

        <div class="max-w-3xl mx-auto space-y-4">
            <div
                class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
            >
                <div
                    v-if="notifications.data.length > 0"
                    class="divide-y divide-gray-50"
                >
                    <div
                        v-for="notif in notifications.data"
                        :key="notif.id"
                        :class="[
                            'flex items-start gap-4 px-6 py-4 transition-colors',
                            !notif.read_at
                                ? 'bg-blue-50/40'
                                : 'hover:bg-gray-50',
                        ]"
                    >
                        <!-- Type icon -->
                        <div
                            :class="[
                                'w-9 h-9 rounded-full flex items-center justify-center shrink-0 mt-0.5',
                                iconFor(notif).color,
                            ]"
                        >
                            <fa-icon
                                :icon="iconFor(notif).icon"
                                class="w-4 h-4"
                            />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 leading-snug">
                                {{ notifMessage(notif) }}
                            </p>
                            <div class="flex items-center gap-3 mt-1 flex-wrap">
                                <span class="text-xs text-gray-400">{{
                                    timeAgo(notif.created_at)
                                }}</span>
                                <span class="text-xs text-gray-300">·</span>
                                <span class="text-xs text-gray-400">{{
                                    fmt(notif.created_at)
                                }}</span>

                                <!-- Link to related asset -->
                                <Link
                                    v-if="notifData(notif).asset_id"
                                    :href="
                                        route(
                                            'assets.show',
                                            notifData(notif).asset_id,
                                        )
                                    "
                                    class="text-xs text-red-600 hover:underline"
                                >
                                    View asset →
                                </Link>

                                <!-- Link to maintenance index for issue_reported -->
                                <Link
                                    v-if="
                                        notifData(notif).type ===
                                            'issue_reported' ||
                                        notifData(notif).type ===
                                            'asset_returned'
                                    "
                                    :href="route('maintenance.index')"
                                    class="text-xs text-blue-600 hover:underline"
                                >
                                    View maintenance →
                                </Link>
                            </div>
                        </div>

                        <!-- Unread indicator + mark read -->
                        <div class="flex items-center gap-2 shrink-0">
                            <span
                                v-if="!notif.read_at"
                                class="w-2 h-2 rounded-full bg-blue-500 mt-1"
                            ></span>
                            <button
                                v-if="!notif.read_at"
                                @click="markRead(notif.id)"
                                class="text-xs text-gray-400 hover:text-emerald-600 transition-colors"
                                title="Mark as read"
                            >
                                <fa-icon icon="circle-check" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty -->
                <div v-else class="px-6 py-16 text-center">
                    <fa-icon icon="bell" class="w-10 h-10 text-gray-200 mb-3" />
                    <p class="text-sm font-semibold text-gray-500 mb-1">
                        All caught up
                    </p>
                    <p class="text-xs text-gray-400">No notifications yet.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="notifications.last_page > 1"
                class="flex justify-center gap-1"
            >
                <template v-for="link in notifications.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-sm border transition-colors',
                            link.active
                                ? 'bg-red-600 text-white border-red-600'
                                : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300',
                        ]"
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="px-3 py-1.5 rounded-lg text-sm border border-gray-100 bg-gray-50 text-gray-300"
                    />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
