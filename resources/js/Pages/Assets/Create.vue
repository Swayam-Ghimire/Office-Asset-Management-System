<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";

defineProps({
    categories: Object,
});

const form = useForm({
    model_name: "",
    category_id: "",
    brand: "",
    purchase_date: "",
    condition: "new",
    status: "available",
    img_path: null,
});

const submit = () => {
    // Note: We use forceFormData: true because we are uploading a file (image)
    form.post(route("assets.store"), {
        forceFormData: true, // Forces the request to be multipart/form-data
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create Asset" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Add New Asset
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8"
                >
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-2 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="model_name" value="Model Asset Name" />
                                <TextInput
                                    id="model_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.model_name"
                                    required
                                    autofocus
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.model_name"
                                />
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel
                                    for="category_id"
                                    value="Category"
                                />
                                <select
                                    id="category_id"
                                    v-model="form.category_id"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="" disabled>
                                        Select a category
                                    </option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="category.id"
                                    >
                                        {{ category.name }}
                                    </option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.category_id"
                                />
                            </div>

                            <div>
                                <InputLabel for="brand" value="Brand" />
                                <TextInput
                                    id="brand"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.brand"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.brand"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel
                                    for="purchase_date"
                                    value="Purchase Date"
                                />
                                <TextInput
                                    id="purchase_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.purchase_date"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.purchase_date"
                                />
                            </div>

                            <div>
                                <InputLabel for="condition" value="Condition" />
                                <select
                                    id="condition"
                                    v-model="form.condition"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="new">New</option>
                                    <option value="good">Good</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.condition"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel
                                    for="status"
                                    value="Initial Status"
                                />
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="available">Available</option>
                                    <option value="not_available">Not Available</option>
                                    <option value="under_maintenance">
                                        Under Maintenance
                                    </option>
                                </select>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.status"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    for="img_path"
                                    value="Asset Image"
                                />
                                <input
                                    type="file"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                    @input="
                                        form.img_path = $event.target.files[0]
                                    "
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.img_path"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <PrimaryButton
                                class="ml-4"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Create Asset
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
