<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import CategoryIcon from "@/Components/UI/CategoryIcon.vue";

const props = defineProps({
    assets: Array, // available assets from controller
    selected: Object, // full asset object passed when coming from asset card
});

// search / filter state
const search = ref("");
const selectedCat = ref("");

// form
const form = useForm({
    asset_id: props.selected?.id ?? null,
    reason: "",
});

const selectedAsset = computed(
    () =>
        props.assets.find((a) => a.id === form.asset_id) ??
        (props.selected?.id === form.asset_id ? props.selected : null),
);

const categories = computed(() => {
    const seen = new Set();
    return props.assets
        .map((a) => a.category)
        .filter((c) => c && !seen.has(c.id) && seen.add(c.id));
});

const filtered = computed(() =>
    props.assets.filter((a) => {
        const q = search.value.toLowerCase();
        const matchSearch =
            !q ||
            a.model_name?.toLowerCase().includes(q) ||
            a.brand?.toLowerCase().includes(q);
        const matchCat =
            !selectedCat.value || a.category_id == selectedCat.value;
        return matchSearch && matchCat;
    }),
);

function selectAsset(asset) {
    form.asset_id = asset.id;
}

function submit() {
    form.post(route("asset-requests.store"));
}

// Helpers

const conditionBadge = {
    new: "bg-violet-100 text-violet-700",
    good: "bg-sky-100 text-sky-700",
    damaged: "bg-rose-100 text-rose-700",
};
</script>

<template>
    <Head title="Request Asset" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                        Request an Asset
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{
                            selected
                                ? `Requesting: ${selected.model_name}`
                                : "Pick an available asset below"
                        }}
                    </p>
                </div>
                <Link
                    :href="route('asset-requests.index')"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-600 border border-gray-200 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors"
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
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>
                    My Requests
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Flash error -->
                <div
                    v-if="$page.props.flash?.error"
                    class="mb-6 flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-xl"
                >
                    <svg
                        class="w-4 h-4 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
                        />
                    </svg>
                    {{ $page.props.flash.error }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- ── LEFT: asset picker ── -->
                    <div class="lg:col-span-2 space-y-4">
                        <!-- Search + Category filter -->
                        <div
                            class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-center"
                        >
                            <div class="flex-1 min-w-[180px] relative">
                                <fa-icon
                                    icon="search"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                                />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search by name or brand…"
                                    class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                                />
                            </div>
                            <select
                                v-model="selectedCat"
                                class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                            >
                                <option value="">All Categories</option>
                                <option
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :value="cat.id"
                                >
                                    {{ cat.name }}
                                </option>
                            </select>
                            <span class="text-xs text-gray-400 ml-auto">
                                {{ filtered.length }} asset{{
                                    filtered.length !== 1 ? "s" : ""
                                }}
                                available
                            </span>
                        </div>

                        <!-- Asset cards grid -->
                        <div
                            v-if="filtered.length > 0"
                            class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                        >
                            <button
                                v-for="asset in filtered"
                                :key="asset.id"
                                type="button"
                                @click="selectAsset(asset)"
                                :class="[
                                    'text-left rounded-xl border-2 p-4 transition-all duration-150 w-full',
                                    form.asset_id === asset.id
                                        ? 'border-red-500 bg-red-50 shadow-md ring-1 ring-red-200'
                                        : 'border-gray-100 bg-white hover:border-gray-300 hover:shadow-sm',
                                ]"
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        :class="
                                            form.asset_id === asset.id
                                                ? 'bg-red-100'
                                                : 'bg-gray-100'
                                        "
                                        class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 transition-colors text-rose-500"
                                    >
                                        <CategoryIcon
                                            :name="asset.category?.name"
                                        />
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div
                                            class="flex items-start justify-between gap-2"
                                        >
                                            <p
                                                class="font-semibold text-gray-900 text-sm leading-snug"
                                            >
                                                {{ asset.model_name }}
                                            </p>
                                            <!-- Selected checkmark -->
                                            <div
                                                v-if="
                                                    form.asset_id === asset.id
                                                "
                                                class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center shrink-0 mt-0.5 text-white"
                                            >
                                                <fa-icon icon="check" />
                                            </div>
                                        </div>

                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{
                                                asset.category?.name ??
                                                "Uncategorized"
                                            }}
                                        </p>

                                        <div
                                            class="flex flex-wrap items-center gap-1.5 mt-2"
                                        >
                                            <span
                                                v-if="asset.brand"
                                                class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-medium"
                                            >
                                                {{ asset.brand }}
                                            </span>
                                            <span
                                                :class="
                                                    conditionBadge[
                                                        asset.condition
                                                    ] ??
                                                    'bg-gray-100 text-gray-600'
                                                "
                                                class="text-xs px-2 py-0.5 rounded font-medium"
                                            >
                                                {{
                                                    asset.condition
                                                        ?.charAt(0)
                                                        .toUpperCase() +
                                                    asset.condition?.slice(1)
                                                }}
                                            </span>
                                            <span
                                                v-if="
                                                    asset.quantity !== undefined
                                                "
                                                :class="
                                                    asset.quantity > 1
                                                        ? 'bg-emerald-100 text-emerald-700'
                                                        : 'bg-amber-100 text-amber-700'
                                                "
                                                class="text-xs px-2 py-0.5 rounded font-medium"
                                            >
                                                Qty: {{ asset.quantity }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </div>

                        <!-- Empty state -->
                        <div
                            v-else
                            class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center"
                        >
                            <div
                                class="flex justify-center mb-3 text-slate-400"
                            >
                                <fa-icon icon="search" />
                            </div>
                            <p class="text-sm font-semibold text-gray-600 mb-1">
                                No assets match your search
                            </p>
                            <p class="text-xs text-gray-400 mb-3">
                                Try clearing your filters
                            </p>
                            <button
                                @click="
                                    search = '';
                                    selectedCat = '';
                                "
                                class="text-xs text-red-600 hover:underline"
                            >
                                Clear filters
                            </button>
                        </div>
                    </div>

                    <!-- ── RIGHT: Summary + Submit ── -->
                    <div class="lg:col-span-1">
                        <div
                            class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden sticky top-24"
                        >
                            <div
                                class="px-6 py-4 border-b border-gray-100 bg-gray-50"
                            >
                                <h2 class="font-semibold text-gray-900 text-sm">
                                    Request Summary
                                </h2>
                            </div>

                            <div class="px-6 py-5 space-y-5">
                                <!-- Selected asset -->
                                <div>
                                    <p
                                        class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2"
                                    >
                                        Selected Asset
                                    </p>

                                    <div
                                        v-if="selectedAsset"
                                        class="flex items-center gap-3 p-3.5 bg-red-50 border border-red-200 rounded-xl"
                                    >
                                        <div
                                            class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center shrink-0"
                                        >
                                            <CategoryIcon
                                                :name="
                                                    selectedAsset.category?.name
                                                "
                                                size="sm"
                                            />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="font-semibold text-gray-900 text-sm truncate"
                                            >
                                                {{ selectedAsset.model_name }}
                                            </p>
                                            <p
                                                class="text-xs text-gray-500 truncate"
                                            >
                                                {{
                                                    selectedAsset.category?.name
                                                }}
                                                <span
                                                    v-if="selectedAsset.brand"
                                                >
                                                    ·
                                                    {{
                                                        selectedAsset.brand
                                                    }}</span
                                                >
                                            </p>
                                            <p
                                                v-if="
                                                    selectedAsset.quantity !==
                                                    undefined
                                                "
                                                :class="
                                                    selectedAsset.quantity > 1
                                                        ? 'text-emerald-600'
                                                        : 'text-amber-600'
                                                "
                                                class="text-xs font-medium mt-0.5"
                                            >
                                                {{ selectedAsset.quantity }}
                                                unit{{
                                                    selectedAsset.quantity !== 1
                                                        ? "s"
                                                        : ""
                                                }}
                                                available
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-else
                                        class="p-5 border-2 border-dashed border-gray-200 rounded-xl text-center"
                                    >
                                        <p class="text-sm text-gray-400">
                                            No asset selected
                                        </p>
                                        <p class="text-xs text-gray-300 mt-0.5">
                                            Click a card on the left
                                        </p>
                                    </div>

                                    <p
                                        v-if="form.errors.asset_id"
                                        class="text-xs text-rose-600 mt-1.5"
                                    >
                                        {{ form.errors.asset_id }}
                                    </p>
                                </div>

                                <!-- Reason -->
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5"
                                    >
                                        Reason
                                        <span
                                            class="normal-case font-normal text-gray-300 ml-1"
                                            >(optional)</span
                                        >
                                    </label>
                                    <textarea
                                        v-model="form.reason"
                                        rows="3"
                                        placeholder="Why do you need this asset?"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none resize-none"
                                    ></textarea>
                                </div>

                                <!-- Info -->
                                <div
                                    class="flex items-start gap-2 p-3 bg-blue-50 border border-blue-100 rounded-lg text-blue-500"
                                >
                                    <fa-icon icon="triangle-exclamation" />
                                    <p
                                        class="text-xs text-blue-700 leading-relaxed"
                                    >
                                        Your request will be reviewed by an
                                        admin. Status updates appear in your
                                        dashboard.
                                    </p>
                                </div>

                                <!-- Submit -->
                                <button
                                    type="button"
                                    @click="submit"
                                    :disabled="
                                        !form.asset_id || form.processing
                                    "
                                    :class="[
                                        'w-full py-2.5 rounded-lg text-sm font-semibold transition-all',
                                        form.asset_id && !form.processing
                                            ? 'bg-red-600 hover:bg-red-700 text-white shadow-sm'
                                            : 'bg-gray-100 text-gray-400 cursor-not-allowed',
                                    ]"
                                >
                                    <span
                                        v-if="form.processing"
                                        class="flex items-center justify-center gap-2"
                                    >
                                        Submitting…
                                    </span>
                                    <span v-else>
                                        {{
                                            form.asset_id
                                                ? "Submit Request"
                                                : "Select an asset first"
                                        }}
                                    </span>
                                </button>

                                <Link
                                    :href="route('asset-requests.index')"
                                    class="block text-center text-xs text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    View my request history →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
