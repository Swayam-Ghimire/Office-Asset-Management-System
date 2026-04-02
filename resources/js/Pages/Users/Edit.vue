<script setup>
import { useForm, Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    user: Object,
    departments: Array,
    roles: Array,
});

const form = useForm({
    name:          props.user.name,
    email:         props.user.email,
    department_id: props.user.department_id ?? '',
    role:          props.user.roles?.[0]?.name ?? 'employee',
    status:        props.user.status,   // integer: 1 = active, 0 = inactive
    password:      '',
    password_confirmation: '',
});

function submit() {
    form.patch(route('users.update', props.user.id), {
        onSuccess: () => form.reset('password', 'password_confirmation'),
    });
}

function deleteUser() {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        router.delete(route('users.destroy', props.user.id));
    }
}
</script>

<template>
    <Head title="Edit User" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Edit User</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Update account details for {{ user.name }}</p>
                </div>
                <Link
                    :href="route('users.index')"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-600 border border-gray-200 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Flash -->
                <div
                    v-if="$page.props.flash?.success"
                    class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 rounded-xl"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $page.props.flash.success }}
                </div>

                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

                    <!-- User Header -->
                    <div class="flex items-center gap-4 px-8 py-6 border-b border-gray-100 bg-gray-50">
                        <img
                            :src="user.img_path
                                ? '/storage/' + user.img_path
                                : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&color=ffffff&background=DC143C`"
                            :alt="user.name"
                            class="w-14 h-14 rounded-full object-cover border-2 border-white shadow"
                        />
                        <div>
                            <p class="font-semibold text-gray-900">{{ user.name }}</p>
                            <p class="text-sm text-gray-500">{{ user.email }}</p>
                            <span
                                :class="user.status === 1
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-rose-100 text-rose-700'"
                                class="mt-1 inline-block text-xs px-2 py-0.5 rounded-full font-medium"
                            >
                                {{ user.status === 1 ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="px-8 py-6 space-y-6">

                        <!-- Section: Basic Info -->
                        <div>
                            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Basic Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                <div>
                                    <InputLabel value="Full Name" />
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                                        placeholder="John Doe"
                                    />
                                    <InputError :message="form.errors.name" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Email Address" />
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        required
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                                        placeholder="john@company.com"
                                    />
                                    <InputError :message="form.errors.email" class="mt-1" />
                                </div>

                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-6">
                            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Role & Access</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                                <div>
                                    <InputLabel value="Department" />
                                    <select
                                        v-model="form.department_id"
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                                    >
                                        <option value="">No Department</option>
                                        <option
                                            v-for="dept in departments"
                                            :key="dept.id"
                                            :value="dept.id"
                                        >
                                            {{ dept.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.department_id" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Role" />
                                    <select
                                        v-model="form.role"
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                                    >
                                        <option value="employee">Employee</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    <InputError :message="form.errors.role" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Status" />
                                    <!-- Toggle switch -->
                                    <div class="mt-2 flex items-center gap-3">
                                        <button
                                            type="button"
                                            @click="form.status = form.status === 1 ? 0 : 1"
                                            :class="form.status === 1
                                                ? 'bg-emerald-500'
                                                : 'bg-gray-200'"
                                            class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1"
                                        >
                                            <span
                                                :class="form.status === 1 ? 'translate-x-5' : 'translate-x-0.5'"
                                                class="inline-block h-5 w-5 mt-0.5 rounded-full bg-white shadow-sm transform transition-transform duration-200"
                                            />
                                        </button>
                                        <span :class="form.status === 1 ? 'text-emerald-700' : 'text-gray-500'" class="text-sm font-medium">
                                            {{ form.status === 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <InputError :message="form.errors.status" class="mt-1" />
                                </div>

                            </div>
                        </div>

                        <!-- Section: Password -->
                        <div class="border-t border-gray-100 pt-6">
                            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Change Password</h2>
                            <p class="text-xs text-gray-400 mb-4">Leave blank to keep the current password.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                                <div>
                                    <InputLabel value="New Password" />
                                    <input
                                        v-model="form.password"
                                        type="password"
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                                        placeholder="Min. 8 characters"
                                        autocomplete="new-password"
                                    />
                                    <InputError :message="form.errors.password" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Confirm New Password" />
                                    <input
                                        v-model="form.password_confirmation"
                                        type="password"
                                        class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                                        placeholder="Repeat new password"
                                        autocomplete="new-password"
                                    />
                                </div>

                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="border-t border-gray-100 pt-6 flex items-center justify-between">
                            <Link
                                :href="route('users.index')"
                                class="text-sm text-gray-500 hover:text-gray-700 transition-colors"
                            >
                                Cancel
                            </Link>

                            <div class="flex items-center gap-3">
                                <Transition
                                    enter-active-class="transition ease-in-out duration-300"
                                    enter-from-class="opacity-0 translate-x-2"
                                    enter-to-class="opacity-100 translate-x-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <span v-if="form.recentlySuccessful" class="text-sm text-emerald-600 font-medium">
                                        ✓ Saved
                                    </span>
                                </Transition>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    :class="form.processing ? 'opacity-60 cursor-not-allowed' : 'hover:bg-red-700'"
                                    class="inline-flex items-center gap-2 bg-red-600 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors"
                                >
                                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ form.processing ? 'Saving…' : 'Save Changes' }}
                                </button>
                            </div>
                        </div>

                    </form>
                </div>

                <!-- Danger Zone -->
                <div class="mt-6 bg-white rounded-xl border border-rose-100 shadow-sm overflow-hidden">
                    <div class="px-8 py-5">
                        <h2 class="text-sm font-semibold text-rose-700 mb-1">Danger Zone</h2>
                        <p class="text-xs text-gray-500 mb-4">Deleting this user is permanent and cannot be undone. Any active assignments should be returned first.</p>
                        <button
                            type="button"
                            @click="deleteUser"
                            class="text-xs border border-rose-300 text-rose-600 hover:bg-rose-50 px-4 py-2 rounded-lg transition-colors font-medium"
                        >
                            Delete User
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>