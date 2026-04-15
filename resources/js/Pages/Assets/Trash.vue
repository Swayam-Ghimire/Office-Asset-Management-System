<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import CategoryIcon from '@/Components/UI/CategoryIcon.vue';

const props = defineProps({
    assets: Object, // paginated (onlyTrashed)
});

//  Restore 
function restore(id) {
    router.post(route('assets.restore', id), {}, { preserveScroll: true });
}

//  Force delete 
const showForceModal  = ref(false);
const selectedAsset   = ref(null);

function confirmForceDelete(asset) {
    selectedAsset.value  = asset;
    showForceModal.value = true;
}
function submitForceDelete() {
    router.delete(route('assets.force-delete', selectedAsset.value.id), {
        onSuccess: () => { showForceModal.value = false; selectedAsset.value = null; },
    });
}

function fmt(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
    });
}

const conditionColors = {
    new:     'bg-violet-100 text-violet-700',
    good:    'bg-sky-100 text-sky-700',
    damaged: 'bg-rose-100 text-rose-700',
};
</script>

<template>
    <Head title="Trash" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Asset Trash</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Deleted assets — restore them or permanently remove them
                    </p>
                </div>
                <Link
                    :href="route('home')"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-600 border border-gray-200 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <fa-icon icon="arrow-left" class="w-3.5 h-3.5" />
                    Back to Inventory
                </Link>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-5">

            <!-- Warning banner -->
            <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 px-5 py-3.5 rounded-xl text-sm">
                <fa-icon icon="triangle-exclamation" class="w-4 h-4 mt-0.5 shrink-0" />
                <p>
                    Assets in trash are hidden from the inventory but their history (assignments, logs) is preserved.
                    Permanent deletion cannot be undone.
                </p>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Trashed Assets</h2>
                    <span class="text-xs text-gray-400">{{ assets.total }} item{{ assets.total !== 1 ? 's' : '' }}</span>
                </div>

                <div v-if="assets.data.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Asset</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Category</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Condition</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Deleted</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="asset in assets.data"
                                :key="asset.id"
                                class="hover:bg-gray-50 transition-colors opacity-80"
                            >
                                <!-- Asset -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 shrink-0">
                                            <CategoryIcon :name="asset.category?.name" size="sm" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-700">{{ asset.model_name }}</p>
                                            <p class="text-xs text-gray-400">{{ asset.brand ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="px-6 py-4 text-gray-500 text-xs">
                                    {{ asset.category?.name ?? 'Uncategorized' }}
                                </td>

                                <!-- Condition -->
                                <td class="px-6 py-4">
                                    <span :class="conditionColors[asset.condition] ?? 'bg-gray-100 text-gray-600'"
                                        class="text-xs px-2 py-0.5 rounded-full font-medium">
                                        {{ asset.condition }}
                                    </span>
                                </td>

                                <!-- Deleted at -->
                                <td class="px-6 py-4 text-gray-500 text-xs whitespace-nowrap">
                                    {{ fmt(asset.deleted_at) }}
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            @click="restore(asset.id)"
                                            class="inline-flex items-center gap-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg transition-colors font-medium"
                                        >
                                            <fa-icon icon="rotate-left" class="w-3 h-3" />
                                            Restore
                                        </button>
                                        <button
                                            @click="confirmForceDelete(asset)"
                                            class="inline-flex items-center gap-1.5 text-xs border border-rose-200 text-rose-600 hover:bg-rose-50 px-3 py-1.5 rounded-lg transition-colors font-medium"
                                        >
                                            <fa-icon icon="trash" class="w-3 h-3" />
                                            Delete Forever
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty -->
                <div v-else class="px-6 py-16 text-center">
                    <fa-icon icon="trash" class="w-10 h-10 text-gray-200 mb-3" />
                    <p class="text-sm font-semibold text-gray-500 mb-1">Trash is empty</p>
                    <p class="text-xs text-gray-400">Deleted assets will appear here.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="assets.last_page > 1" class="flex justify-center gap-1">
                <template v-for="link in assets.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" v-html="link.label"
                        :class="['px-3 py-1.5 rounded-lg text-sm border transition-colors',
                                 link.active ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300']" />
                    <span v-else v-html="link.label"
                        class="px-3 py-1.5 rounded-lg text-sm border border-gray-100 bg-gray-50 text-gray-300" />
                </template>
            </div>
        </div>

        <!-- Force Delete Confirmation Modal -->
        <Modal :show="showForceModal" @close="showForceModal = false">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center shrink-0">
                        <fa-icon icon="trash" class="w-5 h-5 text-rose-600" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Permanently Delete Asset</h3>
                        <p class="text-sm text-gray-500">{{ selectedAsset?.model_name }}</p>
                    </div>
                </div>
                <p class="text-sm text-gray-700 mb-2">
                    This action <span class="font-semibold text-rose-700">cannot be undone</span>. The asset and all its related logs will be permanently removed from the system.
                </p>
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="showForceModal = false"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button @click="submitForceDelete"
                        class="px-4 py-2 text-sm bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-medium transition-colors">
                        Yes, Delete Permanently
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>