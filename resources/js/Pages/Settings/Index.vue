<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object
});

const form = useForm({
    settings: {
        library_name: props.settings?.library_name ?? '',
        library_address: props.settings?.library_address ?? '',
        library_phone: props.settings?.library_phone ?? '',
        library_email: props.settings?.library_email ?? '',
        fine_per_day: props.settings?.fine_per_day ?? 5,
        grace_period_days: props.settings?.grace_period_days ?? 0,
        loan_duration_days: props.settings?.loan_duration_days ?? 14,
        max_books_per_member: props.settings?.max_books_per_member ?? 3,
        reservation_expiry_days: props.settings?.reservation_expiry_days ?? 2,
        currency_symbol: props.settings?.currency_symbol ?? 'LKR',
        timezone: props.settings?.timezone ?? 'UTC+5:30',
        allow_registration: props.settings?.allow_registration ?? '1',
        maintenance_mode: props.settings?.maintenance_mode ?? '0',
    }
});

const submit = () => {
    form.post('/settings', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Settings">
        <template #header>
            Library Settings
        </template>

        <div class="max-w-3xl">
            <div class="glass-card rounded-[2.5rem] p-10 border-white/20 dark:border-slate-800/50 shadow-2xl">
                <div class="mb-10">
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">System Configuration</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Core Library Operational Parameters</p>
                </div>

                <form @submit.prevent="submit" class="space-y-12">
                    <!-- Section: Institutional Identity -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-8 w-8 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <h4 class="text-sm font-black text-slate-700 dark:text-slate-200 uppercase tracking-widest">Institutional Identity</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2 space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Library Display Name</label>
                                <input v-model="form.settings.library_name" type="text"
                                    class="w-full px-6 py-4 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Physical Location / Address</label>
                                <textarea v-model="form.settings.library_address" rows="2"
                                    class="w-full px-6 py-4 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all"></textarea>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Contact Terminal (Phone)</label>
                                <input v-model="form.settings.library_phone" type="text"
                                    class="w-full px-6 py-4 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Official Support Email</label>
                                <input v-model="form.settings.library_email" type="email"
                                    class="w-full px-6 py-4 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                            </div>
                        </div>
                    </div>

                    <!-- Section: Circulation Policy -->
                    <div class="space-y-6 pt-8 border-t border-white/10 dark:border-slate-800/30">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-8 w-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <h4 class="text-sm font-black text-slate-700 dark:text-slate-200 uppercase tracking-widest">Circulation & Lending</h4>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Fine Rate (Per Day)</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-[10px]">{{ form.settings.currency_symbol }}</span>
                                    <input v-model="form.settings.fine_per_day" type="number" step="0.01"
                                        class="w-full pl-16 pr-6 py-4 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-black text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Fine Grace Window</label>
                                <div class="relative">
                                    <input v-model="form.settings.grace_period_days" type="number"
                                        class="w-full px-6 py-4 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-black text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                                    <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-[10px] uppercase tracking-widest">Days</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Loan Window Duration</label>
                                <div class="relative">
                                    <input v-model="form.settings.loan_duration_days" type="number"
                                        class="w-full px-6 py-4 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-black text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                                    <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-[10px] uppercase tracking-widest">Days</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Reservation Retention</label>
                                <div class="relative">
                                    <input v-model="form.settings.reservation_expiry_days" type="number"
                                        class="w-full px-6 py-4 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-black text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                                    <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-black text-[10px] uppercase tracking-widest">Days</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Regional & Global -->
                    <div class="space-y-6 pt-8 border-t border-white/10 dark:border-slate-800/30">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-8 w-8 rounded-lg bg-violet-500/10 flex items-center justify-center text-violet-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.065M15 20.488V18a2 2 0 012-2h3.065M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h4 class="text-sm font-black text-slate-700 dark:text-slate-200 uppercase tracking-widest">Regional & Governance</h4>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Local Currency Token</label>
                                <input v-model="form.settings.currency_symbol" type="text"
                                    class="w-full px-6 py-4 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-black text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                            </div>
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">System Timezone Context</label>
                                <input v-model="form.settings.timezone" type="text"
                                    class="w-full px-6 py-4 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-black text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
                            </div>
                            
                            <div class="md:col-span-2 flex flex-wrap gap-8 pt-4">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <div class="relative">
                                        <input type="checkbox" v-model="form.settings.allow_registration" true-value="1" false-value="0" class="sr-only">
                                        <div class="w-12 h-6 bg-slate-200 dark:bg-slate-800 rounded-full transition-colors group-hover:bg-indigo-200 dark:group-hover:bg-indigo-900" :class="{'bg-indigo-500!': form.settings.allow_registration == '1'}"></div>
                                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform shadow-md" :class="{'translate-x-6': form.settings.allow_registration == '1'}"></div>
                                    </div>
                                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Allow Self-Registration</span>
                                </label>

                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <div class="relative">
                                        <input type="checkbox" v-model="form.settings.maintenance_mode" true-value="1" false-value="0" class="sr-only">
                                        <div class="w-12 h-6 bg-slate-200 dark:bg-slate-800 rounded-full transition-colors group-hover:bg-rose-200 dark:group-hover:bg-rose-900" :class="{'bg-rose-500!': form.settings.maintenance_mode == '1'}"></div>
                                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform shadow-md" :class="{'translate-x-6': form.settings.maintenance_mode == '1'}"></div>
                                    </div>
                                    <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Maintenance Lock</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-8 border-t border-white/10 dark:border-slate-800/50">
                        <button type="submit" :disabled="form.processing"
                            class="px-10 py-5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-2xl shadow-2xl shadow-indigo-500/20 hover:glow-indigo transition-all active:scale-95 disabled:opacity-50 flex items-center gap-3">
                            <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ form.processing ? 'Synchronizing…' : 'Commit Configuration' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
