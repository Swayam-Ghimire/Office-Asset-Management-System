<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ConfirmActionModal from "@/Components/Modals/ConfirmActionModal.vue";
import AssetBarChart from "@/Components/Assets/AssetBarChart.vue";
import AssetDoughnutChart from "@/Components/Assets/AssetDoughnutChart.vue";
import RequestTrendChart from "@/Components/Assets/RequestTrendChart.vue";

const props = defineProps({
    stats: Object,
    pendingRequests: Array,
    recentAssignments: Array,
    assetsByCategory: Object,
    assetStatusBreakdown: Object,
    requestTrend: Object,
});

// Approve / Reject modals
const showApproveModal = ref(false);
const showRejectionModal = ref(false);
const selectedRequest = ref(null);

function confirmApproval(req) {
    selectedRequest.value = req;
    showApproveModal.value = true;
}

function confirmRejection(req) {
    selectedRequest.value = req;
    showRejectionModal.value = true;
}

function approveRequest(id) {
    router.put(
        route("asset-requests.approve", id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showApproveModal.value = false;
                selectedRequest.value = null;
            },
        },
    );
}

function rejectRequest(id) {
    router.put(
        route("asset-requests.reject", id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showRejectionModal.value = false;
                selectedRequest.value = null;
            },
        },
    );
}

const assignmentStatusColors = {
    assigned: "bg-blue-100 text-blue-700",
    returned: "bg-gray-100 text-gray-600",
};

function fmt(date) {
    return new Date(date).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                        Admin Dashboard
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Overview of your asset management system
                    </p>
                </div>
                <Link
                    :href="route('assets.create')"
                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm"
                >
                    <fa-icon icon="plus" class="w-4 h-4" />
                    Add Asset
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Stat tiles -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div
                    class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
                >
                    <p
                        class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1"
                    >
                        Total Assets
                    </p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ stats.total_assets }}
                    </p>
                    <div class="mt-3 h-1 bg-gray-100 rounded-full">
                        <div class="h-1 bg-gray-400 rounded-full w-full"></div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
                >
                    <p
                        class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1"
                    >
                        Available
                    </p>
                    <p class="text-3xl font-bold text-emerald-600">
                        {{ stats.available }}
                    </p>
                    <div class="mt-3 h-1 bg-emerald-100 rounded-full">
                        <div
                            class="h-1 bg-emerald-500 rounded-full"
                            :style="`width:${stats.total_assets ? Math.round((stats.available / stats.total_assets) * 100) : 0}%`"
                        />
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
                >
                    <p
                        class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1"
                    >
                        Assigned
                    </p>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ stats.assigned }}
                    </p>
                    <div class="mt-3 h-1 bg-blue-100 rounded-full">
                        <div
                            class="h-1 bg-blue-500 rounded-full"
                            :style="`width:${stats.total_assets ? Math.round((stats.assigned / stats.total_assets) * 100) : 0}%`"
                        />
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
                >
                    <p
                        class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1"
                    >
                        Maintenance
                    </p>
                    <p class="text-3xl font-bold text-amber-600">
                        {{ stats.under_maintenance }}
                    </p>
                    <div class="mt-3 h-1 bg-amber-100 rounded-full">
                        <div
                            class="h-1 bg-amber-500 rounded-full"
                            :style="`width:${stats.total_assets ? Math.round((stats.under_maintenance / stats.total_assets) * 100) : 0}%`"
                        />
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
                >
                    <p
                        class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1"
                    >
                        Pending
                    </p>
                    <p class="text-3xl font-bold text-rose-600">
                        {{ stats.pending_requests }}
                    </p>
                    <div class="mt-3 h-1 bg-rose-100 rounded-full">
                        <div class="h-1 bg-rose-500 rounded-full w-full" />
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl border border-gray-100 shadow-sm p-5"
                >
                    <p
                        class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1"
                    >
                        Employees
                    </p>
                    <p class="text-3xl font-bold text-violet-600">
                        {{ stats.total_users }}
                    </p>
                    <div class="mt-3 h-1 bg-violet-100 rounded-full">
                        <div class="h-1 bg-violet-500 rounded-full w-full" />
                    </div>
                </div>
            </div>

            <!-- ── Charts row — bar (assets/category) + doughnut (status) ── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <AssetBarChart
                        :chart-data="assetsByCategory"
                        title="Assets by Category"
                    />
                </div>
                <AssetDoughnutChart
                    :chart-data="assetStatusBreakdown"
                    title="Asset Status"
                />
            </div>

            <!-- Pending requests + request trend line chart -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Pending requests — 2 cols -->
                <div
                    class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
                >
                    <div
                        class="flex items-center justify-between px-6 py-4 border-b border-gray-100"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"
                            ></span>
                            <h2 class="font-semibold text-gray-900">
                                Pending Requests
                            </h2>
                            <span
                                class="bg-rose-100 text-rose-700 text-xs px-2 py-0.5 rounded-full font-medium"
                            >
                                {{ stats.pending_requests }}
                            </span>
                        </div>
                        <Link
                            :href="route('asset-requests.index')"
                            class="text-xs text-red-600 hover:underline"
                        >
                            View all
                        </Link>
                    </div>

                    <div
                        v-if="pendingRequests?.length > 0"
                        class="divide-y divide-gray-50"
                    >
                        <div
                            v-for="req in pendingRequests"
                            :key="req.id"
                            class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors"
                        >
                            <img
                                :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(req.user?.name ?? 'U')}&color=ffffff&background=DC143C&size=32`"
                                class="w-8 h-8 rounded-full shrink-0"
                            />
                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-sm font-medium text-gray-900 truncate"
                                >
                                    {{ req.user?.name }}
                                </p>
                                <p class="text-xs text-gray-500 truncate">
                                    Requested:
                                    <span class="font-medium text-gray-700">{{
                                        req.asset?.model_name
                                    }}</span>
                                </p>
                                <p
                                    v-if="req.reason"
                                    class="text-xs text-gray-400 italic truncate"
                                >
                                    {{ req.reason }}
                                </p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <button
                                    @click="confirmApproval(req)"
                                    class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg transition-colors font-medium"
                                >
                                    Approve
                                </button>
                                <button
                                    @click="confirmRejection(req)"
                                    class="text-xs bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 px-3 py-1.5 rounded-lg transition-colors font-medium"
                                >
                                    Reject
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="px-6 py-12 text-center">
                        <fa-icon
                            icon="circle-check"
                            class="w-10 h-10 text-emerald-200 mb-3"
                        />
                        <p class="text-sm text-gray-500">No pending requests</p>
                    </div>
                </div>

                <!-- Request trend chart — 1 col -->
                <RequestTrendChart
                    :chart-data="requestTrend"
                    title="Requests — last 6 months"
                />
            </div>

            <!-- Recent assignments table -->
            <div
                class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
            >
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-100"
                >
                    <h2 class="font-semibold text-gray-900">
                        Recent Assignments
                    </h2>
                    <Link
                        :href="route('asset-assignments.index')"
                        class="text-xs text-red-600 hover:underline"
                    >
                        View all
                    </Link>
                </div>

                <div
                    v-if="recentAssignments?.length > 0"
                    class="overflow-x-auto"
                >
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                <th
                                    class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase"
                                >
                                    Asset
                                </th>
                                <th
                                    class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase"
                                >
                                    Employee
                                </th>
                                <th
                                    class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase"
                                >
                                    Assigned
                                </th>
                                <th
                                    class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase"
                                >
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="a in recentAssignments"
                                :key="a.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <td class="px-6 py-3 font-medium text-gray-900">
                                    {{ a.asset?.model_name }}
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <img
                                            :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(a.user?.name ?? 'U')}&color=ffffff&background=DC143C&size=28`"
                                            class="w-7 h-7 rounded-full"
                                        />
                                        <span class="text-gray-700">{{
                                            a.user?.name
                                        }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-gray-500 text-xs">
                                    {{ fmt(a.assigned_date) }}
                                </td>
                                <td class="px-6 py-3">
                                    <span
                                        :class="[
                                            'text-xs px-2 py-1 rounded-full font-medium',
                                            assignmentStatusColors[a.status] ??
                                                'bg-gray-100 text-gray-600',
                                        ]"
                                    >
                                        {{
                                            a.status.charAt(0).toUpperCase() +
                                            a.status.slice(1)
                                        }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-6 py-12 text-center">
                    <p class="text-sm text-gray-400">No assignments yet</p>
                </div>
            </div>

            <!-- Quick links -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <Link
                    :href="route('home')"
                    class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 hover:shadow-md transition-all group"
                >
                    <fa-icon icon="tags" class="text-2xl text-rose-300 mb-3" />
                    <p
                        class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors"
                    >
                        All Assets
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        View full inventory
                    </p>
                </Link>
                <Link
                    :href="route('asset-requests.index')"
                    class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 hover:shadow-md transition-all group"
                >
                    <fa-icon
                        icon="clipboard-list"
                        class="text-2xl text-orange-400 mb-3"
                    />
                    <p
                        class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors"
                    >
                        Requests
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Manage all requests
                    </p>
                </Link>
                <Link
                    :href="route('asset-assignments.index')"
                    class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 hover:shadow-md transition-all group"
                >
                    <fa-icon icon="link" class="text-2xl text-green-500 mb-3" />
                    <p
                        class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors"
                    >
                        Assignments
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Track who has what
                    </p>
                </Link>
                <Link
                    :href="route('users.index')"
                    class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 hover:shadow-md transition-all group"
                >
                    <fa-icon icon="users" class="text-2xl text-blue-500 mb-3" />
                    <p
                        class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors"
                    >
                        Users
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Manage employees</p>
                </Link>
            </div>
        </div>

        <!-- Modals -->
        <ConfirmActionModal
            :show="showApproveModal"
            actionType="approve"
            title="Approve Request"
            subtitle="Confirming this approval."
            confirmText="Confirm Approval"
            @close="showApproveModal = false"
            @confirm="approveRequest(selectedRequest.id)"
        >
            <p v-if="selectedRequest">
                Approve the request for
                <span class="font-semibold">{{
                    selectedRequest.asset?.model_name
                }}</span
                >? This will assign the asset to
                <span class="font-semibold text-gray-900">{{
                    selectedRequest.user?.name
                }}</span
                >.
            </p>
        </ConfirmActionModal>

        <ConfirmActionModal
            :show="showRejectionModal"
            actionType="reject"
            title="Reject Request"
            subtitle="Confirming this rejection."
            confirmText="Confirm Rejection"
            @close="showRejectionModal = false"
            @confirm="rejectRequest(selectedRequest.id)"
        >
            <p v-if="selectedRequest">
                Reject the request for
                <span class="font-semibold">{{
                    selectedRequest.asset?.model_name
                }}</span>
                from
                <span class="font-semibold text-gray-900">{{
                    selectedRequest.user?.name
                }}</span
                >?
            </p>
        </ConfirmActionModal>
    </AuthenticatedLayout>
</template>
