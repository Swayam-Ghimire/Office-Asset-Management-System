<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SidebarLink    from '@/Components/Navigation/SidebarLink.vue';
import SidebarSection from '@/Components/Navigation/SidebarSection.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import RoleBadge from '@/Components/Navigation/RoleBadge.vue';

defineProps({
    open: { type: Boolean, default: false },
});
const emit = defineEmits(['close']);

const page    = usePage();
const isAdmin = computed(() => page.props.isAdmin);
const roles   = computed(() => page.props.auth?.roles ?? []);
const primaryRole = computed(() => roles.value[0] ?? 'employee');

// Pending request count shared from HandleInertiaRequests
const pendingCount = computed(() => page.props.pending_requests_count ?? 0);
</script>

<template>
    <!-- Mobile overlay -->
    <Transition
        enter-active-class="transition-opacity ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="open"
            class="fixed inset-0 z-30 bg-black/30 lg:hidden"
            @click="emit('close')"
        />
    </Transition>

    <!-- Sidebar panel -->
    <aside
        :class="[
            'fixed top-0 left-0 z-40 h-full w-64 bg-white border-r border-gray-100 flex flex-col transition-transform duration-200 ease-in-out',
            'lg:translate-x-0 lg:static lg:z-auto',
            open ? 'translate-x-0 shadow-xl' : '-translate-x-full',
        ]"
    >
        <!-- Logo + App name -->
        <div class="flex items-center gap-3 h-16 px-5 border-b border-gray-100 shrink-0">
            <Link
                :href="isAdmin ? route('dashboard') : route('employee-dashboard')"
                class="flex items-center gap-2.5 min-w-0"
            >
                <ApplicationLogo />
            </Link>
        </div>

        <!-- User role badge (below logo, above nav) -->
        <div class="px-5 py-3 border-b border-gray-50">
            <RoleBadge :role="primaryRole" />
        </div>

        <!-- Nav links — scrollable -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-4">

            <!-- Dashboard (both roles) -->
            <SidebarSection label="Overview">
                <SidebarLink
                    v-if="isAdmin"
                    :href="route('dashboard')"
                    icon="chart-bar"
                    label="Dashboard"
                    :exact="true"
                />
                <SidebarLink
                    v-else
                    :href="route('employee-dashboard')"
                    icon="chart-bar"
                    label="Dashboard"
                    :exact="true"
                />
            </SidebarSection>

            <!-- Asset Management -->
            <SidebarSection label="Asset Management" :collapsible="true" :default-open="true">
                <!-- All users see the asset list -->
                <SidebarLink
                    :href="route('home')"
                    icon="box"
                    label="All Assets"
                />

                <!-- Admin only -->
                <template v-if="isAdmin">
                    <SidebarLink
                        :href="route('categories.index')"
                        icon="tags"
                        label="Categories"
                    />
                    <SidebarLink
                        :href="route('maintenance.index')"
                        icon="screwdriver-wrench"
                        label="Maintenance"
                    />
                </template>
            </SidebarSection>

            <!-- Request System -->
            <SidebarSection label="Request System" :collapsible="true" :default-open="true">
                <!-- Admin: pending requests with live badge -->
                <SidebarLink
                    v-if="isAdmin"
                    :href="route('asset-requests.index')"
                    icon="clipboard-list"
                    label="Pending Requests"
                    :badge="pendingCount"
                />

                <!-- Employee: request history -->
                <SidebarLink
                    v-else
                    :href="route('asset-requests.create')"
                    icon="plus-circle"
                    label="New Request"
                />

                <!-- Both: request history / log -->
                <SidebarLink
                    :href="route('asset-requests.index')"
                    icon="clock-rotate-left"
                    :label="isAdmin ? 'Request History' : 'My Requests'"
                />

                <!-- Assignments — admin sees all, employee sees own -->
                <SidebarLink
                    :href="route('asset-assignments.index')"
                    icon="link"
                    :label="isAdmin ? 'Assignments' : 'My Assignments'"
                />
            </SidebarSection>

            <!-- User Management (admin only) -->
            <SidebarSection v-if="isAdmin" label="User Management" :collapsible="true" :default-open="false">
                <SidebarLink
                    :href="route('users.index')"
                    icon="users"
                    label="Employees"
                />
                <SidebarLink
                    :href="route('departments.index')"
                    icon="building"
                    label="Departments"
                />
            </SidebarSection>

            <!-- Upload / Tools (placeholder for future) -->
            <!-- <SidebarSection v-if="isAdmin" label="Tools" :collapsible="true" :default-open="false">
                <SidebarLink
                    :href="route('assets.import')"
                    icon="file-arrow-up"
                    label="Upload Asset PDF"
                />
            </SidebarSection> -->

        </nav>

        <!-- Sidebar footer -->
        <div class="px-3 py-3 border-t border-gray-100 shrink-0">
            <p class="text-[10px] text-gray-300 text-center">
                Assetify &copy; {{ new Date().getFullYear() }}
            </p>
        </div>
    </aside>
</template>