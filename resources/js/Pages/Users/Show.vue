<script setup>
// show users
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CategoryIcon from '@/Components/UI/CategoryIcon.vue';

const props = defineProps({
    user: { type: Object, required: true },
});

function fmt(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
    });
}

const primaryRole = props.user.roles?.[0]?.name ?? 'employee';
</script>

<template>
    <Head :title="user.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <img
                        :src="user.img_path
                            ? '/storage/' + user.img_path
                            : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&color=ffffff&background=DC143C`"
                        :alt="user.name"
                        class="w-12 h-12 rounded-full object-cover border-2 border-white shadow"
                    />
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ user.name }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span :class="primaryRole === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'"
                                class="text-xs px-2 py-0.5 rounded-full font-semibold uppercase tracking-wide">
                                {{ primaryRole }}
                            </span>
                            <span :class="user.status === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'"
                                class="text-xs px-2 py-0.5 rounded-full font-medium">
                                {{ user.status === 1 ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
                <Link :href="route('users.edit', user.id)"
                    class="inline-flex items-center gap-1.5 text-sm bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <fa-icon icon="pen" class="w-3.5 h-3.5" />
                    Edit User
                </Link>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-6">

            <!-- Info cards row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Email</p>
                    <p class="text-sm font-medium text-gray-900 break-all">{{ user.email }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Department</p>
                    <p class="text-sm font-medium text-gray-900">{{ user.department?.name ?? 'None' }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Joined</p>
                    <p class="text-sm font-medium text-gray-900">{{ fmt(user.created_at) }}</p>
                </div>
            </div>

            <!-- Current Assignments -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Currently Assigned Assets</h2>
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">
                        {{ user.assignments?.filter(a => a.status === 'assigned').length ?? 0 }}
                    </span>
                </div>

                <div v-if="user.assignments?.some(a => a.status === 'assigned')" class="divide-y divide-gray-50">
                    <div v-for="a in user.assignments.filter(x => x.status === 'assigned')" :key="a.id"
                        class="px-6 py-4 flex items-center gap-4 hover:bg-gray-50 transition-colors">
                        <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500 shrink-0">
                            <CategoryIcon :name="a.asset?.category?.name" size="sm" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm">{{ a.asset?.model_name }}</p>
                            <p class="text-xs text-gray-400">{{ a.asset?.category?.name ?? 'Uncategorized' }} · Since {{ fmt(a.assigned_date) }}</p>
                        </div>
                        <Link :href="route('assets.show', a.asset_id)"
                            class="text-xs text-gray-500 hover:text-gray-700 border border-gray-200 px-2.5 py-1.5 rounded-lg">
                            <fa-icon icon="eye" class="w-3.5 h-3.5" />
                        </Link>
                    </div>
                </div>
                <div v-else class="px-6 py-10 text-center text-sm text-gray-400">
                    No assets currently assigned to this user.
                </div>
            </div>

            <!-- Request history -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Request History</h2>
                </div>

                <div v-if="user.requests?.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Asset</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Reason</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="req in user.requests" :key="req.id" class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-900">
                                    {{ req.asset?.model_name ?? 'Deleted asset' }}
                                </td>
                                <td class="px-6 py-3 text-gray-500 text-xs">{{ fmt(req.requested_at) }}</td>
                                <td class="px-6 py-3 text-gray-500 text-xs max-w-xs">
                                    <span v-if="req.reason" class="italic">{{ req.reason }}</span>
                                    <span v-else class="text-gray-300">—</span>
                                </td>
                                <td class="px-6 py-3">
                                    <span :class="{
                                        'bg-amber-100 text-amber-700':   req.status === 'pending',
                                        'bg-emerald-100 text-emerald-700': req.status === 'approved',
                                        'bg-rose-100 text-rose-700':     req.status === 'rejected',
                                    }" class="text-xs px-2 py-0.5 rounded-full font-medium">
                                        {{ req.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-6 py-10 text-center text-sm text-gray-400">No requests made yet.</div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>