<script setup>
import { ref } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    categories: Object, // paginated
});

// Create
const createForm = useForm({ name: "" });
function submitCreate() {
    createForm.post(route("categories.store"), {
        onSuccess: () => createForm.reset(),
    });
}

// Edit
const editingId = ref(null);
const editForm = useForm({ name: "" });

function startEdit(cat) {
    editingId.value = cat.id;
    editForm.name = cat.name;
}
function cancelEdit() {
    editingId.value = null;
    editForm.reset();
}
function submitEdit(cat) {
    editForm.put(route("categories.update", cat.id), {
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
}

// Delete
const showDeleteModal = ref(false);
const deletingCategory = ref(null);

function confirmDelete(cat) {
    deletingCategory.value = cat;
    showDeleteModal.value = true;
}
function submitDelete() {
    router.delete(route("categories.destroy", deletingCategory.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingCategory.value = null;
        },
    });
}
</script>

<template>
    <Head title="Categories" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Manage asset types used across the inventory
                </p>
            </div>
        </template>

        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Create form -->
            <div
                class="bg-white rounded-xl border border-gray-100 shadow-sm p-6"
            >
                <h2 class="text-sm font-semibold text-gray-700 mb-4">
                    Add New Category
                </h2>
                <form @submit.prevent="submitCreate" class="flex gap-3">
                    <div class="flex-1">
                        <input
                            v-model="createForm.name"
                            type="text"
                            placeholder="e.g. Laptop, Monitor, Keyboard…"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                        />
                        <InputError
                            :message="createForm.errors.name"
                            class="mt-1"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="
                            createForm.processing || !createForm.name.trim()
                        "
                        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                    >
                        <fa-icon icon="plus" class="w-3.5 h-3.5" />
                        Add
                    </button>
                </form>
            </div>

            <!-- Category list -->
            <div
                class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
            >
                <div
                    class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
                >
                    <h2 class="font-semibold text-gray-900">All Categories</h2>
                    <span class="text-xs text-gray-400"
                        >{{ categories.total }} total</span
                    >
                </div>

                <div
                    v-if="categories.data.length > 0"
                    class="divide-y divide-gray-50"
                >
                    <div
                        v-for="cat in categories.data"
                        :key="cat.id"
                        class="px-6 py-4 flex items-center gap-4"
                    >
                        <!-- Icon -->
                        <div
                            class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-rose-500 shrink-0"
                        >
                            <fa-icon icon="tags" class="w-4 h-4" />
                        </div>

                        <!-- Inline edit or display -->
                        <div class="flex-1 min-w-0">
                            <div v-if="editingId === cat.id" class="flex gap-2">
                                <input
                                    v-model="editForm.name"
                                    type="text"
                                    @keyup.enter="submitEdit(cat)"
                                    @keyup.escape="cancelEdit"
                                    class="flex-1 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-red-500 outline-none"
                                    autofocus
                                />
                                <InputError
                                    :message="editForm.errors.name"
                                    class="mt-1"
                                />
                                <button
                                    @click="submitEdit(cat)"
                                    class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg"
                                >
                                    Save
                                </button>
                                <button
                                    @click="cancelEdit"
                                    class="text-xs border border-gray-200 text-gray-600 hover:bg-gray-50 px-3 py-1.5 rounded-lg"
                                >
                                    Cancel
                                </button>
                            </div>
                            <div v-else>
                                <p class="font-medium text-gray-900 text-sm">
                                    {{ cat.name }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ cat.assets_count }} asset{{
                                        cat.assets_count !== 1 ? "s" : ""
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div
                            v-if="editingId !== cat.id"
                            class="flex gap-1 shrink-0"
                        >
                            <button
                                @click="startEdit(cat)"
                                class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                title="Edit"
                            >
                                <fa-icon icon="pen" class="w-4 h-4" />
                            </button>
                            <button
                                @click="confirmDelete(cat)"
                                :disabled="cat.assets_count > 0"
                                :title="
                                    cat.assets_count > 0
                                        ? 'Has assets — cannot delete'
                                        : 'Delete'
                                "
                                class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                            >
                                <fa-icon icon="trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="px-6 py-12 text-center">
                    <fa-icon icon="tags" class="w-10 h-10 text-rose-200 mb-3" />
                    <p class="text-sm text-gray-400">
                        No categories yet. Add one above.
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="categories.last_page > 1"
                class="flex justify-center gap-1"
            >
                <template v-for="link in categories.links" :key="link.label">
                    <a
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

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div
                        class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center shrink-0"
                    >
                        <fa-icon icon="trash" class="w-5 h-5 text-rose-600" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">
                            Delete Category
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ deletingCategory?.name }}
                        </p>
                    </div>
                </div>
                <p class="text-sm text-gray-700 mb-6">
                    This will permanently delete this category. Assets in this
                    category will become uncategorized.
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitDelete"
                        class="px-4 py-2 text-sm bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-medium"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
