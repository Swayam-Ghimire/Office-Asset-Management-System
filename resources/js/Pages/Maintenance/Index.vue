<script setup>
import { ref } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ResolveMaintenanceModal from "@/Components/Modals/ResolveMaintenanceModal.vue";
import MarkInProgressModal from "@/Components/Modals/MarkInProgressModal.vue";
import Modal from "@/Components/Modal.vue";
import CategoryIcon from "@/Components/UI/CategoryIcon.vue";

const props = defineProps({
    maintenances: Object, // paginated
    filters: Object,      // current filter values
});

const page    = usePage();
const isAdmin = page.props.isAdmin;

// ── Filter ────────────────────────────────────────────────────
const selectedStatus = ref(props.filters?.status ?? "");
function applyFilter() {
    router.get(
        route("maintenance.index"),
        { status: selectedStatus.value },
        { preserveScroll: true, replace: true },
    );
}

// ── Shared selected record ────────────────────────────────────
const selectedRecord = ref(null);

// ── Resolve modal (admin only) ────────────────────────────────
const showResolveModal = ref(false);
function openResolve(record) {
    selectedRecord.value = record;
    showResolveModal.value = true;
}

// ── Mark in-progress modal (admin only) ──────────────────────
const showInProgressModal = ref(false);
function openInProgress(record) {
    selectedRecord.value = record;
    showInProgressModal.value = true;
}

// ── Request-return modal (admin only) ────────────────────────
const showReturnModal = ref(false);
const returnForm = useForm({ reason: "" });

function openRequestReturn(record) {
    selectedRecord.value = record;
    returnForm.reason    = "";
    showReturnModal.value = true;
}

function submitRequestReturn() {
    returnForm.post(route("maintenance.request-return", selectedRecord.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showReturnModal.value = false;
            selectedRecord.value  = null;
            returnForm.reset();
        },
    });
}

// ── Helpers ───────────────────────────────────────────────────
/**
 * Returns true if the reporter still has an active assignment for this asset.
 * The controller loads reporter.assignments so we can check client-side
 * before showing the button — avoids an extra request.
 */
const canRequestReturn = (record) => {
    if (!record.reporter?.assignments) return false;
    return record.reporter.assignments.some(
        (a) => a.asset_id === record.asset_id && a.status === "assigned",
    );
};

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
        pill: "bg-amber-100 text-amber-700 border-amber-200",
        dot:  "bg-amber-400",
    },
    in_progress: {
        pill: "bg-blue-100 text-blue-700 border-blue-200",
        dot:  "bg-blue-400",
    },
    resolved: {
        pill: "bg-emerald-100 text-emerald-700 border-emerald-200",
        dot:  "bg-emerald-400",
    },
};
</script>

<template>
    <Head title="Maintenance" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Maintenance</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Asset issues reported by employees
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-6xl mx-auto space-y-5">

            <!-- Filter bar -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex gap-2 flex-wrap">
                <button
                    v-for="opt in [
                        { value: '',            label: 'All' },
                        { value: 'reported',    label: 'Reported' },
                        { value: 'in_progress', label: 'In Progress' },
                        { value: 'resolved',    label: 'Resolved' },
                    ]"
                    :key="opt.value"
                    @click="selectedStatus = opt.value; applyFilter()"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors',
                        selectedStatus === opt.value
                            ? 'bg-red-600 text-white border-red-600'
                            : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300',
                    ]"
                >
                    {{ opt.label }}
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div v-if="maintenances.data.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Asset</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Reported By</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Description</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Reported</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <!-- Action column always visible — each row decides what to show -->
                                <th v-if="isAdmin" class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="record in maintenances.data"
                                :key="record.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <!-- Asset -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500 shrink-0">
                                            <CategoryIcon :name="record.asset?.category?.name" size="sm" />
                                        </div>
                                        <div>
                                            <Link
                                                :href="route('assets.show', record.asset_id)"
                                                class="font-semibold text-gray-900 hover:text-red-600 transition-colors"
                                            >
                                                {{ record.asset?.model_name }}
                                            </Link>
                                            <p class="text-xs text-gray-400">
                                                {{ record.asset?.category?.name ?? "Uncategorized" }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Reporter -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <img
                                            v-if="record.reporter?.img_path"
                                            :src="`/storage/${record.reporter.img_path}`"
                                            :alt="record.reporter.name"
                                            class="h-8 w-8 rounded-full object-cover border border-gray-200"
                                        />
                                        <img
                                            v-else
                                            :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(record.reporter?.name ?? 'Unknown')}&color=7F9CF5&background=EBF4FF`"
                                            :alt="record.reporter?.name"
                                            class="h-8 w-8 rounded-full border border-gray-200"
                                        />
                                        <span class="text-sm text-gray-700">
                                            {{ record.reporter?.name ?? "Unknown" }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Description -->
                                <td class="px-6 py-4 text-gray-600 text-xs max-w-xs">
                                    <p class="line-clamp-2">{{ record.description }}</p>
                                </td>

                                <!-- Date -->
                                <td class="px-6 py-4 text-gray-500 text-xs whitespace-nowrap">
                                    {{ fmt(record.reported_at) }}
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full font-medium border',
                                        statusStyle[record.status]?.pill ?? 'bg-gray-100 text-gray-600 border-gray-200',
                                    ]">
                                        <span :class="['w-1.5 h-1.5 rounded-full', statusStyle[record.status]?.dot ?? 'bg-gray-400']"></span>
                                        {{ record.status === "in_progress" ? "In Progress"
                                         : record.status === "resolved"    ? "Resolved"
                                         : "Reported" }}
                                    </span>
                                </td>

                                <!-- Actions (admin only) -->
                                <td v-if="isAdmin" class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2 flex-wrap">

                                        <!-- Ask to return — only when asset is still assigned to the reporter -->
                                        <button
                                            v-if="record.status === 'reported' && canRequestReturn(record)"
                                            @click="openRequestReturn(record)"
                                            class="inline-flex items-center gap-1.5 text-xs border border-rose-200 text-rose-600 hover:bg-rose-50 px-2.5 py-1.5 rounded-lg transition-colors"
                                        >
                                            <fa-icon icon="rotate-left" class="w-3 h-3" />
                                            Ask to Return
                                        </button>

                                        <!-- Acknowledge (reported → in_progress) -->
                                        <button
                                            v-if="record.status === 'reported'"
                                            @click="openInProgress(record)"
                                            class="inline-flex items-center gap-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1.5 rounded-lg transition-colors"
                                        >
                                            <fa-icon icon="screwdriver-wrench" class="w-3 h-3" />
                                            Acknowledge
                                        </button>

                                        <!-- Resolve (in_progress → resolved) -->
                                        <button
                                            v-if="record.status === 'in_progress'"
                                            @click="openResolve(record)"
                                            class="inline-flex items-center gap-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1.5 rounded-lg transition-colors"
                                        >
                                            <fa-icon icon="circle-check" class="w-3 h-3" />
                                            Resolve
                                        </button>

                                        <!-- Done state -->
                                        <span
                                            v-if="record.status === 'resolved'"
                                            class="text-xs text-gray-400 italic px-2.5 py-1.5"
                                        >
                                            Resolved {{ fmt(record.resolved_at) }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty -->
                <div v-else class="px-6 py-16 text-center">
                    <fa-icon icon="screwdriver-wrench" class="w-10 h-10 text-gray-200 mb-3" />
                    <p class="text-sm font-semibold text-gray-500 mb-1">No maintenance records</p>
                    <p class="text-xs text-gray-400">
                        {{ selectedStatus ? "Try a different status filter." : "No issues have been reported yet." }}
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="maintenances.last_page > 1" class="flex justify-center gap-1">
                <template v-for="link in maintenances.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" v-html="link.label"
                        :class="['px-3 py-1.5 rounded-lg text-sm border transition-colors',
                                 link.active ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300']" />
                    <span v-else v-html="link.label"
                        class="px-3 py-1.5 rounded-lg text-sm border border-gray-100 bg-gray-50 text-gray-300" />
                </template>
            </div>
        </div>

        <!-- Resolve Modal -->
        <ResolveMaintenanceModal
            :show="showResolveModal"
            :record="selectedRecord"
            @close="showResolveModal = false"
            @success="showResolveModal = false; selectedRecord = null;"
        />

        <!-- Mark In Progress Modal -->
        <MarkInProgressModal
            :show="showInProgressModal"
            :record="selectedRecord"
            @close="showInProgressModal = false"
            @success="showInProgressModal = false; selectedRecord = null;"
        />

        <!-- Request Return Modal -->
        <Modal :show="showReturnModal" @close="showReturnModal = false">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center shrink-0">
                        <fa-icon icon="rotate-left" class="w-5 h-5 text-rose-600" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Request Asset Return</h3>
                        <p class="text-sm text-gray-500">{{ selectedRecord?.asset?.model_name }}</p>
                    </div>
                </div>

                <!-- Context: who is being asked -->
                <div class="mb-4 p-3 bg-gray-50 border border-gray-100 rounded-lg flex items-center gap-3">
                    <img
                        :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(selectedRecord?.reporter?.name ?? 'U')}&color=ffffff&background=DC143C&size=32`"
                        class="w-8 h-8 rounded-full shrink-0"
                    />
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ selectedRecord?.reporter?.name }}</p>
                        <p class="text-xs text-gray-400">
                            Will receive a notification and email asking them to return this asset.
                        </p>
                    </div>
                </div>

                <!-- Issue summary -->
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Reported Issue (read-only)
                    </label>
                    <p class="text-sm text-gray-700 bg-gray-50 border border-gray-100 rounded-lg px-3 py-2.5">
                        {{ selectedRecord?.description }}
                    </p>
                </div>

                <!-- Optional admin note -->
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Note to Employee <span class="font-normal normal-case text-gray-400">(optional)</span>
                    </label>
                    <textarea
                        v-model="returnForm.reason"
                        rows="3"
                        placeholder="Why to return this asset?"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-rose-400 focus:border-rose-400 outline-none resize-none"
                    ></textarea>
                    <p v-if="returnForm.errors.reason" class="text-xs text-rose-600 mt-1">
                        {{ returnForm.errors.reason }}
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        @click="showReturnModal = false"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitRequestReturn"
                        :disabled="returnForm.processing"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-medium transition-colors disabled:opacity-60"
                    >
                        <fa-icon v-if="returnForm.processing" icon="circle-notch" class="w-3.5 h-3.5 animate-spin" />
                        Send Return Request
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>