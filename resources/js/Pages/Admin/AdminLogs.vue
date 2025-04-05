<script setup>
import { ref, watch, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { debounce } from 'lodash';

defineOptions({
    layout: AuthenticatedLayout,
});

const pageProps = usePage().props;
const pagination = ref(pageProps.logs);
const logsList = ref(pageProps.logs.data ?? []);

const filters = ref({
    search: pageProps.filters?.search ?? '',
    from_date: pageProps.filters?.from_date ?? '',
    to_date: pageProps.filters?.to_date ?? '',
});

watch(
    () => [filters.value.search, filters.value.from_date, filters.value.to_date],
    debounce(() => {
        fetchLogs();
    }, 500)
);

const fetchLogs = (url = route('AdminLogs')) => {
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["logs", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.logs;
            logsList.value = props.logs.data;
        },
    });
};

const goToPage = (url) => url && fetchLogs(url);

const paginationInfo = computed(() => {
    const { from, to, total } = pagination.value;
    return from && to
        ? `Showing ${from} to ${to} of ${total} entries`
        : "No activity logs found";
});

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

const clearFilters = () => {
    filters.value = {
        search: '',
        from_date: '',
        to_date: ''
    };
    fetchLogs();
};
</script>

<template>
    <div class="p-4">
        <h1 class="text-3xl md:text-4xl mb-5 font-bold text-gray-800 border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
            ACTIVITY LOGS
        </h1>

        <div class="flex flex-col md:flex-row md:items-center gap-2 mb-4">
            <div class="relative flex-1 w-full md:w-auto">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search activities..."
                    class="border p-2 pl-4 pr-12 rounded w-full"
                />
                <div class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-700 px-3 py-1 rounded">
                    <i class="fas fa-search"></i>
                </div>
            </div>

            <div class="flex flex-col md:flex-row w-full sm:w-auto gap-2">
                <div class="flex flex-col md:flex-row w-full sm:w-auto">
                    <label class="text-gray-600 text-sm font-semibold mb-1 md:mb-0 md:mr-2">From:</label>
                    <input
                        v-model="filters.from_date"
                        type="date"
                        class="border rounded p-2 focus:ring-2 focus:ring-gray-400 outline-none w-full sm:w-auto"
                    />
                </div>
                <div class="flex flex-col md:flex-row w-full sm:w-auto">
                    <label class="text-gray-600 text-sm font-semibold mb-1 md:mb-0 md:mr-2">To:</label>
                    <input
                        v-model="filters.to_date"
                        type="date"
                        class="border rounded p-2 focus:ring-2 focus:ring-gray-400 outline-none w-full sm:w-auto"
                    />
                </div>
                <button
                    @click="clearFilters"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center justify-center md:justify-start gap-2 hover:bg-gray-400 w-full md:w-auto transition-colors"
                >
                    <i class="fas fa-sync-alt"></i>
                    <span class="inline">Clear</span>
                </button>
            </div>
        </div>

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
