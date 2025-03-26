<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center px-4 relative">
        <Link href="/" class="absolute top-4 left-4 text-blue-900 font-bold hover:text-blue-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Go to Homepage
        </Link>
        <div class="w-full max-w-md space-y-2">
            <div class="text-center">
                <div class="flex justify-center items-center mb-2">
                    <img src="/img/dilg-main.png" class="rounded-full h-[125px] w-[125px] shadow-lg" alt="bohol-seal">
                </div>
                <h2 class="text-xl font-extrabold text-gray-900 whitespace-nowrap">Department of the Interior and Local Government</h2>
                <p class="mt-1 text-md font-semibold text-gray-800">
                    Bohol Province
                </p>
            </div>

            <div v-if="status" class="p-4 rounded-md bg-green-50 text-green-600 text-center">
                {{ status }}
            </div>

            <div class="bg-white p-6 shadow-lg rounded-lg">
                <p class="text-center mb-2 font-semibold text-sm text-gray-600">
                    Sign in to your account
                </p>
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" value="Email address" class="sr-only" />
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full px-4 py-3 rounded-md border border-gray-300 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Email address"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <InputError class="mt-1 text-sm text-red-600" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" class="sr-only" />
                        <TextInput
                            id="password"
                            type="password"
                            class="block w-full px-4 py-3 rounded-md border border-gray-300 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Password"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                        />
                        <InputError class="mt-1 text-sm text-red-600" :message="form.errors.password" />
                    </div>

                    <div>
                        <PrimaryButton
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            :class="{ 'opacity-50': form.processing }"
                            :disabled="form.processing"
                        >
                            Sign in
                        </PrimaryButton>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>

                        <!-- <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-blue-600 hover:text-blue-500"
                        >
                            Forgot password?
                        </Link> -->
                    </div>
                </form>
            </div>
            <p class="text-xs text-center text-gray-500 mt-4">&copy; DILG-BOHOL PROVINCE 2023</p>
        </div>
    </div>
    <div class="bg"></div>
    <div class="bg bg2"></div>
    <div class="bg bg3"></div>
</template>

<style scoped>
.bg {
    animation: slide 20s ease-in-out infinite alternate;
    background-image: linear-gradient(-60deg, rgb(226, 217, 217) 50%, white 50%);
    bottom: 0;
    left: -50%;
    opacity: 0.5;
    position: fixed;
    right: -50%;
    top: 0;
    z-index: -1;
    will-change: transform;
}

.bg2 {
    animation-direction: alternate-reverse;
    animation-duration: 25s;
}

.bg3 {
    animation-duration: 30s;
}

@keyframes slide {
    0% {
        transform: translate3d(-25%, 0, 0);
    }
    100% {
        transform: translate3d(25%, 0, 0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .bg, .bg2, .bg3 {
        animation: none !important;
    }
}
</style>
