<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import AiInsightsPanel from '@/Components/Dashboard/AiInsightsPanel.vue';
import RecentIssuesTable from '@/Components/Dashboard/RecentIssuesTable.vue';

defineProps({
    stats: Object,
    recent_issues: Array,
    ai_insights: Object,
});
</script>

<template>
    <AppLayout title="Dashboard" noScroll>
        <template #header>Dashboard Overview</template>

        <div class="flex flex-col h-full gap-4 sm:gap-6 min-h-0">
            <!-- Stats Row: Fixed Height Header -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 shrink-0">
                <!-- Main Stat: Total Books -->
                <StatCard :value="stats.total_books" label="Total Books in Library" sublabel="Inventory" color="indigo" size="large">
                    <template #icon>
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </template>
                </StatCard>

                <!-- Stat: Members -->
                <StatCard :value="stats.total_members" label="Total Members" color="emerald">
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </template>
                </StatCard>

                <!-- Stat: Active Issues -->
                <StatCard :value="stats.active_issues" label="Active Loans" color="amber">
                    <template #icon>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </template>
                </StatCard>

                <!-- Revenue Overview (Taking 4th slot instead of being shared) -->
                <div class="bento-card p-5 sm:p-6 bg-linear-to-br from-slate-900/10 to-transparent dark:from-white/5 dark:to-transparent flex flex-col justify-between group overflow-hidden">
                    <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Revenue Loss</span>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white mt-1">Rs. {{ stats.pending_fines }}</h3>
                    <div class="text-[10px] font-bold text-slate-700 dark:text-slate-300 mt-2">Outstanding Fines</div>
                </div>
            </div>

            <!-- Dashboard Split View: Actionable Content Area -->
            <div class="flex-1 grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6 min-h-0">
                <!-- AI Insights Section -->
                <div class="min-h-0">
                    <AiInsightsPanel :insights="ai_insights" class="h-full" />
                </div>

                <!-- Recent Activity Section -->
                <div class="min-h-0 flex flex-col">
                    <RecentIssuesTable v-if="$page.props.auth?.permissions?.can_manage_circulation" :issues="recent_issues" class="h-full" />
                    
                    <!-- Quick Reports (Stacked below table on desktop if space allows, or visible links) -->
                    <div class="mt-4 grid grid-cols-2 gap-3 shrink-0">
                        <a v-if="$page.props.auth?.permissions?.can_view_reports" href="/reports/overdue" target="_blank" class="glass-ghost p-4 rounded-2xl flex items-center gap-3 hover:glow-rose group">
                            <div class="p-2 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-lg group-hover:scale-110 transition-transform">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest">Overdue PDF</span>
                        </a>
                        <a v-if="$page.props.auth?.permissions?.can_view_reports" href="/reports/inventory" target="_blank" class="glass-ghost p-4 rounded-2xl flex items-center gap-3 hover:glow-indigo group">
                            <div class="p-2 bg-slate-500/10 text-slate-600 dark:text-slate-400 rounded-lg group-hover:scale-110 transition-transform">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <span class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest">Inventory PDF</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
