<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    issues: Array,
});

const statusClasses = {
    issued: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
    returned: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
    overdue: 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
};
</script>

<template>
    <div class="sm:col-span-2 xl:col-span-4 glass-card rounded-3xl overflow-hidden mt-2 flex flex-col h-full">
        <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-5 flex items-center justify-between gap-2 border-b border-white/10 dark:border-slate-800/50">
            <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight">Recent Activity</h3>
            <Link href="/issues" class="text-[10px] sm:text-xs font-black text-indigo-600 dark:text-indigo-400 hover:glow-indigo transition-all whitespace-nowrap">VIEW ALL LOGS</Link>
        </div>
        <div class="overflow-x-auto flex-1 overflow-y-auto min-h-0">
            <table class="w-full min-w-160">
                <thead>
                    <tr class="bg-white/5 dark:bg-slate-800/20 sticky top-0 z-10">
                        <th class="px-4 sm:px-6 lg:px-8 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Book</th>
                        <th class="px-4 sm:px-6 lg:px-8 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Member</th>
                        <th class="px-4 sm:px-6 lg:px-8 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Due Date</th>
                        <th class="px-4 sm:px-6 lg:px-8 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-widest leading-none">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 dark:divide-slate-800/30">
                    <tr v-for="issue in issues" :key="issue.id" class="group hover:bg-white/40 dark:hover:bg-white/5 transition-colors">
                        <td class="px-4 sm:px-6 lg:px-8 py-5">
                            <div class="text-sm font-black text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ issue.book.title }}</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">Ref: #{{ issue.id }}</div>
                        </td>
                        <td class="px-4 sm:px-6 lg:px-8 py-5">
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ issue.member.name }}</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">{{ issue.member.member_id }}</div>
                        </td>
                        <td class="px-4 sm:px-6 lg:px-8 py-5">
                            <div class="text-sm font-medium text-slate-500 dark:text-slate-500">{{ new Date(issue.due_date).toLocaleDateString() }}</div>
                        </td>
                        <td class="px-4 sm:px-6 lg:px-8 py-5 text-right">
                            <span class="inline-block px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest"
                                :class="statusClasses[issue.status]">{{ issue.status }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
