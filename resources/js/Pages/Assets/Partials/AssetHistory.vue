<script setup>
import { ref } from "vue";
const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
});
const img_path = ref(props.asset.assignments[0]?.user?.img_path);
function avatarUrl(name) {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&color=ffffff&background=DC143C`;
}
const photoPreview = ref(img_path.value ? "/storage/" + img_path.value : avatarUrl(props.asset.assignments[0]?.user?.name));


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
            <h2 class="font-semibold text-gray-900">Assignment History</h2>
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
                                    :src="photoPreview"
                                    class="w-7 h-7 rounded-full"
                                />
                                <span class="font-medium text-gray-900">{{
                                    a.user?.name ?? "Deleted user"
                                }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-gray-500 text-xs">
                            {{ fmt(a.assigned_date) }}
                        </td>
                        <td class="px-6 py-3 text-gray-500 text-xs">
                            {{ a.return_date ? fmt(a.return_date) : "—" }}
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
        <div v-else class="px-6 py-12 text-center text-sm text-gray-400">
            No assignment history.
        </div>
    </div>
</template>
