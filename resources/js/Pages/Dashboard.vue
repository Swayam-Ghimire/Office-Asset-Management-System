<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    stats: Object,
    // Admin-only props
    pendingRequests: Array,
    recentAssignments: Array,
    assetsByCategory: Object,
});

function approveRequest(id) {
    router.put(route('asset-requests.approve', id), {}, { preserveScroll: true });
}

function rejectRequest(id) {
    router.put(route('asset-requests.reject', id), {}, { preserveScroll: true });
}

const statusColors = {
    pending:  'bg-amber-100 text-amber-700',
    approved: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-rose-100 text-rose-700',
    assigned: 'bg-blue-100 text-blue-700',
    returned: 'bg-gray-100 text-gray-600',
};

// Simple bar chart ratios for assets by category
const categoryData = computed(() => {
    if (!props.assetsByCategory) return [];
    const entries = Object.entries(props.assetsByCategory);
    const max = Math.max(...entries.map(([, v]) => v), 1);
    return entries.map(([name, count]) => ({ name, count, pct: Math.round((count / max) * 100) }));
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                        {{ $page.props.isAdmin ? 'Admin Dashboard' : 'My Dashboard' }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $page.props.isAdmin ? 'Manage assets, requests and users' : 'Track your assets and requests' }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link v-if="$page.props.isAdmin" :href="route('assets.create')" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Asset
                    </Link>

                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- ─── ADMIN STATS ─── -->
                <template v-if="$page.props.isAdmin">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 col-span-1">
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Total Assets</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.total_assets }}</p>
                            <div class="mt-3 h-1 bg-gray-100 rounded-full"><div class="h-1 bg-gray-400 rounded-full w-full"></div></div>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 col-span-1">
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Available</p>
                            <p class="text-3xl font-bold text-emerald-600">{{ stats.available }}</p>
                            <div class="mt-3 h-1 bg-emerald-100 rounded-full"><div class="h-1 bg-emerald-500 rounded-full" :style="`width:${stats.total_assets ? Math.round(stats.available/stats.total_assets*100) : 0}%`"></div></div>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 col-span-1">
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Assigned</p>
                            <p class="text-3xl font-bold text-blue-600">{{ stats.assigned }}</p>
                            <div class="mt-3 h-1 bg-blue-100 rounded-full"><div class="h-1 bg-blue-500 rounded-full" :style="`width:${stats.total_assets ? Math.round(stats.assigned/stats.total_assets*100) : 0}%`"></div></div>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 col-span-1">
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Maintenance</p>
                            <p class="text-3xl font-bold text-amber-600">{{ stats.maintenance }}</p>
                            <div class="mt-3 h-1 bg-amber-100 rounded-full"><div class="h-1 bg-amber-500 rounded-full" :style="`width:${stats.total_assets ? Math.round(stats.maintenance/stats.total_assets*100) : 0}%`"></div></div>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 col-span-1">
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Pending</p>
                            <p class="text-3xl font-bold text-rose-600">{{ stats.pending_requests }}</p>
                            <div class="mt-3 h-1 bg-rose-100 rounded-full"><div class="h-1 bg-rose-500 rounded-full w-full"></div></div>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 col-span-1">
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Employees</p>
                            <p class="text-3xl font-bold text-violet-600">{{ stats.total_users }}</p>
                            <div class="mt-3 h-1 bg-violet-100 rounded-full"><div class="h-1 bg-violet-500 rounded-full w-full"></div></div>
                        </div>
                    </div>

                    <!-- Admin Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        <!-- Pending Requests (spans 2 cols) -->
                        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                    <h2 class="font-semibold text-gray-900">Pending Requests</h2>
                                    <span class="ml-1 bg-rose-100 text-rose-700 text-xs px-2 py-0.5 rounded-full font-medium">{{ stats.pending_requests }}</span>
                                </div>
                                <Link :href="route('asset-requests.index')" class="text-xs text-red-600 hover:underline">View all</Link>
                            </div>

                            <div v-if="pendingRequests?.length > 0" class="divide-y divide-gray-50">
                                <div v-for="req in pendingRequests" :key="req.id" class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-500 shrink-0">
                                        {{ req.user?.name?.charAt(0)?.toUpperCase() }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ req.user?.name }}</p>
                                        <p class="text-xs text-gray-500 truncate">
                                            Requested: <span class="font-medium text-gray-700">{{ req.asset?.model_name }}</span>
                                            <span class="ml-1 text-gray-400">{{ req.asset?.reason }}</span>
                                        </p>
                                    </div>
                                    <div class="flex gap-2 shrink-0">
                                        <button
                                            @click="approveRequest(req.id)"
                                            class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg transition-colors font-medium"
                                        >
                                            Approve
                                        </button>
                                        <button
                                            @click="rejectRequest(req.id)"
                                            class="text-xs bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 px-3 py-1.5 rounded-lg transition-colors font-medium"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="px-6 py-12 text-center">
                                <div class="text-4xl mb-3">🎉</div>
                                <p class="text-sm text-gray-500">No pending requests</p>
                            </div>
                        </div>

                        <!-- Category Chart -->
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100">
                                <h2 class="font-semibold text-gray-900">Assets by Category</h2>
                            </div>
                            <div class="p-6 space-y-4">
                                <div v-if="categoryData.length > 0" v-for="item in categoryData" :key="item.name" class="space-y-1">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-600 font-medium truncate">{{ item.name }}</span>
                                        <span class="text-gray-400 shrink-0 ml-2">{{ item.count }}</span>
                                    </div>
                                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div
                                            class="h-2 bg-red-500 rounded-full transition-all duration-700"
                                            :style="`width: ${item.pct}%`"
                                        ></div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-6 text-gray-400 text-sm">No data</div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Assignments -->
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <h2 class="font-semibold text-gray-900">Recent Assignments</h2>
                            <Link :href="route('asset-assignments.index')" class="text-xs text-red-600 hover:underline">View all</Link>
                        </div>

                        <div v-if="recentAssignments?.length > 0" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 bg-gray-50">
                                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Asset</th>
                                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Employee</th>
                                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Assigned</th>
                                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wide">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <tr v-for="a in recentAssignments" :key="a.id" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-3">
                                            <p class="font-medium text-gray-900">{{ a.asset?.model_name }}</p>
                                        </td>
                                        <td class="px-6 py-3 text-gray-700">{{ a.user?.name }}</td>
                                        <td class="px-6 py-3 text-gray-500 text-xs">{{ new Date(a.assigned_date).toLocaleDateString() }}</td>
                                        <td class="px-6 py-3">
                                            <span :class="['text-xs px-2 py-1 rounded-full font-medium', statusColors[a.status]]">
                                                {{ a.status.charAt(0).toUpperCase() + a.status.slice(1) }}
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

                    <!-- Quick Links -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link :href="route('home')" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 hover:shadow-md transition-all group">
                            <div class="text-3xl mb-3">📦</div>
                            <p class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors">All Assets</p>
                            <p class="text-xs text-gray-400 mt-0.5">View full inventory</p>
                        </Link>
                        <Link :href="route('asset-requests.index')" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 hover:shadow-md transition-all group">
                            <div class="text-3xl mb-3">📋</div>
                            <p class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors">Requests</p>
                            <p class="text-xs text-gray-400 mt-0.5">Manage all requests</p>
                        </Link>
                        <Link :href="route('asset-assignments.index')" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 hover:shadow-md transition-all group">
                            <div class="text-3xl mb-3">🔗</div>
                            <p class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors">Assignments</p>
                            <p class="text-xs text-gray-400 mt-0.5">Track who has what</p>
                        </Link>
                        <Link :href="route('users.index')" class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 hover:shadow-md transition-all group">
                            <div class="text-3xl mb-3">👥</div>
                            <p class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors">Users</p>
                            <p class="text-xs text-gray-400 mt-0.5">Manage employees</p>
                        </Link>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>