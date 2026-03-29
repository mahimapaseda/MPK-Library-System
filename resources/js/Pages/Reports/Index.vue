<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AnalyticsChart from '@/Components/Reports/AnalyticsChart.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    analytics: {
        type: Object,
        required: true
    }
});

const reports = [
    {
        title: 'Overdue Books',
        description: 'Comprehensive list of all books past due, including member contact details and fine calculations.',
        href: '/reports/overdue',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        color: 'rose'
    },
    {
        title: 'Inventory Summary',
        description: 'Overview of stock levels categorized by genre, including total vs available quantities.',
        href: '/reports/inventory',
        icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        color: 'indigo'
    },
    {
        title: 'AI Strategy Report',
        description: 'Actionable library intelligence report including risk alerts and strategic recommendations.',
        href: '/reports/ai-strategy',
        icon: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
        color: 'violet'
    }
];

const distributionData = computed(() => ({
    labels: props.analytics.distribution.map(d => d.name),
    datasets: [{
        data: props.analytics.distribution.map(d => d.count),
        backgroundColor: [
            '#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4', '#f43f5e'
        ],
        borderWidth: 0,
        hoverOffset: 10
    }]
}));

const trendsData = computed(() => ({
    labels: props.analytics.trends.map(t => t.date),
    datasets: [
        {
            label: 'Issues',
            data: props.analytics.trends.map(t => t.issued),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true,
            tension: 0.4
        },
        {
            label: 'Returns',
            data: props.analytics.trends.map(t => t.returned),
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            tension: 0.4
        }
    ]
}));

const revenueData = computed(() => ({
    labels: ['Paid', 'Pending'],
    datasets: [{
        data: [props.analytics.revenue.paid, props.analytics.revenue.pending],
        backgroundColor: ['#10b981', '#f43f5e'],
        borderWidth: 0
    }]
}));

const getColorClasses = (color) => {
    const themes = {
        rose: 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
        indigo: 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400',
        violet: 'bg-violet-500/10 text-violet-600 dark:text-violet-400'
    };
    return themes[color] || themes.indigo;
};
</script>

<template>
    <AppLayout title="Reports Hub">
        <template #header>Reports & Analytics Dashboard</template>

        <div class="space-y-6 pb-12">
            <!-- Analytics Summary Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Doughnut Insight: Collection Balance -->
                <div class="lg:col-span-2 glass-card p-8 flex flex-col sm:flex-row items-center gap-8 group">
                    <div class="w-full sm:w-1/2">
                        <div class="text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-4">Collection Distribution</div>
                        <AnalyticsChart type="doughnut" :data="distributionData" :height="200" :options="{ 
                            cutout: '70%',
                            plugins: { legend: { display: true, position: 'right', labels: { boxWidth: 10, padding: 15 } } }
                        }" />
                    </div>
                    <div class="w-full sm:w-1/2 space-y-4">
                        <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight leading-tight">Catalog <br> Composition</h3>
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-400 leading-relaxed">
                            A comparative overview of your library's holdings categorized by genre, highlighting the most represented literary domains.
                        </p>
                    </div>
                </div>

                <!-- Pie Insight: Fine Revenue -->
                <div class="glass-card p-8 flex flex-col justify-between">
                    <div>
                        <div class="text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-4">Revenue Status</div>
                        <AnalyticsChart type="pie" :data="revenueData" :height="150" />
                    </div>
                    <div class="mt-6">
                        <div class="flex items-end justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                            <span class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest">Total Value</span>
                            <span class="text-lg font-black text-slate-800 dark:text-white">Rs. {{ analytics.revenue.paid + analytics.revenue.pending }}</span>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Paid: Rs. {{ analytics.revenue.paid }}</span>
                            <span class="text-[10px] font-bold text-rose-500 uppercase tracking-widest">Pending: Rs. {{ analytics.revenue.pending }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trends Row: Usage Analysis -->
            <div class="glass-card p-8 sm:p-10">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8">
                    <div>
                        <div class="text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest">Circulation Trends</div>
                        <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight mt-1">Activity Analysis (7 Days)</h3>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-indigo-500"></div>
                            <span class="text-[10px] font-black text-slate-700 dark:text-slate-400 uppercase tracking-widest">Issues</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                            <span class="text-[10px] font-black text-slate-700 dark:text-slate-400 uppercase tracking-widest">Returns</span>
                        </div>
                    </div>
                </div>
                <AnalyticsChart type="line" :data="trendsData" :height="300" />
            </div>

            <!-- Registry Center: PDF Reports -->
            <div>
                <div class="text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest mb-4 px-2">Export Registry</div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="report in reports" :key="report.href"
                        class="glass-ghost p-6 rounded-3xl group relative overflow-hidden flex flex-col justify-between h-48 sm:h-auto">
                        <div class="flex items-start justify-between gap-4">
                            <div :class="['p-3 rounded-xl transition-transform group-hover:scale-110 duration-500', getColorClasses(report.color)]">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="report.icon" />
                                </svg>
                            </div>
                            <a :href="report.href" target="_blank" 
                                class="p-2 text-slate-400 hover:text-indigo-500 transition-colors">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" /></svg>
                            </a>
                        </div>
                        <div class="mt-4">
                            <h3 class="text-sm font-black text-slate-800 dark:text-white">{{ report.title }}</h3>
                            <p class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest mt-1">Portable Format (PDF)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
