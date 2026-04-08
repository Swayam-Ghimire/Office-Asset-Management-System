<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ConfirmActionModal from "@/Components/Modals/ConfirmActionModal.vue";
import CategoryIcon from "@/Components/UI/CategoryIcon.vue";
import { usePage } from "@inertiajs/vue3";


const page = usePage();
const isAdmin = page.props.isAdmin
const props = defineProps({
    requests: Object, // paginated
    filters: Object,
});


const selectedStatus = ref(props.filters?.status ?? "");

// Approve asset modal
const showApproveModal = ref(false);

// Reject asset modal
const showRejectionModal = ref(false);

// Hold current request

const selectedRequest = ref(null);
function applyFilter() {
    router.get(
        route("asset-requests.index"),
        { status: selectedStatus.value },
        {
            preserveScroll: true,
            replace: true,
        },
    );
}

function resetFilter() {
    selectedStatus.value = "";
    router.get(route("asset-requests.index"));
}

function confirmApproval(request) {
    showApproveModal.value = true;
    selectedRequest.value = request;
}

function confirmRejection(request) {
    showRejectionModal.value = true;
    selectedRequest.value = request;
}

function approve(id) {
    router.put(
        route("asset-requests.approve", id),
        {},
        { preserveScroll: true },
    );
    selectedRequest.value = null;
    showApproveModal.value = false;
}

function reject(id) {
    router.put(
        route("asset-requests.reject", id),
        {},
        { preserveScroll: true },
    );
    selectedRequest.value = null;
    showRejectionModal.value = false;
}

const statusStyle = {
    pending: {
        pill: "bg-amber-100 text-amber-700 border border-amber-200",
        dot: "bg-amber-400",
    },
    approved: {
        pill: "bg-emerald-100 text-emerald-700 border border-emerald-200",
        dot: "bg-emerald-400",
    },
    rejected: {
        pill: "bg-rose-100 text-rose-700 border border-rose-200",
        dot: "bg-rose-400",
    },
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
    <Head title="Asset Requests" />

    <AuthenticatedLayout page-title="Asset Request">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                        {{ isAdmin ? "All Asset Requests" : "My Requests" }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{
                            isAdmin
                                ? "Review and manage employee asset requests"
                                : "Track the status of your asset requests"
                        }}
                    </p>
                </div>
                <Link
                    v-if="!isAdmin"
                    :href="route('asset-requests.create')"
                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm"
                >
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    New Request
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
                <!-- Filter bar -->
                <div
                    class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex flex-wrap items-end gap-3"
                >
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-500 mb-1"
                            >Filter by Status</label
                        >
                        <div class="flex gap-2">
                            <button
                                v-for="opt in [
                                    '',
                                    'pending',
                                    'approved',
                                    'rejected',
                                ]"
                                :key="opt"
                                @click="
                                    selectedStatus = opt;
                                    applyFilter();
                                "
                                :class="[
                                    'px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors',
                                    selectedStatus === opt
                                        ? 'bg-red-600 text-white border-red-600'
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300',
                                ]"
                            >
                                {{
                                    opt === ""
                                        ? "All"
                                        : opt.charAt(0).toUpperCase() +
                                          opt.slice(1)
                                }}
                            </button>
                        </div>
                    </div>
                    <div v-if="selectedStatus" class="ml-auto">
                        <button
                            @click="resetFilter"
                            class="text-xs text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            Clear filter ✕
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div
                    class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
                >
                    <div
                        v-if="requests.data.length > 0"
                        class="overflow-x-auto"
                    >
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50">
                                    <th
                                        class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                    >
                                        Asset
                                    </th>
                                    <th
                                        v-if="isAdmin"
                                        class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                    >
                                        Requested By
                                    </th>
                                    <th
                                        class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                    >
                                        Date
                                    </th>
                                    <th
                                        class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                    >
                                        Status
                                    </th>
                                    <th
                                        v-if="isAdmin"
                                        class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide"
                                    >
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr
                                    v-for="req in requests.data"
                                    :key="req.id"
                                    class="hover:bg-gray-50 transition-colors"
                                >
                                    <!-- Asset -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-lg shrink-0 text-rose-400"
                                            >
                                                <CategoryIcon
                                                    :name="
                                                        req.asset?.category
                                                            ?.name
                                                    "
                                                    size="sm"
                                                />
                                            </div>
                                            <div>
                                                <p
                                                    class="font-semibold text-gray-900"
                                                >
                                                    {{ req.asset?.model_name }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-400"
                                                >
                                                    {{
                                                        req.asset?.category
                                                            ?.name ??
                                                        "Uncategorized"
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Requested by (admin only) -->
                                    <td v-if="isAdmin" class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <img
                                                :src="
                                                    '/storage/' +
                                                        req.user?.img_path ??
                                                    `https://ui-avatars.com/api/?name=${encodeURIComponent(req.user.name)}&color=7F9CF5&background=EBF4FF`
                                                "
                                                :alt="req.user.name"
                                                class="h-10 w-10 rounded-full object-cover border border-gray-200"
                                            />
                                            <div>
                                                <p
                                                    class="text-sm font-medium text-gray-900"
                                                >
                                                    {{ req.user?.name }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-400"
                                                >
                                                    {{ req.user?.email }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Date -->
                                    <td
                                        class="px-6 py-4 text-gray-500 text-xs whitespace-nowrap"
                                    >
                                        {{ fmt(req.requested_at) }}
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">
                                        <span
                                            :class="[
                                                'inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full font-medium',
                                                statusStyle[req.status]?.pill ??
                                                    'bg-gray-100 text-gray-600',
                                            ]"
                                        >
                                            <span
                                                :class="[
                                                    'w-1.5 h-1.5 rounded-full',
                                                    statusStyle[req.status]
                                                        ?.dot ?? 'bg-gray-400',
                                                ]"
                                            ></span>
                                            {{
                                                req.status
                                                    .charAt(0)
                                                    .toUpperCase() +
                                                req.status.slice(1)
                                            }}
                                        </span>
                                    </td>

                                    <!-- Admin actions -->
                                    <td
                                        v-if="isAdmin"
                                        class="px-6 py-4 text-right"
                                    >
                                        <div
                                            v-if="req.status === 'pending'"
                                            class="flex justify-end gap-2"
                                        >
                                            <button
                                                @click="confirmApproval(req)"
                                                class="inline-flex items-center gap-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg transition-colors font-medium"
                                            >
                                                <svg
                                                    class="w-3.5 h-3.5"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M5 13l4 4L19 7"
                                                    />
                                                </svg>
                                                Approve
                                            </button>
                                            <button
                                                @click="confirmRejection(req)"
                                                class="inline-flex items-center gap-1 text-xs border border-rose-200 text-rose-600 hover:bg-rose-50 px-3 py-1.5 rounded-lg transition-colors font-medium"
                                            >
                                                <svg
                                                    class="w-3.5 h-3.5"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"
                                                    />
                                                </svg>
                                                Reject
                                            </button>
                                        </div>
                                        <span
                                            v-else
                                            class="text-xs text-gray-400 italic"
                                        >
                                            {{
                                                req.status === "approved"
                                                    ? "Approved"
                                                    : "Rejected"
                                            }}
                                            <span
                                                v-if="req.approved_at"
                                                class="block text-gray-300"
                                                >{{
                                                    fmt(req.approved_at)
                                                }}</span
                                            >
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="px-6 py-16 text-center">
                        <div class="text-5xl mb-4 text-zinc-500">
                            <fa-icon icon="clipboard-list" />
                        </div>
                        <p class="text-sm font-semibold text-gray-600 mb-1">
                            No requests found
                        </p>
                        <p class="text-xs text-gray-400 mb-4">
                            {{
                                selectedStatus
                                    ? "Try a different status filter"
                                    : isAdmin
                                      ? "No requests have been submitted yet"
                                      : "You haven't made any requests yet"
                            }}
                        </p>
                        <Link
                            v-if="!isAdmin"
                            :href="route('asset-requests.create')"
                            class="inline-flex items-center gap-1.5 text-sm text-red-600 font-medium hover:underline"
                        >
                            Make your first request →
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="requests.last_page > 1"
                    class="flex justify-center gap-1"
                >
                    <template v-for="link in requests.links" :key="link.label">
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
        </div>
        <!-- Modal -->
        <ConfirmActionModal
            :show="showApproveModal"
            actionType="approve"
            title="Approve Request"
            subtitle="Confirming this approval."
            confirmText="Confirm Approval"
            @close="showApproveModal = false"
            @confirm="approve(selectedRequest.id)"
        >
            <p v-if="selectedRequest">
                Are you sure you want to approve the request for
                <span class="font-semibold">{{
                    selectedRequest.asset?.model_name ??
                    selectedRequest.asset?.name
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
            @confirm="reject(selectedRequest.id)"
        >
            <p v-if="selectedRequest">
                Are you sure you want to reject the request for
                <span class="font-semibold">{{
                    selectedRequest.asset?.model_name
                }}</span
                >? This will deny the asset to
                <span class="font-semibold text-gray-900">{{
                    selectedRequest.user?.name
                }}</span
                >.
            </p>
        </ConfirmActionModal>
    </AuthenticatedLayout>
</template>
