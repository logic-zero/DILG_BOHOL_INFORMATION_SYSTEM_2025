<script setup>
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    pdMessage: {
        type: Object,
    },
});

const user = usePage().props.auth.user;
const showImageModal = ref(false);
const showPDMessageModal = ref(false);
const imagePreview = ref(null);
const fileInput = ref(null);

const profileForm = useForm({
    name: user.name,
    email: user.email,
    _method: 'patch'
});

const imageForm = useForm({
    profile_image: null,
    _method: 'patch'
});

const pdMessageForm = useForm({
    message: usePage().props.pdMessage?.message || '',
    _method: 'post'
});

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        imageForm.profile_image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
            showImageModal.value = true;
        };
        reader.readAsDataURL(file);
    }
};

const resetImage = () => {
    imageForm.profile_image = null;
    imagePreview.value = null;
    showImageModal.value = false;
};

const triggerFileInput = () => {
    fileInput.value.click();
};

const updateProfileImage = () => {
    imageForm.post(route('profile.image.update'), {
        preserveScroll: true,
        onSuccess: () => {
            window.location.reload();
        }
    });
};

const submitPDMessage = () => {
    pdMessageForm.post(route('profile.PDMessage'), {
        preserveScroll: true,
        onSuccess: () => {
            showPDMessageModal.value = false;
        }
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form @submit.prevent="profileForm.post(route('profile.update'), {
            preserveScroll: true
        })" class="mt-6">
            <div class="flex flex-col md:flex-row gap-8">
                <div class="w-full md:w-1/2 space-y-6">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="profileForm.name"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <InputError class="mt-2" :message="profileForm.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="profileForm.email"
                            required
                            autocomplete="username"
                        />
                        <InputError class="mt-2" :message="profileForm.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && user.email_verified_at === null">
                        <p class="mt-2 text-sm text-gray-800">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>
                        <div
                            v-show="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex justify-between items-center gap-4">
                        <PrimaryButton :disabled="profileForm.processing">Save</PrimaryButton>
                        <PrimaryButton
                            v-if="user.position.toLowerCase() === 'provincial director'"
                            @click="showPDMessageModal = true"
                            type="button"
                        >
                            PD Message
                        </PrimaryButton>
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-if="profileForm.recentlySuccessful"
                                class="text-sm text-gray-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </div>

                <div class="w-full md:w-1/2">
                    <div class="flex flex-col items-center">
                        <InputLabel value="Profile Photo" class="mb-2" />
                        <label class="relative group cursor-pointer">
                            <div class="h-40 w-40 rounded-full overflow-hidden border border-black group-hover:border-indigo-500 transition-all duration-200 flex items-center justify-center">
                                <img
                                    v-if="user.profile_image"
                                    :src="`/profile_images/${user.profile_image}`"
                                    class="h-full w-full object-cover"
                                />
                                <div v-else class="h-full w-full bg-gray-200 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <div class="absolute inset-0 bg-black rounded-full bg-opacity-30 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                                    <span class="text-white font-medium">Change Photo</span>
                                </div>
                            </div>
                            <input
                                ref="fileInput"
                                id="profile_image"
                                type="file"
                                class="hidden"
                                @change="handleImageChange"
                                accept="image/*"
                            />
                        </label>
                        <InputError class="mt-2" :message="imageForm.errors.profile_image" />
                        <p class="text-sm text-gray-500 mt-2">Click image to change</p>
                    </div>
                </div>
            </div>
        </form>

        <div v-if="showImageModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg mx-4">
                <h2 class="text-xl mb-4 text-center">Update Profile Photo</h2>

                <div class="flex justify-center mb-6">
                    <div class="w-48 h-48 rounded-full overflow-hidden border border-black">
                        <img
                            :src="imagePreview"
                            class="h-full w-full object-cover"
                            alt="Preview"
                        />
                    </div>
                </div>

                <div class="flex justify-center gap-2">
                    <SecondaryButton @click="triggerFileInput">
                        Change Image
                    </SecondaryButton>
                    <PrimaryButton
                        @click="updateProfileImage"
                        :disabled="imageForm.processing"
                    >
                        Save Photo
                    </PrimaryButton>
                </div>

                <div class="flex justify-center mt-4">
                    <button @click="resetImage" class="text-gray-600 hover:text-gray-800 text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <div v-if="showPDMessageModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-2xl mx-4">
                <h2 class="text-xl mb-4 text-center">Provincial Director's Message</h2>

                <div class="mb-6">
                    <InputLabel for="message" value="Message" />
                    <textarea
                        id="message"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 h-96"
                        v-model="pdMessageForm.message"
                        required
                    ></textarea>
                    <InputError class="mt-2" :message="pdMessageForm.errors.message" />
                </div>

                <div class="flex justify-center gap-2">
                    <SecondaryButton @click="showPDMessageModal = false">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        @click="submitPDMessage"
                        :disabled="pdMessageForm.processing"
                    >
                        Save Message
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </section>
</template>
