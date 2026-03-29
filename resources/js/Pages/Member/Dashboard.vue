<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useFullScreen } from '@/Composables/useFullScreen';

const props = defineProps({
    auth: Object,
    stats: Object,
    recent_loans: Array,
});

const page = usePage();
const settings = computed(() => page.props.settings);
const member = computed(() => props.auth.user);

const { isFullScreen, toggleFullScreen } = useFullScreen();

const typeBadgeClass = (type) => ({
    student: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    teacher: 'bg-violet-500/10 text-violet-400 border-violet-500/20',
    staff: 'bg-slate-500/10 text-slate-400 border-slate-500/20',
}[type] || 'bg-slate-500/10 text-slate-400 border-slate-500/20');
</script>

<template>
    <Head title="Member Dashboard" />

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
                    <Link href="/member/dashboard" class="text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-500 pb-1">Dashboard</Link>
                    <Link href="/member/history" class="hover:text-slate-900 dark:hover:text-white transition-colors">My History</Link>
                    <Link href="/member/fines" class="hover:text-slate-900 dark:hover:text-white transition-colors">Fines</Link>
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
            
            <!-- Welcome Header -->
            <header class="relative py-12 px-10 rounded-[2.5rem] bg-linear-to-br from-indigo-600/20 to-violet-600/10 border border-white/5 overflow-hidden group">
                <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl group-hover:bg-indigo-500/30 transition-all duration-1000"></div>
                <div class="relative z-10">
                    <span class="inline-block px-3 py-1 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-[10px] font-black uppercase tracking-widest mb-4">Personal Dashboard</span>
                    <h2 class="text-4xl md:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-none mb-4">
                        Welcome back, <br/>
                        <span class="text-transparent bg-clip-text bg-linear-to-r from-slate-900 dark:from-white to-slate-500 dark:to-slate-400">{{ member.name }}</span>
                    </h2>
                    <div class="flex flex-wrap gap-4 items-center">
                        <span class="px-3 py-1.5 rounded-full border text-[10px] font-black uppercase tracking-wider" :class="typeBadgeClass(member.type)">
                            {{ member.type }}
                        </span>
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-500 uppercase tracking-widest">ID: {{ member.member_id }}</span>
                    </div>
                </div>
            </header>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="glass-card p-8 rounded-4xl group hover:glow-indigo transition-all duration-500">
                    <div class="flex justify-between items-start mb-6">
                        <div class="p-3 bg-indigo-500/10 rounded-2xl text-indigo-400 group-hover:scale-110 transition-transform">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    </div>
                    <div class="text-4xl font-black text-slate-900 dark:text-white mb-1 group-hover:translate-x-1 transition-transform">{{ stats.current_loans }}</div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-500 mb-4">ACTIVE LOANS</div>
                    <div class="h-1.5 w-full bg-slate-900 rounded-full overflow-hidden">
                        <div class="h-full bg-linear-to-r from-indigo-500 to-violet-500" :style="{ width: (stats.current_loans / 5 * 100) + '%' }"></div>
                    </div>
                </div>

                <div class="glass-card p-8 rounded-4xl group hover:glow-emerald transition-all duration-500">
                    <div class="flex justify-between items-start mb-6">
                        <div class="p-3 bg-emerald-500/10 rounded-2xl text-emerald-400 group-hover:scale-110 transition-transform">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="text-4xl font-black text-white mb-1 group-hover:translate-x-1 transition-transform">{{ stats.total_borrowed }}</div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500">BORROWING HISTORY</div>
                </div>

                <div class="glass-card p-8 rounded-4xl group hover:glow-rose transition-all duration-500">
                    <div class="flex justify-between items-start mb-6">
                        <div class="p-3 bg-rose-500/10 rounded-2xl text-rose-400 group-hover:scale-110 transition-transform">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="text-4xl font-black text-white mb-1 group-hover:translate-x-1 transition-transform">{{ settings.currency_symbol }} {{ stats.unpaid_fines }}</div>
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500">OUTSTANDING FINES</div>
                </div>
            </div>

            <!-- Recent Activity Table -->
            <section class="glass-card rounded-[2.5rem] overflow-hidden">
                <div class="px-8 py-6 border-b border-white/5 flex items-center justify-between">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">Current Borrowings</h3>
                    <Link href="/member/history" class="text-[10px] font-black uppercase tracking-widest text-indigo-400 hover:text-indigo-300 transition-colors">View All</Link>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-white/5">
                            <tr class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                                <th class="px-8 py-4">Resource</th>
                                <th class="px-8 py-4 text-center">Issued Date</th>
                                <th class="px-8 py-4 text-center">Due Date</th>
                                <th class="px-8 py-4 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr v-for="loan in recent_loans" :key="loan.id" class="group hover:bg-white/5 transition-all">
                                <td class="px-8 py-5">
                                    <div class="font-black text-sm text-white group-hover:text-indigo-400 transition-colors">{{ loan.book.title }}</div>
                                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ loan.book.author }}</div>
                                </td>
                                <td class="px-8 py-5 text-center text-xs font-bold text-slate-400">
                                    {{ new Date(loan.issued_at).toLocaleDateString() }}
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="px-2 py-1 rounded bg-slate-900 border border-slate-800 text-[10px] font-black uppercase tracking-tight" :class="new Date(loan.due_date) < new Date() ? 'text-rose-500' : 'text-emerald-500'">
                                        {{ new Date(loan.due_date).toLocaleDateString() }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <span class="px-2 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border" :class="loan.status === 'issued' ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'">
                                        {{ loan.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="recent_loans.length === 0">
                                <td colspan="4" class="px-8 py-20 text-center text-slate-500 uppercase tracking-widest font-black text-xs opacity-50">
                                    No active borrowings found
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
    @apply bg-white/5 border border-white/10 backdrop-blur-xl shadow-2xl;
}
.glow-indigo {
    @apply shadow-[0_0_40px_-10px_rgba(99,102,241,0.3)];
}
.glow-emerald {
    @apply shadow-[0_0_40px_-10px_rgba(16,185,129,0.3)];
}
.glow-rose {
    @apply shadow-[0_0_40px_-10px_rgba(244,63,94,0.3)];
}
.rounded-4xl {
    border-radius: 2rem;
}
</style>
