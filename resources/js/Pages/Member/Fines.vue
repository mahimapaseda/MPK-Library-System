<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useFullScreen } from '@/Composables/useFullScreen';

const props = defineProps({
    auth: Object,
    fines: Array,
});

const page = usePage();
const settings = computed(() => page.props.settings);
const member = computed(() => props.auth.user);
const totalUnpaid = computed(() => props.fines.filter(f => f.status === 'unpaid').reduce((sum, f) => sum + parseFloat(f.amount), 0));

const { isFullScreen, toggleFullScreen } = useFullScreen();
</script>

<template>
    <Head title="My Fines" />

    <div class="min-h-screen bg-transparent text-slate-700 dark:text-slate-200 selection:bg-indigo-500/30 selection:text-indigo-200 font-sans p-6 md:p-12">
        
        <!-- Navigation -->
        <nav class="max-w-7xl mx-auto mb-12 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-2xl bg-linear-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow-2xl shadow-indigo-500/40 glow-indigo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black tracking-tight text-slate-900 dark:text-white uppercase">{{ settings.library_name || 'Library' }} <span class="text-indigo-500">Member</span></h1>
                    <p class="text-[10px] font-bold text-slate-600 dark:text-slate-500 uppercase tracking-widest">Personal Resource Hub</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden md:flex gap-8 text-[11px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">
                    <Link href="/member/dashboard" class="hover:text-slate-900 dark:hover:text-white transition-colors">Dashboard</Link>
                    <Link href="/member/history" class="hover:text-slate-900 dark:hover:text-white transition-colors">My History</Link>
                    <Link href="/member/fines" class="text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-500 pb-1">Fines</Link>
                </div>
                <button @click="toggleFullScreen" class="p-2 rounded-xl bg-white/50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-white hover:bg-white dark:hover:bg-slate-800 transition-all active:scale-95" :title="isFullScreen ? 'Exit Full Screen' : 'Enter Full Screen'">
                    <svg v-if="!isFullScreen" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V4m0 5H4m0 0l5-5M15 9h5m0 0l-5-5m5 5v5m0 6v-5m0 5h-5m5 0l-5-5M9 15H4m0 0l5 5M9 15v5"/>
                    </svg>
                </button>
                <Link method="post" as="button" href="/logout" class="px-5 py-2.5 bg-white/50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-800 transition-all active:scale-95">Sign Out</Link>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto space-y-12">
            <!-- Header -->
            <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-white/5">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-rose-500 mb-2 block">Finance</span>
                    <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight">Outstanding Fines</h2>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-500 uppercase tracking-widest mt-2">Manage your overdue penalties and payment status</p>
                </div>
                <div class="flex gap-4">
                    <div class="px-8 py-4 bg-rose-500/10 border border-rose-500/20 rounded-4xl text-center glow-rose">
                        <div class="text-2xl font-black text-rose-500 dark:text-rose-400 leading-none">{{ settings.currency_symbol }} {{ totalUnpaid.toFixed(2) }}</div>
                        <div class="text-[8px] font-black text-slate-600 dark:text-slate-500 uppercase tracking-widest mt-1">Total Due</div>
                    </div>
                </div>
            </header>

            <!-- Fines Table -->
            <section class="glass-card rounded-[2.5rem] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-white/10 dark:bg-white/6">
                            <tr class="text-[10px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-500">
                                <th class="px-8 py-4">Linked Resource</th>
                                <th class="px-8 py-4 text-center">Amount ({{ settings.currency_symbol }})</th>
                                <th class="px-8 py-4 text-center">Generation Date</th>
                                <th class="px-8 py-4 text-right">Payment Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/8">
                            <tr v-for="fine in fines" :key="fine.id" class="group hover:bg-white/2 transition-all">
                                <td class="px-8 py-6">
                                    <template v-if="fine.book_issue?.book">
                                        <div class="font-black text-sm text-slate-800 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ fine.book_issue.book.title }}</div>
                                        <div class="text-[10px] font-bold text-slate-600 dark:text-slate-500 uppercase tracking-widest">Returned Late</div>
                                    </template>
                                    <template v-else>
                                        <div class="font-black text-sm text-slate-800 dark:text-white">General Fine</div>
                                    </template>
                                </td>
                                <td class="px-8 py-6 text-center text-sm font-black text-slate-800 dark:text-white">
                                    {{ parseFloat(fine.amount).toFixed(2) }}
                                </td>
                                <td class="px-8 py-6 text-center text-xs font-bold text-slate-500 dark:text-slate-400">
                                    {{ new Date(fine.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border" :class="fine.status === 'unpaid' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'">
                                        {{ fine.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="fines.length === 0">
                                <td colspan="4" class="px-8 py-20 text-center text-slate-500 uppercase tracking-widest font-black text-xs opacity-50">
                                    Clean record! No fines found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>
</template>

<style scoped>
@reference "../../../css/app.css";

.glass-card {
    @apply bg-white/5 dark:bg-white/5 border border-white/15 dark:border-white/10 backdrop-blur-xl shadow-2xl;
}
.glow-indigo {
    @apply shadow-[0_0_40px_-10px_rgba(99,102,241,0.3)];
}
.glow-rose {
    @apply shadow-[0_0_40px_-10px_rgba(244,63,94,0.3)];
}
</style>
