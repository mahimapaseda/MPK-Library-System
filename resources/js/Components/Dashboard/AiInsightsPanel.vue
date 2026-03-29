<script setup>
defineProps({
    insights: Object,
});

const severityClasses = {
    high: { border: 'border-rose-300/50 dark:border-rose-500/30 bg-rose-500/5', text: 'text-rose-500' },
    medium: { border: 'border-amber-300/50 dark:border-amber-500/30 bg-amber-500/5', text: 'text-amber-500' },
    low: { border: 'border-emerald-300/50 dark:border-emerald-500/30 bg-emerald-500/5', text: 'text-emerald-500' },
};

const riskClasses = {
    low: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    medium: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
    high: 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
};
</script>

<template>
    <div class="sm:col-span-2 xl:col-span-4 glass-card rounded-3xl overflow-hidden flex flex-col h-full">
        <!-- Header -->
        <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-5 border-b border-white/10 dark:border-slate-800/50 flex items-center justify-between gap-2">
            <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight">AI Insights</h3>
            <span class="inline-block px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest"
                :class="riskClasses[insights.summary.risk_level]">
                {{ insights.summary.risk_level }} risk
            </span>
        </div>

        <div class="p-4 sm:p-6 lg:p-8 grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6 flex-1 overflow-y-auto min-h-0">
            <!-- Prediction Snapshot -->
            <div class="xl:col-span-1 p-5 rounded-2xl bg-white/50 dark:bg-slate-900/40 border border-white/20 dark:border-slate-700/30">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Prediction Snapshot</div>
                <div class="mt-3 space-y-2">
                    <div class="text-sm font-bold text-slate-700 dark:text-slate-300">Overdue Rate: <span class="text-slate-900 dark:text-white">{{ insights.summary.overdue_rate }}%</span></div>
                    <div class="text-sm font-bold text-slate-700 dark:text-slate-300">Pending Fines: <span class="text-slate-900 dark:text-white">Rs. {{ insights.summary.pending_fine_amount }}</span></div>
                    <div class="text-sm font-bold text-slate-700 dark:text-slate-300">New Fines (30d): <span class="text-slate-900 dark:text-white">{{ insights.summary.new_fines_last_30_days }}</span></div>
                    <div class="text-sm font-bold text-slate-700 dark:text-slate-300">Low-Stock Titles: <span class="text-slate-900 dark:text-white">{{ insights.summary.low_stock_titles }}</span></div>
                </div>
            </div>

            <!-- Alerts & Recommendations -->
            <div class="xl:col-span-2 p-5 rounded-2xl bg-white/50 dark:bg-slate-900/40 border border-white/20 dark:border-slate-700/30">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Automated Alerts</div>
                <div class="space-y-3 mb-5">
                    <div v-for="(alert, idx) in insights.alerts" :key="`alert-${idx}`"
                        class="p-3 rounded-xl border"
                        :class="severityClasses[alert.severity]?.border">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-sm font-black text-slate-800 dark:text-white">{{ alert.title }}</div>
                            <span class="text-[10px] font-black uppercase tracking-widest"
                                :class="severityClasses[alert.severity]?.text">{{ alert.severity }}</span>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ alert.detail }}</div>
                    </div>
                </div>

                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Recommended Actions</div>
                <div class="space-y-3">
                    <div v-for="(item, idx) in insights.recommendations" :key="idx"
                        class="p-3 rounded-xl border border-white/20 dark:border-slate-700/30">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-sm font-black text-slate-800 dark:text-white">{{ item.title }}</div>
                            <span class="text-[10px] font-black uppercase tracking-widest"
                                :class="severityClasses[item.priority]?.text">{{ item.priority }}</span>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ item.detail }}</div>
                    </div>
                </div>
            </div>

            <!-- High-Demand Books -->
            <div class="xl:col-span-3 p-5 rounded-2xl bg-white/50 dark:bg-slate-900/40 border border-white/20 dark:border-slate-700/30">
                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">High-Demand Books (Low Stock)</div>
                <div v-if="insights.high_demand_books.length === 0" class="text-sm text-slate-500 dark:text-slate-400">No critical stock-outs detected right now.</div>
                <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div v-for="book in insights.high_demand_books" :key="book.id" class="p-3 rounded-xl border border-white/20 dark:border-slate-700/30">
                        <div class="text-sm font-black text-slate-800 dark:text-white">{{ book.title }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ book.author }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
