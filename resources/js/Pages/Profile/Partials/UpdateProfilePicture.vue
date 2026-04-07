<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';


const page = usePage();
const user = page.props.auth.user


// Ref to directly access the hidden file input element
const photoInput = ref(null);

// Ref to hold the local image preview URL
const photoPreview = ref(user.img_path ? '/storage/' + user.img_path : avatarUrl(user.name));

const form = useForm({
    img_path: null,
    _method: 'PATCH', // Fake the PATCH request for Laravel routing
});

function avatarUrl(name) {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&color=ffffff&background=DC143C`;
}


const updateProfilePicture = () => {
    form.post(route('profile.update-picture'), {
        preserveScroll: true,
    });
};

const updatePhotoPreview = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Assign the file to the form
    form.img_path = file;

    // Create a local preview URL
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};


</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Update Profile Picture
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Ensure your account is using a clear and appropriate photo.
            </p>
        </header>

        <form @submit.prevent="updateProfilePicture" class="mt-6 space-y-6">
            <div>
                <input
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    @change="updatePhotoPreview"
                >

                <div class="flex items-center gap-4">
                    <div class="h-20 w-20 rounded-full overflow-hidden bg-gray-100 border border-gray-200 flex-shrink-0">
                        <img
                            v-if="photoPreview"
                            :src="photoPreview"
                            alt="Preview"
                            class="h-full w-full object-cover"
                        >
                    </div>
                    
                    <button
                        type="button"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        @click="photoInput.click()"
                    >
                        Select A New Photo
                    </button>
                </div>

                <InputError :message="form.errors.img_path" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing || !form.img_path">
                    Save
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.processing" class="text-sm text-gray-600">
                        Saving...
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>