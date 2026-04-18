<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CategoryIcon from '@/Components/UI/CategoryIcon.vue';


const props = defineProps({
    assets: Object,
    categories: Array,
    stats: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? '');
const selectedCategory = ref(props.filters?.category ?? '');
const selectedStatus = ref(props.filters?.status ?? '');
const selectedCondition = ref(props.filters?.condition ?? '');

function applyFilters() {
    router.get(route('home'), {
        search: search.value,
        category: selectedCategory.value,
        status: selectedStatus.value,
        condition: selectedCondition.value,
    }, { preserveScroll: true, replace: true });
}

function resetFilters() {
    search.value = '';
    selectedCategory.value = '';
    selectedStatus.value = '';
    selectedCondition.value = '';
    router.get(route('home'));
}

function requestAsset(assetId) {
    router.get(route('asset-requests.create', { asset_id: assetId }));
}

const statusColors = {
    available:        'bg-emerald-100 text-emerald-700 border-emerald-200',
    not_available:         'bg-blue-100 text-blue-700 border-blue-200',
    under_maintenance:'bg-amber-100 text-amber-700 border-amber-200',
};

const conditionColors = {
    new:     'bg-violet-100 text-violet-700',
    good:    'bg-sky-100 text-sky-700',
    damaged: 'bg-rose-100 text-rose-700',
};

const statusDot = {
    available:        'bg-emerald-400',
    not_available:         'bg-blue-400',
    under_maintenance:'bg-amber-400',
};
</script>

<template>
    <Head title="Assets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Asset Inventory</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Browse and request available office assets</p>
                </div>
                <Link
                    v-if="$page.props.isAdmin"
                    :href="route('assets.create')"
                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm"
                >
                    <fa-icon icon="circle-plus"/>
                    Add Asset
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Stats Bar -->
                <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-lg text-slate-500">
                            <fa-icon icon="box"/>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Total</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-lg text-green-700">
                            <fa-icon icon="check"/>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Available</p>
                            <p class="text-2xl font-bold text-emerald-600">{{ stats.available }}</p>
                        </div>
                    </div>
                    <div v-if="!$page.props.isAdmin" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-lg text-blue-500">
                            <fa-icon icon="mobile-screen" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">Assigned to you</p>
                            <p class="text-2xl font-bold text-blue-600">{{ stats.assigned_to_you }}</p>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                    <div class="flex flex-wrap gap-3 items-end">
                        <!-- Search -->
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                                </svg>
                                <input
                                    v-model="search"
                                    @keyup.enter="applyFilters"
                                    type="text"
                                    placeholder="Name, brand…"
                                    class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                                />
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="min-w-[160px]">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                            <select v-model="selectedCategory" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="min-w-[150px]">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                            <select v-model="selectedStatus" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                                <option value="">All Statuses</option>
                                <option value="available">Available</option>
                                <option value="not_available">Not available</option>
                                <option value="under_maintenance">Maintenance</option>
                            </select>
                        </div>

                        <!-- Condition -->
                        <div class="min-w-[140px]">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Condition</label>
                            <select v-model="selectedCondition" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none">
                                <option value="">All Conditions</option>
                                <option value="new">New</option>
                                <option value="good">Good</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </div>

                        <div class="flex gap-2 pb-0.5">
                            <button @click="applyFilters" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Filter
                            </button>
                            <button @click="resetFilters" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Asset Grid -->
                <div v-if="assets.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    <div
                        v-for="asset in assets.data"
                        :key="asset.id"
                        class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col"
                    >
                        <!-- Image / Icon -->
                        <div class="h-36 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center relative">
                            <img
                                v-if="asset.img_path"
                                :src="`/storage/${asset.img_path}`"
                                :alt="asset.model_name"
                                class="h-full w-full object-cover"
                            />
                            <div v-else class="text-5xl select-none">
                                <CategoryIcon :name="asset?.category?.name" size="lg"/>
                            </div>

                            <!-- Status badge -->
                            <span :class="['absolute top-2 right-2 text-xs px-2 py-0.5 rounded-full border font-medium', statusColors[asset.status]]">
                                <span :class="['inline-block w-1.5 h-1.5 rounded-full mr-1 mb-0.5', statusDot[asset.status]]"></span>
                                {{ asset.status === 'under_maintenance' ? 'Maintenance' : asset.status.charAt(0).toUpperCase() + asset.status.slice(1) }}
                            </span>
                        </div>

                        <!-- Body -->
                        <div class="p-4 flex flex-col flex-1">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <h3 class="font-semibold text-gray-900 text-sm leading-tight line-clamp-2">{{ asset.model_name }}</h3>
                                <span :class="['text-xs px-1.5 py-0.5 rounded font-medium shrink-0', conditionColors[asset.condition]]">
                                    {{ asset.condition.charAt(0).toUpperCase() + asset.condition.slice(1) }}
                                </span>
                            </div>

                            <div class="space-y-1 text-xs text-gray-500 mb-4">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 10V5a2 2 0 012-2z"/>
                                    </svg>
                                    {{ asset.category?.name ?? 'Uncategorized' }}
                                </div>
                                <div v-if="asset.brand" class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    {{ asset.brand }}
                                </div>
                            </div>

                            <div class="mt-auto flex gap-2">
                                <!-- View -->
                                <Link
                                    :href="route('assets.show', asset.id)"
                                    class="flex-1 text-center text-xs border border-gray-200 hover:border-gray-300 text-gray-600 py-1.5 rounded-lg transition-colors"
                                >
                                    Details
                                </Link>

                                <!-- Admin actions -->
                                <template v-if="$page.props.isAdmin">
                                    <Link
                                        :href="route('assets.edit', asset.id)"
                                        class="flex-1 text-center text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 py-1.5 rounded-lg transition-colors"
                                    >
                                        Edit
                                    </Link>
                                </template>

                                <!-- Employee: request -->
                                <template v-else>
                                    <button
                                        v-if="asset.status === 'available' && asset.condition !== 'damaged'"
                                        @click="requestAsset(asset.id)"
                                        class="flex-1 text-xs bg-red-600 hover:bg-red-700 text-white py-1.5 rounded-lg transition-colors font-medium"
                                    >
                                        Request
                                    </button>
                                    <span
                                        v-else
                                        class="flex-1 text-center text-xs bg-gray-50 text-gray-400 py-1.5 rounded-lg cursor-not-allowed"
                                    >
                                        Unavailable
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-xl border border-gray-100 shadow-sm p-16 text-center">
                    <div class="text-5xl mb-4 text-rose-400">
                        <fa-icon icon="box"/>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-1">No assets found</h3>
                    <p class="text-sm text-gray-400 mb-4">Try adjusting your filters or add a new asset.</p>
                    <button @click="resetFilters" class="text-red-600 text-sm underline">Clear filters</button>
                </div>

                <!-- Pagination -->
                <div v-if="assets.last_page > 1" class="flex justify-center gap-1">
                    <template v-for="link in assets.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-sm border transition-colors',
                                link.active
                                    ? 'bg-red-600 text-white border-red-600'
                                    : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'
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
    </AuthenticatedLayout>
</template>