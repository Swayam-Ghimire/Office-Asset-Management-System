<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";

defineProps({
    departments: Array,
    // roles: Array,
});

const form = useForm({
    name: "",
    email: "",
    // password: "",
    // password_confirmation: "",
    status: 1,
    department_id: "",
    role: "employee",
});

const submit = () => {
    form.post(route("users.store"), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create User" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">
                Create New User
            </h2>
        </template>

        <div class="py-10">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm rounded-lg p-8">

                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="name" value="Name" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    v-model="form.name"
                                    class="mt-1 w-full"
                                    autofocus
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    class="mt-1 w-full"
                                />
                                <InputError :message="form.errors.email" />
                            </div>
                        </div>

                        <!-- Password -->
                        <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="password" value="Password" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    v-model="form.password"
                                    class="mt-1 w-full"
                                />
                                <InputError :message="form.errors.password" />
                            </div>

                            <div>
                                <InputLabel
                                    for="password_confirmation"
                                    value="Confirm Password"
                                />
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    v-model="form.password_confirmation"
                                    class="mt-1 w-full"
                                />
                            </div>
                        </div> -->

                        <!-- Role & Department -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="role" value="Role" />
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="mt-1 w-full rounded-md border-gray-300"
                                >
                                    <option value="employee" selected>Employee</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <InputError :message="form.errors.role" />
                            </div>

                            <div>
                                <InputLabel
                                    for="department_id"
                                    value="Department"
                                />
                                <select
                                    id="department_id"
                                    v-model="form.department_id"
                                    class="mt-1 w-full rounded-md border-gray-300"
                                >
                                    <option value="">-- Optional --</option>
                                    <option
                                        v-for="department in departments"
                                        :key="department.id"
                                        :value="department.id"
                                    >
                                        {{ department.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.department_id" />
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <InputLabel for="status" value="Status" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 w-full rounded-md border-gray-300"
                            >
                                <option :value="1">Active</option>
                                <option :value="0">Inactive</option>
                            </select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end">
                            <PrimaryButton :disabled="form.processing">
                                {{ form.processing ? "Creating..." : "Create User" }}
                            </PrimaryButton>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>