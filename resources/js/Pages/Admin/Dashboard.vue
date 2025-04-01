<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import TotalCountCard from "@/Components/TotalCountCard.vue";
import PageVisitCard from "@/Components/PageVisitCard.vue";
import PageVisitsChart from "@/Components/PageVisitsChart.vue";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    newsStats: Object,
    organizational_structure: Number,
    pdmu: Number,
    field_officers: Number,
    lgu: Number,
    faq: Number,
    issuances: Number,
    prov_officials: Number,
    citizens_charter: Number,
    users: Number,
    pageVisits: Object,
    knowledge_materials: Object,
    jobs: Object,
});
</script>

<template>
    <Head title="Dashboard" />

    <h1 class="text-3xl md:text-4xl mt-4 mb-2 font-bold text-gray-800 border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
        DASHBOARD
    </h1>

    <div class="mx-auto px-4 py-6 flex flex-col xl:flex-row gap-4">
        <!-- page visit section -->
        <div class="xl:max-w-[600px] xl:min-w-[400px] flex-1 min-w-0">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <PageVisitCard
                    title="Today's Visits"
                    :count="pageVisits.today"
                    icon="fas fa-calendar-day"
                    bg-color="bg-white"
                    border-color="border-blue-900 border"
                    text-color="text-blue-950"
                />

                <PageVisitCard
                    title="This Week"
                    :count="pageVisits.thisWeek"
                    icon="fas fa-calendar-week"
                    bg-color="bg-white"
                    border-color="border-blue-900 border"
                    text-color="text-blue-950"
                />

                <PageVisitCard
                    title="This Month"
                    :count="pageVisits.thisMonth"
                    icon="fas fa-calendar-alt"
                    bg-color="bg-white"
                    border-color="border-blue-900 border"
                    text-color="text-blue-950"
                />

                <PageVisitCard
                    title="Total Visits"
                    :count="pageVisits.total"
                    icon="fas fa-chart-line"
                    bg-color="bg-white"
                    border-color="border-blue-900 border"
                    text-color="text-blue-950"
                />
            </div>

            <div class="bg-white p-4 rounded-lg border shadow-sm border-blue-900 xl:min-h-[350px]">
                <h3 class="text-sm text-blue-950 uppercase font-semibold mb-2">Page Visits (Last 10 Days)</h3>
                <PageVisitsChart
                    :labels="pageVisits.graph.labels"
                    :data="pageVisits.graph.data"
                />
            </div>
        </div>

        <!-- other cards section-->
        <div class="flex-1">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 justify-items-center">
                <!-- Row 1 -->
                <TotalCountCard
                    title="Total News"
                    :count="newsStats.total"
                    icon="fas fa-newspaper"
                    color="indigo"
                />
                <TotalCountCard
                    title="Approved News"
                    :count="newsStats.approved"
                    icon="fas fa-check-circle"
                    color="green"
                />
                <TotalCountCard
                    title="Pending News"
                    :count="newsStats.pending"
                    icon="fas fa-clock"
                    color="yellow"
                />

                <!-- Row 2 -->
                <TotalCountCard
                    title="Organizational Structure"
                    :count="organizational_structure"
                    icon="fas fa-sitemap"
                    color="fuchsia"
                />
                <TotalCountCard
                    title="PDMUs"
                    :count="pdmu"
                    icon="fas fa-user-friends"
                    color="pink"
                />
                <TotalCountCard
                    title="Field Officers"
                    :count="field_officers"
                    icon="fas fa-users"
                    color="violet"
                />

                <!-- Row 3 -->
                <TotalCountCard
                    title="LGUs"
                    :count="lgu"
                    icon="fas fa-city"
                    color="red"
                />
                <TotalCountCard
                    title="FAQs"
                    :count="faq"
                    icon="fas fa-question-circle"
                    color="orange"
                />
                <TotalCountCard
                    title="Issuances"
                    :count="issuances"
                    icon="fas fa-file-alt"
                    color="amber"
                />

                <!-- Row 4 -->
                <TotalCountCard
                    title="Provincial Officials"
                    :count="prov_officials"
                    icon="fas fa-user-tie"
                    color="lime"
                />
                <TotalCountCard
                    title="Citizens Charter"
                    :count="citizens_charter"
                    icon="fas fa-file-signature"
                    color="emerald"
                />
                <TotalCountCard
                    title="Users"
                    :count="users"
                    icon="fas fa-user-cog"
                    color="teal"
                />

                <!-- Row 5 -->
                <TotalCountCard
                    title="Downloadables"
                    :count="prov_officials"
                    icon="fas fa-download"
                    color="cyan"
                />
                <TotalCountCard
                    title="Knowledge Materials"
                    :count="knowledge_materials"
                    icon="fas fa-book"
                    color="violet"
                />
                <TotalCountCard
                    title="Job Vacancies"
                    :count="jobs"
                    icon="fas fa-briefcase"
                    color="purple"
                />
            </div>
        </div>
    </div>
</template>
