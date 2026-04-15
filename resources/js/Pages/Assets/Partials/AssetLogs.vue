<script setup>
const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    logIcons: {
        type: Object,
        required: true,
    },
});

function logStyle(action) {
    return (
        props.logIcons[action] ?? {
            icon: "circle",
            color: "text-gray-400 bg-gray-50",
        }
    );
}

function fmt(date) {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
}
</script>
<template>
    <div
        class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
    >
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900">Activity Log</h2>
        </div>
        <div v-if="asset.logs?.length" class="px-6 py-4">
            <div
                v-for="(log, i) in asset.logs"
                :key="log.id"
                class="flex gap-4"
            >
                <!-- Connector line -->
                <div class="flex flex-col items-center">
                    <div
                        :class="[
                            'w-8 h-8 rounded-full flex items-center justify-center shrink-0',
                            logStyle(log.action).color,
                        ]"
                    >
                        <fa-icon
                            :icon="logStyle(log.action).icon"
                            class="w-3.5 h-3.5"
                        />
                    </div>
                    <div
                        v-if="i < asset.logs.length - 1"
                        class="w-px flex-1 bg-gray-100 my-1"
                    ></div>
                </div>
                <!-- Content -->
                <div class="pb-5 min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 capitalize">
                        {{ log.action.replace(/_/g, " ") }}
                    </p>
                    <p v-if="log.remarks" class="text-xs text-gray-500 mt-0.5">
                        {{ log.remarks }}
                    </p>
                    <div class="flex items-center gap-2 mt-1">
                        <span v-if="log.user" class="text-xs text-gray-400">{{
                            log.user.name
                        }}</span>
                        <span class="text-xs text-gray-300">·</span>
                        <span class="text-xs text-gray-400">{{
                            fmt(log.created_at)
                        }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="px-6 py-12 text-center text-sm text-gray-400">
            No activity logged.
        </div>
    </div>
</template>
