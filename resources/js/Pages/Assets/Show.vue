<script setup>
// Asset Show
import { ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CategoryIcon from "@/Components/UI/CategoryIcon.vue";
import AssetDetails from "./Partials/AssetDetails.vue";
import AssetHistory from "./Partials/AssetHistory.vue";
import AssetLogs from "./Partials/AssetLogs.vue";
import AssetRequests from "./Partials/AssetRequests.vue";
import AssetMaintenanceHistory from "./Partials/AssetMaintenanceHistory.vue";

const props = defineProps({
    asset: { type: Object, required: true },
});
const page = usePage();
const isAdmin = page.props.isAdmin;

const activeTab = ref("details");

const statusColors = {
    available: "bg-emerald-100 text-emerald-700",
    not_available: "bg-blue-100 text-blue-700",
    under_maintenance: "bg-amber-100 text-amber-700",
};
const conditionColors = {
    new: "bg-violet-100 text-violet-700",
    good: "bg-sky-100 text-sky-700",
    damaged: "bg-rose-100 text-rose-700",
};

const logIcons = {
    created: { icon: "plus", color: "text-emerald-500 bg-emerald-50" },
    updated: { icon: "pen", color: "text-blue-500 bg-blue-50" },
    approved: { icon: "circle-check", color: "text-emerald-500 bg-emerald-50" },
    rejected: { icon: "circle-xmark", color: "text-rose-500 bg-rose-50" },
    returned: { icon: "rotate-left", color: "text-gray-500 bg-gray-50" },
    deleted: { icon: "trash", color: "text-rose-500 bg-rose-50" },
    restored: { icon: "box-archive", color: "text-violet-500 bg-violet-50" },
    maintenance_reported: {
        icon: "triangle-exclamation",
        color: "text-amber-500 bg-amber-50",
    },
    maintenance_resolved: {
        icon: "screwdriver-wrench",
        color: "text-teal-500 bg-teal-50",
    },
    maintenance_in_progress: {
        icon: "spinner",
        color: "text-amber-500 bg-amber-50",
    },
    return_requested: {
        icon: "rotate-left",
        color: "text-rose-500 bg-gray-50",
    }
};

function deleteAsset() {
    if (confirm("Move this asset to trash?")) {
        router.delete(route("assets.destroy", props.asset.id));
    }
}
</script>

<template>
    <Head :title="asset.model_name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center"
                    >
                        <CategoryIcon
                            :name="asset.category?.name"
                            size="md"
                            class="text-gray-500"
                        />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">
                            {{ asset.model_name }}
                        </h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span
                                :class="[
                                    'text-xs px-2 py-0.5 rounded-full font-medium',
                                    statusColors[asset.status],
                                ]"
                            >
                                {{ asset.status.replace("_", " ") }}
                            </span>
                            <span
                                :class="[
                                    'text-xs px-2 py-0.5 rounded-full font-medium',
                                    conditionColors[asset.condition],
                                ]"
                            >
                                {{ asset.condition }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="isAdmin" class="flex gap-2">
                    <Link
                        :href="route('assets.edit', asset.id)"
                        class="inline-flex items-center gap-1.5 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors"
                    >
                        <fa-icon icon="pen" class="w-3.5 h-3.5" /> Edit
                    </Link>
                    <button
                        @click="deleteAsset"
                        class="inline-flex items-center gap-1.5 text-sm border border-rose-200 text-rose-600 hover:bg-rose-50 px-3 py-2 rounded-lg transition-colors"
                    >
                        <fa-icon icon="trash" class="w-3.5 h-3.5" /> Delete
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Tabs -->
            <div
                v-if="isAdmin"
                class="flex gap-1 bg-gray-100 p-1 rounded-xl w-fit"
            >
                <button
                    v-for="tab in [
                        'details',
                        'history',
                        'requests',
                        'logs',
                        'maintenance history',
                    ]"
                    :key="tab"
                    @click="activeTab = tab"
                    :class="[
                        'px-4 py-1.5 rounded-lg text-sm font-medium transition-colors capitalize',
                        activeTab === tab
                            ? 'bg-white shadow text-gray-900'
                            : 'text-gray-500 hover:text-gray-700',
                    ]"
                >
                    {{ tab }}
                </button>
            </div>

            <!-- Details tab -->
            <AssetDetails
                v-if="activeTab === 'details'"
                :asset="asset"
                :conditionColors="conditionColors"
                :statusColors="statusColors"
                :isAdmin="isAdmin"
            />

            <!-- History (assignments) tab -->
            <AssetHistory v-if="activeTab === 'history'" :asset="asset" />

            <!-- Requests tab (admin sees reason) -->
            <AssetRequests v-if="activeTab === 'requests'" :asset="asset" />

            <!-- Logs tab — timeline -->
            <AssetLogs
                v-if="activeTab === 'logs'"
                :asset="asset"
                :logIcons="logIcons"
            />
            <!-- Maintenance History tab -->
            <AssetMaintenanceHistory
                v-if="activeTab === 'maintenance history'"
                :maintenances="asset.maintenance"
                :logIcons="logIcons"
            />
        </div>
    </AuthenticatedLayout>
</template>
