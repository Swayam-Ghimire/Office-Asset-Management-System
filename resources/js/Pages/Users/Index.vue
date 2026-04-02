<script setup>
// users index vue
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    users: Object, // Paginated object
    departments: Array,
    filters: Object,
});

// Reactive filter state
const search = ref(props.filters?.search ?? "");
const status = ref(props.filters?.status ?? null);
const selectedDepartment = ref(props.filters?.department ?? "");

function applyFilters() {
    router.get(
        route("users.index"),
        {
            search: search.value,
            status: status.value,
            department: selectedDepartment.value,
        },
        {
            preserveScroll: true,
            replace: true,
        },
    );
}

function resetFilters() {
    search.value = "";
    status.value = null;
    selectedDepartment.value = "";
    router.get(route("users.index"));
}

const deleteUser = (id) => {
    if (confirm("Are you sure you want to delete this user?")) {
        router.delete(route("users.destroy", id));
    }
};

// const toggleStatus = (user) => {
//     const newStatus = user.status === 1 ? 0 : 1;
//     router.patch(route("users.update-status", user.id), { status: newStatus });
// };
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    User Management
                </h2>
                <Link
                    :href="route('users.create')"
                    class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm font-medium transition"
                >
                    Add New User
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div
                    class="bg-white p-6 rounded-t-lg border-b border-gray-200 shadow-sm flex flex-wrap gap-4 items-end"
                >
                    <div class="flex-1 min-w-[200px]">
                        <label
                            class="block text-xs font-medium text-gray-600 mb-1"
                            >Search</label
                        >
                        <div class="relative">
                            <svg
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"
                                />
                            </svg>
                            <input
                                v-model="search"
                                @keyup.enter="applyFilters"
                                type="text"
                                placeholder="Name, code, brand…"
                                class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                            />
                        </div>
                    </div>

                    <div class="w-48">
                        <label
                            class="block text-xs font-medium text-gray-600 mb-1"
                            >Status</label
                        >
                        <select
                            v-model="status"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                        >
                            <option value="null">All Statuses</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="w-48">
                        <label
                            class="block text-xs font-medium text-gray-600 mb-1"
                            >Department</label
                        >
                        <select
                            v-model="selectedDepartment"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                        >
                            <option value="">Department</option>
                            <option
                                v-for="department in departments"
                                :key="department.id"
                                :value="department.id"
                            >
                                {{ department.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex gap-2 pb-0.5">
                        <button
                            @click="applyFilters"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                        >
                            Filter
                        </button>
                        <button
                            @click="resetFilters"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                        >
                            Reset
                        </button>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-b-lg">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase"
                                    >
                                        User
                                    </th>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase"
                                    >
                                        Department
                                    </th>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase"
                                    >
                                        Role
                                    </th>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase text-center"
                                    >
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase text-right"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="hover:bg-gray-50 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 shrink-0">
                                                <img
                                                    v-if="user.img_path"
                                                    :src="
                                                        '/storage/' +
                                                        user.img_path
                                                    "
                                                    :alt="user.name"
                                                    class="h-10 w-10 rounded-full object-cover border border-gray-200"
                                                />
                                                <img
                                                    v-else
                                                    :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&color=7F9CF5&background=EBF4FF`"
                                                    :alt="user.name"
                                                    class="h-10 w-10 rounded-full border border-gray-200"
                                                />
                                            </div>

                                            <div>
                                                <div
                                                    class="text-sm font-bold text-gray-900"
                                                >
                                                    {{ user.name }}
                                                </div>
                                                <div
                                                    class="text-xs text-gray-500"
                                                >
                                                    {{ user.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ user.department?.name || "N/A" }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            v-for="role in user.roles"
                                            :key="role.id"
                                            class="text-[10px] uppercase font-bold px-2 py-0.5 bg-gray-100 text-gray-600 rounded mr-1"
                                        >
                                            {{ role.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            :class="
                                                user.status === 1
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : 'bg-rose-100 text-rose-700'
                                            "
                                            class="px-3 py-1 rounded-full text-xs font-medium hover:opacity-75 transition"
                                        >
                                            {{
                                                user.status === 1
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="
                                                    route('users.show', user.id)
                                                "
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                                title="View Details"
                                            >
                                                <svg
                                                    class="w-5 h-5"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                    />
                                                    <path
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                    />
                                                </svg>
                                            </Link>
                                            <Link
                                                :href="
                                                    route('users.edit', user.id)
                                                "
                                                class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                                title="Edit User"
                                            >
                                                <svg
                                                    class="w-5 h-5"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                    />
                                                </svg>
                                            </Link>
                                            <button
                                                @click="deleteUser(user.id)"
                                                class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                                title="Delete User"
                                            >
                                                <svg
                                                    class="w-5 h-5"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-6 py-12 text-center text-gray-500"
                                    >
                                        No users found matching your criteria.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex justify-center">
                    <div class="flex gap-1">
                        <Link
                            v-for="link in users.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'px-4 py-2 rounded-md border text-sm transition',
                                link.active
                                    ? 'bg-red-600 text-white border-red-600'
                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
                                !link.url
                                    ? 'opacity-50 cursor-not-allowed'
                                    : '',
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
