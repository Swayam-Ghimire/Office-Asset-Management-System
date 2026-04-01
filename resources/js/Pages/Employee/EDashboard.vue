<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    stats: Object,
    myAssignments: Array,
    myRequests: Array,
});

// Return asset modal
const showReturnModal = ref(false);
const selectedAssignment = ref(null);

function confirmReturn(assignment) {
    selectedAssignment.value = assignment;
    showReturnModal.value = true;
}

function submitReturn() {
    router.post(route('asset-assignments.return', selectedAssignment.value.id), {}, {
        onSuccess: () => { showReturnModal.value = false; selectedAssignment.value = null; },
    });
}

// Report issue modal
const showIssueModal = ref(false);
const selectedAssetForIssue = ref(null);
const issueDescription = ref('');
const issueError = ref('');

function openIssueModal(assignment) {
    selectedAssetForIssue.value = assignment;
    issueDescription.value = '';
    issueError.value = '';
    showIssueModal.value = true;
}

function submitIssue() {
    if (!issueDescription.value.trim()) {
        issueError.value = 'Please describe the issue.';
        return;
    }
    router.post(route('maintenance.store'), {
        asset_id: selectedAssetForIssue.value.asset_id,
        description: issueDescription.value,
    }, {
        onSuccess: () => { showIssueModal.value = false; issueDescription.value = ''; },
        onError: (errors) => { issueError.value = errors.description ?? 'Something went wrong.'; },
    });
}

const statusColors = {
    pending:  'bg-amber-100 text-amber-700 border border-amber-200',
    approved: 'bg-emerald-100 text-emerald-700 border border-emerald-200',
    rejected: 'bg-rose-100 text-rose-700 border border-rose-200',
    assigned: 'bg-blue-100 text-blue-700 border border-blue-200',
    returned: 'bg-gray-100 text-gray-600 border border-gray-200',
};

const categoryIcon = (name) => {
    if (!name) return '📦';
    const n = name.toLowerCase();
    if (n.includes('laptop'))   return '💻';
    if (n.includes('monitor'))  return '🖥️';
    if (n.includes('mouse'))    return '🖱️';
    if (n.includes('keyboard')) return '⌨️';
    if (n.includes('chair'))    return '🪑';
    if (n.includes('phone'))    return '📱';
    if (n.includes('tablet'))   return '📟';
    return '📦';
};
</script>

<template>
    <Head title="My Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">My Dashboard</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Track your assigned assets and requests</p>
                </div>
                <Link
                    :href="route('asset-requests.create')"
                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Request Asset
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Stats Row -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center text-lg">🔗</div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">My Assets</p>
                        </div>
                        <p class="text-3xl font-bold text-blue-600">{{ stats.my_assets }}</p>
                        <p class="text-xs text-gray-400 mt-1">Currently held</p>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center text-lg">⏳</div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Pending</p>
                        </div>
                        <p class="text-3xl font-bold text-amber-600">{{ stats.pending_requests }}</p>
                        <p class="text-xs text-gray-400 mt-1">Awaiting approval</p>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center text-lg">✅</div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Approved</p>
                        </div>
                        <p class="text-3xl font-bold text-emerald-600">{{ stats.approved_requests }}</p>
                        <p class="text-xs text-gray-400 mt-1">Total approved</p>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-50 flex items-center justify-center text-lg">↩️</div>
                            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Returned</p>
                        </div>
                        <p class="text-3xl font-bold text-gray-600">{{ stats.returned_assets }}</p>
                        <p class="text-xs text-gray-400 mt-1">Previously returned</p>
                    </div>
                </div>

                <!-- Main Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                    <!-- My Assets (3 cols) -->
                    <div class="lg:col-span-3 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <h2 class="font-semibold text-gray-900">My Assigned Assets</h2>
                                <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-medium">{{ stats.my_assets }}</span>
                            </div>
                            <Link :href="route('asset-assignments.index')" class="text-xs text-red-600 hover:underline font-medium">View all →</Link>
                        </div>

                        <div v-if="myAssignments?.length > 0" class="divide-y divide-gray-50">
                            <div
                                v-for="a in myAssignments"
                                :key="a.id"
                                class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors"
                            >
                                <!-- Icon -->
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-100 flex items-center justify-center text-2xl shrink-0">
                                    {{ categoryIcon(a.asset?.category?.name) }}
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 text-sm truncate">{{ a.asset?.model_name ?? a.asset?.name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        <span class="font-medium text-gray-500">{{ a.asset?.category?.name ?? 'Uncategorized' }}</span>
                                        <span class="mx-1.5 text-gray-300">·</span>
                                        Since {{ new Date(a.assigned_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                                    </p>
                                    <p v-if="a.asset?.brand" class="text-xs text-gray-400">{{ a.asset.brand }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2 shrink-0">
                                    <!-- Report issue -->
                                    <button
                                        @click="openIssueModal(a)"
                                        class="inline-flex items-center gap-1 text-xs border border-amber-200 text-amber-600 hover:bg-amber-50 px-2.5 py-1.5 rounded-lg transition-colors"
                                        title="Report an issue"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                        </svg>
                                        Issue
                                    </button>

                                    <!-- Return -->
                                    <button
                                        @click="confirmReturn(a)"
                                        class="inline-flex items-center gap-1 text-xs border border-gray-200 text-gray-600 hover:border-rose-300 hover:text-rose-600 hover:bg-rose-50 px-2.5 py-1.5 rounded-lg transition-colors"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        Return
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="px-6 py-16 text-center">
                            <div class="text-5xl mb-4">📭</div>
                            <p class="text-sm font-medium text-gray-600 mb-1">No assets assigned yet</p>
                            <p class="text-xs text-gray-400 mb-4">Browse the inventory and make a request</p>
                            <Link
                                :href="route('home')"
                                class="inline-flex items-center gap-1.5 text-sm text-red-600 font-medium hover:underline"
                            >
                                Browse assets →
                            </Link>
                        </div>
                    </div>

                    <!-- My Requests (2 cols) -->
                    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                            <h2 class="font-semibold text-gray-900">Recent Requests</h2>
                            <Link :href="route('asset-requests.index')" class="text-xs text-red-600 hover:underline font-medium">View all →</Link>
                        </div>

                        <div v-if="myRequests?.length > 0" class="divide-y divide-gray-50">
                            <div
                                v-for="req in myRequests"
                                :key="req.id"
                                class="px-5 py-3.5 flex items-center gap-3 hover:bg-gray-50 transition-colors"
                            >
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-lg shrink-0">
                                    {{ categoryIcon(req.asset?.category?.name) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ req.asset?.model_name ?? req.asset?.name }}</p>
                                    <p class="text-xs text-gray-400">{{ new Date(req.requested_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}</p>
                                </div>
                                <span :class="['text-xs px-2 py-1 rounded-full font-medium shrink-0', statusColors[req.status]]">
                                    {{ req.status.charAt(0).toUpperCase() + req.status.slice(1) }}
                                </span>
                            </div>
                        </div>

                        <div v-else class="px-6 py-16 text-center">
                            <div class="text-4xl mb-3">📋</div>
                            <p class="text-sm font-medium text-gray-600 mb-1">No requests yet</p>
                            <Link :href="route('asset-requests.create')" class="text-xs text-red-600 hover:underline">Make your first request →</Link>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Quick Actions</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link
                            :href="route('home')"
                            class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 hover:shadow-md transition-all group flex flex-col items-start"
                        >
                            <div class="text-2xl mb-2">📦</div>
                            <p class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors">Browse Assets</p>
                            <p class="text-xs text-gray-400 mt-0.5">Find available items</p>
                        </Link>

                        <Link
                            :href="route('asset-requests.create')"
                            class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 hover:shadow-md transition-all group flex flex-col items-start"
                        >
                            <div class="text-2xl mb-2">➕</div>
                            <p class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors">New Request</p>
                            <p class="text-xs text-gray-400 mt-0.5">Submit an asset request</p>
                        </Link>

                        <Link
                            :href="route('asset-requests.index')"
                            class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 hover:shadow-md transition-all group flex flex-col items-start"
                        >
                            <div class="text-2xl mb-2">📋</div>
                            <p class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors">My Requests</p>
                            <p class="text-xs text-gray-400 mt-0.5">View all request history</p>
                        </Link>

                        <Link
                            :href="route('asset-assignments.index')"
                            class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:border-red-200 hover:shadow-md transition-all group flex flex-col items-start"
                        >
                            <div class="text-2xl mb-2">🗂️</div>
                            <p class="font-semibold text-gray-900 text-sm group-hover:text-red-600 transition-colors">Assignment History</p>
                            <p class="text-xs text-gray-400 mt-0.5">Past & current assets</p>
                        </Link>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── Return Confirmation Modal ── -->
        <Modal :show="showReturnModal" @close="showReturnModal = false">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Return Asset</h3>
                        <p class="text-sm text-gray-500">This action cannot be undone.</p>
                    </div>
                </div>

                <p class="text-sm text-gray-700 mb-6">
                    Are you sure you want to return
                    <span class="font-semibold">{{ selectedAssignment?.asset?.model_name ?? selectedAssignment?.asset?.name }}</span>?
                    It will become available for others to request.
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        @click="showReturnModal = false"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitReturn"
                        class="px-4 py-2 text-sm bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition-colors font-medium"
                    >
                        Yes, Return It
                    </button>
                </div>
            </div>
        </Modal>

        <!-- ── Report Issue Modal ── -->
        <Modal :show="showIssueModal" @close="showIssueModal = false">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Report an Issue</h3>
                        <p class="text-sm text-gray-500">
                            {{ selectedAssetForIssue?.asset?.model_name ?? selectedAssetForIssue?.asset?.name }}
                        </p>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Describe the issue <span class="text-rose-500">*</span></label>
                    <textarea
                        v-model="issueDescription"
                        rows="4"
                        placeholder="e.g. The screen flickers when opened, the keyboard has a stuck key..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none resize-none"
                    ></textarea>
                    <p v-if="issueError" class="text-xs text-rose-600 mt-1">{{ issueError }}</p>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="showIssueModal = false"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitIssue"
                        class="px-4 py-2 text-sm bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors font-medium"
                    >
                        Submit Report
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>