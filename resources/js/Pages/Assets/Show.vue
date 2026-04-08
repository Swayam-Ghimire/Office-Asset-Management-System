<script setup>
// Asset Show
import { ref } from "vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CategoryIcon from "@/Components/UI/CategoryIcon.vue";

const props = defineProps({
    asset: { type: Object, required: true },
});
const img_path = ref(props.asset.assignments[0]?.user?.img_path)
const page = usePage();
const isAdmin = page.props.isAdmin;

const activeTab = ref("details");
function avatarUrl(name) {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&color=ffffff&background=DC143C`;
}
const photoPreview = ref(
    img_path.value ? "/storage/" + img_path.value : avatarUrl(props.asset.assignments[0]?.user?.name),
);
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
};

function logStyle(action) {
    return (
        logIcons[action] ?? {
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
            <div class="flex gap-1 bg-gray-100 p-1 rounded-xl w-fit">
                <button
                    v-for="tab in ['details', 'history', 'requests', 'logs']"
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
            <div
                v-if="activeTab === 'details'"
                class="grid grid-cols-1 md:grid-cols-3 gap-6"
            >
                <!-- Image / icon -->
                <div class="md:col-span-1">
                    <div
                        class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
                    >
                        <img
                            v-if="asset.img_path"
                            :src="'/storage/' + asset.img_path"
                            :alt="asset.model_name"
                            class="w-full h-48 object-cover"
                        />
                        <div
                            v-else
                            class="h-48 flex items-center justify-center bg-gray-50"
                        >
                            <CategoryIcon
                                :name="asset.category?.name"
                                size="lg"
                                class="text-gray-300 scale-[3]"
                            />
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div
                    class="md:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6 space-y-4"
                >
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p
                                class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1"
                            >
                                Category
                            </p>
                            <p class="text-gray-900 font-medium">
                                {{ asset.category?.name ?? "—" }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1"
                            >
                                Brand
                            </p>
                            <p class="text-gray-900 font-medium">
                                {{ asset.brand ?? "—" }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1"
                            >
                                Purchase Date
                            </p>
                            <p class="text-gray-900 font-medium">
                                {{ fmt(asset.purchase_date) }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1"
                            >
                                Condition
                            </p>
                            <span
                                :class="[
                                    'text-xs px-2 py-0.5 rounded-full font-medium',
                                    conditionColors[asset.condition],
                                ]"
                            >
                                {{
                                    asset.condition.charAt(0).toUpperCase() +
                                    asset.condition.slice(1)
                                }}
                            </span>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1"
                            >
                                Status
                            </p>
                            <span
                                :class="[
                                    'text-xs px-2 py-0.5 rounded-full font-medium',
                                    statusColors[asset.status],
                                ]"
                            >
                                {{ asset.status.replace(/_/g, " ") }}
                            </span>
                        </div>
                        <div>
                            <p
                                class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1"
                            >
                                Added
                            </p>
                            <p class="text-gray-900 font-medium">
                                {{ fmt(asset.created_at) }}
                            </p>
                        </div>
                    </div>

                    <!-- Current assignment -->
                    <div
                        v-if="
                            asset.assignments.length !== 0"
                        class="pt-4 border-t border-gray-100"
                    >
                        <p
                            class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2"
                        >
                            Currently Assigned To
                        </p>
                        <div
                            v-for="a in asset.assignments"
                            :key="a.id"
                            class="flex items-center gap-3"
                        >
                            <img
                                :src="photoPreview"
                                class="w-8 h-8 rounded-full"
                            />
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ a.user?.name }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    Since {{ fmt(a.assigned_date) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History (assignments) tab -->
            <div
                v-if="activeTab === 'history'"
                class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
            >
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">
                        Assignment History
                    </h2>
                </div>
                <div v-if="asset.assignments?.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
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
                                    Returned
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
                                v-for="a in asset.assignments"
                                :key="a.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <img
                                            :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(a.user?.name ?? 'U')}&color=ffffff&background=DC143C&size=28`"
                                            class="w-7 h-7 rounded-full"
                                        />
                                        <span
                                            class="font-medium text-gray-900"
                                            >{{
                                                a.user?.name ?? "Deleted user"
                                            }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-gray-500 text-xs">
                                    {{ fmt(a.assigned_date) }}
                                </td>
                                <td class="px-6 py-3 text-gray-500 text-xs">
                                    {{
                                        a.return_date ? fmt(a.return_date) : "—"
                                    }}
                                </td>
                                <td class="px-6 py-3">
                                    <span
                                        :class="
                                            a.status === 'assigned'
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-gray-100 text-gray-600'
                                        "
                                        class="text-xs px-2 py-0.5 rounded-full font-medium"
                                    >
                                        {{ a.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    v-else
                    class="px-6 py-12 text-center text-sm text-gray-400"
                >
                    No assignment history.
                </div>
            </div>

            <!-- Requests tab (admin sees reason) -->
            <div
                v-if="activeTab === 'requests'"
                class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
            >
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Request History</h2>
                </div>
                <div v-if="asset.requests?.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                <th
                                    class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase"
                                >
                                    Employee
                                </th>
                                <th
                                    class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase"
                                >
                                    Date
                                </th>
                                <th
                                    class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase"
                                >
                                    Reason
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
                                v-for="req in asset.requests"
                                :key="req.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-3 font-medium text-gray-900">
                                    {{ req.user?.name ?? "Deleted user" }}
                                </td>
                                <td class="px-6 py-3 text-gray-500 text-xs">
                                    {{ fmt(req.requested_at) }}
                                </td>
                                <td
                                    class="px-6 py-3 text-gray-500 text-xs max-w-xs"
                                >
                                    <span v-if="req.reason" class="italic">{{
                                        req.reason
                                    }}</span>
                                    <span v-else class="text-gray-300"
                                        >No reason given</span
                                    >
                                </td>
                                <td class="px-6 py-3">
                                    <span
                                        :class="{
                                            'bg-amber-100 text-amber-700':
                                                req.status === 'pending',
                                            'bg-emerald-100 text-emerald-700':
                                                req.status === 'approved',
                                            'bg-rose-100 text-rose-700':
                                                req.status === 'rejected',
                                        }"
                                        class="text-xs px-2 py-0.5 rounded-full font-medium"
                                    >
                                        {{ req.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div
                    v-else
                    class="px-6 py-12 text-center text-sm text-gray-400"
                >
                    No requests yet.
                </div>
            </div>

            <!-- Logs tab — timeline -->
            <div
                v-if="activeTab === 'logs'"
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
                            <p
                                class="text-sm font-medium text-gray-900 capitalize"
                            >
                                {{ log.action.replace(/_/g, " ") }}
                            </p>
                            <p
                                v-if="log.remarks"
                                class="text-xs text-gray-500 mt-0.5"
                            >
                                {{ log.remarks }}
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <span
                                    v-if="log.user"
                                    class="text-xs text-gray-400"
                                    >{{ log.user.name }}</span
                                >
                                <span class="text-xs text-gray-300">·</span>
                                <span class="text-xs text-gray-400">{{
                                    fmt(log.created_at)
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    v-else
                    class="px-6 py-12 text-center text-sm text-gray-400"
                >
                    No activity logged.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
