<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    maintenances: { type: Array, default: () => [] },
    logIcons: { type: Object, required: true },
});


const page = usePage();
const isAdmin = page.props.isAdmin;

function fmt(date) {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
}

const statusStyle = {
    reported: {
        pill: "bg-amber-100 text-amber-700 border border-amber-200",
        dot: "bg-amber-400",
        label: "Reported",
    },
    in_progress: {
        pill: "bg-blue-100 text-blue-700 border border-blue-200",
        dot: "bg-blue-400",
        label: "In Progress",
    },
    resolved: {
        pill: "bg-emerald-100 text-emerald-700 border border-emerald-200",
        dot: "bg-emerald-400",
        label: "Resolved",
    },
};

function style(status) {
    return (
        statusStyle[status] ?? {
            pill: "bg-gray-100 text-gray-600 border border-gray-200",
            dot: "bg-gray-400",
            label: status,
        }
    );
}

// Most recent open record (if any) — used for the alert banner
const openRecord = computed(
    () =>
        props.maintenances.find(
            (r) => r.status === "reported" || r.status === "in_progress",
        ) ?? null,
);
</script>

<template>
    <div
        class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
    >
        <div
            class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
        >
            <h2 class="font-semibold text-gray-900">Maintenance History</h2>
            <span class="text-xs text-gray-400"
                >{{ maintenances.length }} record{{
                    maintenances.length !== 1 ? "s" : ""
                }}</span
            >
        </div>

        <!-- Open-issue banner (shown while asset is under maintenance) -->
        <div
            v-if="openRecord"
            class="mx-6 mt-4 flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3"
        >
            <fa-icon
                icon="triangle-exclamation"
                class="w-4 h-4 text-amber-600 mt-0.5 shrink-0"
            />
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-amber-800">
                    Open issue — {{ style(openRecord.status).label }}
                </p>
                <p class="text-xs text-amber-600 mt-0.5 line-clamp-2">
                    {{ openRecord.description }}
                </p>
            </div>
        </div>

        <!-- Records list -->
        <div v-if="maintenances.length > 0" class="divide-y divide-gray-50 mt-4">
            <div
                v-for="record in maintenances"
                :key="record.id"
                class="px-6 py-4 hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-start gap-4">
                    <!-- Status indicator column -->
                    <div class="flex flex-col items-center pt-1 shrink-0">
                        <span
                            :class="[
                                'w-2.5 h-2.5 rounded-full',
                                style(record.status).dot,
                            ]"
                        ></span>
                    </div>

                    <!-- Main content -->
                    <div class="flex-1 min-w-0">
                        <!-- Top row: reporter + status + date -->
                        <div class="flex items-center gap-3 flex-wrap">
                            <div class="flex items-center gap-2">
                                <img
                                    :src="'/storage/' + record.reporter?.img_path ?? `https://ui-avatars.com/api/?name=${encodeURIComponent(record.reporter?.name ?? 'U')}&color=ffffff&background=DC143C&size=24`"
                                    class="w-6 h-6 rounded-full shrink-0"
                                />
                                <span class="text-sm font-medium text-gray-900">
                                    {{ record.reporter?.name ?? "Unknown" }}
                                </span>
                            </div>
                            <span
                                :class="[
                                    'inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium',
                                    style(record.status).pill,
                                ]"
                            >
                                <span
                                    :class="[
                                        'w-1.5 h-1.5 rounded-full',
                                        style(record.status).dot,
                                    ]"
                                ></span>
                                {{ style(record.status).label }}
                            </span>
                            <span class="text-xs text-gray-400"
                                >Reported {{ fmt(record.reported_at) }}</span
                            >
                            <span
                                v-if="record.resolved_at"
                                class="text-xs text-emerald-600"
                            >
                                · Resolved {{ fmt(record.resolved_at) }}
                            </span>
                        </div>

                        <!-- Description -->
                        <p class="text-sm text-gray-600 mt-1.5 leading-relaxed">
                            {{ record.description }}
                        </p>

                        <!-- Resolution note (if any) -->
                        <div
                            v-if="
                                record.status === 'resolved' &&
                                record.resolution_note
                            "
                            class="mt-2 text-xs text-gray-500 bg-gray-50 border border-gray-100 rounded px-3 py-2 italic"
                        >
                            Resolution: {{ record.resolution_note }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty -->
        <div v-else class="px-6 py-12 text-center">
            <fa-icon
                icon="screwdriver-wrench"
                class="w-8 h-8 text-gray-200 mb-2"
            />
            <p class="text-sm text-gray-400">
                No maintenance records for this asset.
            </p>
        </div>
    </div>
</template>
