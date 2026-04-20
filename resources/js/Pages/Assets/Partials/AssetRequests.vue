<script setup>
const props = defineProps({
    asset: { type: Object, required: true },
});
    
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
                        <td class="px-6 py-3 text-gray-500 text-xs max-w-xs">
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
        <div v-else class="px-6 py-12 text-center text-sm text-gray-400">
            No requests yet.
        </div>
    </div>
</template>
