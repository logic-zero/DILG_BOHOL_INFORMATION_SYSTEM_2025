<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineOptions({
    layout: AuthenticatedLayout,
});

const pageProps = usePage().props;
const pagination = ref(pageProps.logs);
const logsList = ref(pageProps.logs.data ?? []);

const paginationInfo = computed(() => {
    const { from, to, total } = pagination.value;
    return from && to
        ? `Showing ${from} to ${to} of ${total} entries`
        : "No activity logs found";
});

const goToPage = (url) => {
    if (url) {
        router.get(url, {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: ({ props }) => {
                pagination.value = props.logs;
                logsList.value = props.logs.data;
            },
        });
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <div class="p-4">
        <h1 class="text-3xl md:text-4xl mb-5 font-bold text-gray-800 border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
            ACTIVITY LOGS
        </h1>

        <div class="overflow-x-auto hidden xl:block">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase tracking-wider">
                        <th class="p-3 text-left w-[20%]">Date</th>
                        <th class="p-3 text-left w-[80%]">Activity</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in logsList" :key="log.id" class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-600">
                            {{ formatDate(log.created_at) }}
                        </td>
                        <td class="p-3 text-gray-600">
                            <span class="capitalize">{{ log.description }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="block xl:hidden space-y-4">
            <div v-for="log in logsList" :key="log.id" class="border rounded-lg shadow-md bg-gray-100 p-4">
                <p class="text-sm font-bold text-gray-700 mb-2">{{ formatDate(log.created_at) }}</p>
                <p class="text-gray-800 capitalize">
                    {{ log.description }}
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center mt-6 text-gray-700">
            <span>{{ paginationInfo }}</span>
            <div class="flex flex-wrap space-x-2 mt-2 sm:mt-0">
                <button
                    v-for="(link, index) in pagination.links"
                    :key="index"
                    @click="goToPage(link.url)"
                    v-html="link.label"
                    :class="{
                        'font-bold bg-blue-300 text-gray-900': link.active,
                        'text-gray-400 cursor-not-allowed pointer-events-none': !link.url,
                    }"
                    class="px-4 py-1 border border-gray-300 hover:bg-gray-200 transition"
                    :disabled="!link.url"
                ></button>
            </div>
        </div>
    </div>
</template>
