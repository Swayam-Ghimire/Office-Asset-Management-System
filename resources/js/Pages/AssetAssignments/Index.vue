<script setup>
import { ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ConfirmActionModal from "@/Components/Modals/ConfirmActionModal.vue";
import CategoryIcon from "@/Components/UI/CategoryIcon.vue";

const props = defineProps({
    assignments: Object,
    filters: Object,
});

const page = usePage();
const isAdmin = page.props.isAdmin;

const selectedStatus = ref(props.filters?.status ?? "");

function applyFilter() {
    router.get(
        route("asset-assignments.index"),
        { status: selectedStatus.value },
        {
            preserveScroll: true,
            replace: true,
        },
    );
}

// Return modal
const showReturnModal = ref(false);
const selectedAssignment = ref(null);

function confirmReturn(a) {
    selectedAssignment.value = a;
    showReturnModal.value = true;
}

function submitReturn() {
    router.post(
        route("asset-assignments.return", selectedAssignment.value.id),
        {},
        {
            onSuccess: () => {
                showReturnModal.value = false;
                selectedAssignment.value = null;
            },
        },
    );
}

// Report issue modal
const showIssueModal = ref(false);
const selectedAssetForIssue = ref(null);
const issueDescription = ref("");
const issueError = ref("");

function openIssueModal(assignment) {
    selectedAssetForIssue.value = assignment;
    issueDescription.value = "";
    issueError.value = "";
    showIssueModal.value = true;
}

function submitIssue() {
    if (!issueDescription.value.trim()) {
        issueError.value = "Please describe the issue.";
        return;
    }
    router.post(
        route("maintenance.store"),
        {
            asset_id: selectedAssetForIssue.value.asset_id,
            description: issueDescription.value,
        },
        {
            onSuccess: () => {
                showIssueModal.value = false;
                issueDescription.value = "";
            },
            onError: (errors) => {
                issueError.value =
                    errors.description ?? "Something went wrong.";
            },
        },
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
    <Head title="Assignments" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ isAdmin ? "All Assignments" : "My Assignments" }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{
                            isAdmin
                                ? "Who currently holds what asset"
                                : "Your current and past asset assignments"
                        }}
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-5">
            <!-- Filter bar -->
            <div
                class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex gap-2"
            >
                <button
                    v-for="opt in ['', 'assigned', 'returned']"
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
                            : opt.charAt(0).toUpperCase() + opt.slice(1)
                    }}
                </button>
            </div>

            <!-- Table -->
            <div
                class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
            >
                <div v-if="assignments.data.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                <th
                                    class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase"
                                >
                                    Asset
                                </th>
                                <th
                                    v-if="isAdmin"
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
                                    Returned
                                </th>
                                <th
                                    class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase"
                                >
                                    Status
                                </th>
                                <th
                                    class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="a in assignments.data"
                                :key="a.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500 shrink-0"
                                        >
                                            <CategoryIcon
                                                :name="a.asset?.category?.name"
                                                size="sm"
                                            />
                                        </div>
                                        <div>
                                            <p
                                                class="font-semibold text-gray-900"
                                            >
                                                {{ a.asset?.model_name }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                {{
                                                    a.asset?.category?.name ??
                                                    "Uncategorized"
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td v-if="isAdmin" class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <img
                                            :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(a.user?.name ?? 'U')}&color=ffffff&background=DC143C&size=28`"
                                            class="w-7 h-7 rounded-full"
                                        />
                                        <div>
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{ a.user?.name }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                {{ a.user?.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-xs">
                                    {{ fmt(a.assigned_date) }}
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-xs">
                                    {{
                                        a.return_date ? fmt(a.return_date) : "—"
                                    }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="
                                            a.status === 'assigned'
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-gray-100 text-gray-600'
                                        "
                                        class="text-xs px-2.5 py-1 rounded-full font-medium"
                                    >
                                        {{
                                            a.status.charAt(0).toUpperCase() +
                                            a.status.slice(1)
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="
                                                route('assets.show', a.asset_id)
                                            "
                                            class="text-xs text-gray-500 hover:text-gray-700 border border-gray-200 px-2.5 py-1.5 rounded-lg transition-colors"
                                        >
                                            <fa-icon
                                                icon="eye"
                                                class="w-3.5 h-3.5"
                                            />
                                        </Link>
                                        <!-- Report issue -->
                                        <button v-if="!isAdmin"
                                            @click="openIssueModal(a)"
                                            class="inline-flex items-center gap-1 text-xs border border-amber-200 text-amber-600 hover:bg-amber-50 px-2.5 py-1.5 rounded-lg transition-colors"
                                            title="Report an issue"
                                        >
                                            <fa-icon
                                                icon="triangle-exclamation"
                                            />
                                            Issue
                                        </button>
                                        <div v-if="isAdmin">
                                            <button
                                                v-if="a.status === 'assigned'"
                                                @click="confirmReturn(a)"
                                                class="text-xs border border-rose-200 text-rose-600 hover:bg-rose-50 px-2.5 py-1.5 rounded-lg transition-colors"
                                            >
                                                <fa-icon
                                                    icon="rotate-left"
                                                    class="w-3.5 h-3.5"
                                                />
                                                Ask to Return
                                            </button>
                                        </div>
                                        <div v-else>
                                            <button
                                                v-if="a.status === 'assigned'"
                                                @click="confirmReturn(a)"
                                                class="text-xs border border-rose-200 text-rose-600 hover:bg-rose-50 px-2.5 py-1.5 rounded-lg transition-colors"
                                            >
                                                <fa-icon
                                                    icon="rotate-left"
                                                    class="w-3.5 h-3.5"
                                                />
                                                Return
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-6 py-14 text-center">
                    <fa-icon icon="link" class="w-10 h-10 text-gray-200 mb-3" />
                    <p class="text-sm font-semibold text-gray-500 mb-1">
                        No assignments found
                    </p>
                    <p class="text-xs text-gray-400">
                        {{
                            isAdmin
                                ? "No assets have been assigned yet."
                                : "You have no asset assignments."
                        }}
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="assignments.last_page > 1"
                class="flex justify-center gap-1"
            >
                <template v-for="link in assignments.links" :key="link.label">
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

        <!-- Return Modal -->
        <ConfirmActionModal
            :show="showReturnModal"
            actionType="return"
            title="Return Asset"
            subtitle="This action cannot be undone"
            confirmText="Yes, Return It"
            @close="showReturnModal = false"
            @confirm="submitReturn"
        >
            <p class="text-sm text-gray-700 mb-6">
                Are you sure you want to return
                <span class="font-semibold">{{
                    selectedAssignment?.asset?.model_name
                }}</span
                >? It will become available for others to request.
            </p>
        </ConfirmActionModal>
        <ConfirmActionModal
            :show="showIssueModal"
            actionType="issue"
            title="Report an Issue"
            :subtitle="selectedAssetForIssue?.asset?.model_name"
            confirmText="Submit Report"
            @close="showIssueModal = false"
            @confirm="submitIssue"
        >
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1.5"
                    >Describe the issue
                    <span class="text-rose-500">*</span></label
                >
                <textarea
                    v-model="issueDescription"
                    rows="4"
                    placeholder="e.g. The screen flickers when opened, the keyboard has a stuck key..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none resize-none"
                ></textarea>
                <p v-if="issueError" class="text-xs text-rose-600 mt-1">
                    {{ issueError }}
                </p>
            </div>
        </ConfirmActionModal>
    </AuthenticatedLayout>
</template>
