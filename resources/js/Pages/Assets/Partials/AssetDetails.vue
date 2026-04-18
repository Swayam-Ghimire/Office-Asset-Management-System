<script setup>
import { ref } from "vue";
import CategoryIcon from "@/Components/UI/CategoryIcon.vue";
const props = defineProps({
    asset: {
        type: Object,
        required: true,
    },
    conditionColors: {
        type: Object,
        required: true,
    },
    statusColors: {
        type: Object,
        required: true,
    },
    isAdmin: {
        type: Boolean,
        required: true,
    },
});

const img_path = ref(props.asset.assignments[0]?.user?.img_path);

function avatarUrl(name) {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&color=ffffff&background=DC143C`;
}

const photoPreview = ref(
    img_path.value
        ? "/storage/" + img_path.value
        : avatarUrl(props.asset.assignments[0]?.user?.name),
);

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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                v-if="asset.assignments?.some(a => a.status === 'assigned') && isAdmin"
                class="pt-4 border-t border-gray-100"
            >
                <p
                    class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2"
                >
                    Currently Assigned To
                </p>
                <div
                    v-for="a in asset.assignments.filter(x => x.status === 'assigned')"
                    :key="a.id"
                    class="flex items-center gap-3"
                >
                    <img :src="photoPreview" class="w-8 h-8 rounded-full" />
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
</template>
