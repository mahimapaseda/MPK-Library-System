<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    settings: Object
});

const activeTab = ref('identity');

const tabs = [
    { key: 'identity', label: 'Identity' },
    { key: 'circulation', label: 'Circulation' },
    { key: 'automation', label: 'Automation' },
    { key: 'system', label: 'System' },
];

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
        send_due_reminders: props.settings?.send_due_reminders ?? '1',
        reminder_days_before_due: props.settings?.reminder_days_before_due ?? 2,
        auto_mark_overdue: props.settings?.auto_mark_overdue ?? '1',
        allow_member_renewal: props.settings?.allow_member_renewal ?? '1',
        max_renewals_per_issue: props.settings?.max_renewals_per_issue ?? 1,
        max_reservations_per_member: props.settings?.max_reservations_per_member ?? 2,
        low_stock_threshold: props.settings?.low_stock_threshold ?? 2,
        overdue_lock_days: props.settings?.overdue_lock_days ?? 14,
        issue_receipt_enabled: props.settings?.issue_receipt_enabled ?? '1',
        working_days_per_week: props.settings?.working_days_per_week ?? 5,
        opening_time: props.settings?.opening_time ?? '08:00',
        closing_time: props.settings?.closing_time ?? '16:00',
    }
});

const summary = computed(() => ({
    loanDays: Number(form.settings.loan_duration_days || 0),
    maxBooks: Number(form.settings.max_books_per_member || 0),
    fine: Number(form.settings.fine_per_day || 0),
    reminders: form.settings.send_due_reminders === '1' ? 'On' : 'Off',
}));

const submit = () => {
    form.post('/settings', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Settings" :noScroll="true">
        <template #header>
            Library Settings
        </template>

        <div class="h-full min-h-0">
            <div class="settings-form h-full min-h-0 glass-card rounded-[2.2rem] border-white/20 dark:border-slate-800/50 shadow-2xl p-5 sm:p-6 lg:p-8 grid grid-cols-1 lg:grid-cols-[18rem_minmax(0,1fr)] gap-5">
                <aside class="rounded-3xl border border-white/30 dark:border-slate-700/60 bg-white/40 dark:bg-slate-900/80 p-4 sm:p-5 min-h-0">
                    <h3 class="text-lg font-black text-slate-800 dark:text-white">System Configuration</h3>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-300 uppercase tracking-widest mt-1">No long-scroll layout</p>

                    <div class="mt-4 grid grid-cols-2 lg:grid-cols-1 gap-2">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            type="button"
                            @click="activeTab = tab.key"
                            class="rounded-2xl px-4 py-3 text-left text-xs font-black uppercase tracking-widest transition-all"
                            :class="activeTab === tab.key ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'bg-white/50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-300'"
                        >
                            {{ tab.label }}
                        </button>
                    </div>

                    <div class="settings-surface mt-5 rounded-2xl bg-slate-50/70 dark:bg-slate-950/60 border border-slate-200/60 dark:border-slate-700/70 p-4 space-y-2">
                        <div class="text-[10px] font-black tracking-widest uppercase text-slate-400 dark:text-slate-300">Policy Snapshot</div>
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-200">Loan days: {{ summary.loanDays }}</div>
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-200">Max books: {{ summary.maxBooks }}</div>
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-200">Fine/day: {{ form.settings.currency_symbol }} {{ summary.fine }}</div>
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-200">Reminders: {{ summary.reminders }}</div>
                    </div>
                </aside>

                <form @submit.prevent="submit" class="min-h-0 flex flex-col rounded-3xl border border-white/30 dark:border-slate-700/60 bg-white/40 dark:bg-slate-900/80">
                    <div class="flex-1 min-h-0 overflow-y-auto p-5 sm:p-6 lg:p-8">
                        <div v-if="activeTab === 'identity'" class="space-y-5">
                            <h4 class="section-title text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-200">Institutional Identity</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="md:col-span-2 space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Library Display Name</label>
                                    <input v-model="form.settings.library_name" type="text" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="md:col-span-2 space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Address</label>
                                    <textarea v-model="form.settings.library_address" rows="3" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500"></textarea>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Phone</label>
                                    <input v-model="form.settings.library_phone" type="text" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Email</label>
                                    <input v-model="form.settings.library_email" type="email" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500" />
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'circulation'" class="space-y-5">
                            <h4 class="section-title text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-200">Circulation Policy</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Fine Per Day</label>
                                    <input v-model="form.settings.fine_per_day" type="number" step="0.01" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Grace Period (Days)</label>
                                    <input v-model="form.settings.grace_period_days" type="number" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Loan Duration (Days)</label>
                                    <input v-model="form.settings.loan_duration_days" type="number" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Max Books / Member</label>
                                    <input v-model="form.settings.max_books_per_member" type="number" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Reservation Expiry (Days)</label>
                                    <input v-model="form.settings.reservation_expiry_days" type="number" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Max Renewals / Issue</label>
                                    <input v-model="form.settings.max_renewals_per_issue" type="number" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Max Reservations / Member</label>
                                    <input v-model="form.settings.max_reservations_per_member" type="number" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Overdue Lock (Days)</label>
                                    <input v-model="form.settings.overdue_lock_days" type="number" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                            </div>
                        </div>

                        <div v-if="activeTab === 'automation'" class="space-y-5">
                            <h4 class="section-title text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-200">Automation & Alerts</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Reminder Days Before Due</label>
                                    <input v-model="form.settings.reminder_days_before_due" type="number" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Low Stock Threshold</label>
                                    <input v-model="form.settings.low_stock_threshold" type="number" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="settings-surface rounded-2xl border border-slate-200/70 dark:border-slate-700/70 p-4 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Send Due Reminders</span>
                                    <input type="checkbox" v-model="form.settings.send_due_reminders" true-value="1" false-value="0" class="h-5 w-5 accent-indigo-600">
                                </label>
                                <label class="settings-surface rounded-2xl border border-slate-200/70 dark:border-slate-700/70 p-4 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Auto Mark Overdue</span>
                                    <input type="checkbox" v-model="form.settings.auto_mark_overdue" true-value="1" false-value="0" class="h-5 w-5 accent-indigo-600">
                                </label>
                                <label class="settings-surface rounded-2xl border border-slate-200/70 dark:border-slate-700/70 p-4 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Allow Member Renewal</span>
                                    <input type="checkbox" v-model="form.settings.allow_member_renewal" true-value="1" false-value="0" class="h-5 w-5 accent-indigo-600">
                                </label>
                                <label class="settings-surface rounded-2xl border border-slate-200/70 dark:border-slate-700/70 p-4 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Issue Receipt Enabled</span>
                                    <input type="checkbox" v-model="form.settings.issue_receipt_enabled" true-value="1" false-value="0" class="h-5 w-5 accent-indigo-600">
                                </label>
                            </div>
                        </div>

                        <div v-if="activeTab === 'system'" class="space-y-5">
                            <h4 class="section-title text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-200">Regional & System Controls</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Currency Symbol</label>
                                    <input v-model="form.settings.currency_symbol" type="text" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Timezone</label>
                                    <input v-model="form.settings.timezone" type="text" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Working Days / Week</label>
                                    <input v-model="form.settings.working_days_per_week" type="number" class="w-full px-5 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-2">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Open</label>
                                        <input v-model="form.settings.opening_time" type="time" class="w-full px-4 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Close</label>
                                        <input v-model="form.settings.closing_time" type="time" class="w-full px-4 py-3 bg-slate-50/70 dark:bg-slate-950/60 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold focus:outline-none focus:border-indigo-500" />
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                <label class="settings-surface rounded-2xl border border-slate-200/70 dark:border-slate-700/70 p-4 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Allow Self Registration</span>
                                    <input type="checkbox" v-model="form.settings.allow_registration" true-value="1" false-value="0" class="h-5 w-5 accent-indigo-600">
                                </label>
                                <label class="settings-surface rounded-2xl border border-slate-200/70 dark:border-slate-700/70 p-4 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Maintenance Mode</span>
                                    <input type="checkbox" v-model="form.settings.maintenance_mode" true-value="1" false-value="0" class="h-5 w-5 accent-rose-600">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 border-t border-white/20 dark:border-slate-800/50 p-4 sm:p-5 flex justify-end">
                        <button type="submit" :disabled="form.processing" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-2xl shadow-xl shadow-indigo-500/20 transition-all disabled:opacity-50">
                            {{ form.processing ? 'Saving...' : 'Save Settings' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.dark .settings-form label {
    color: rgb(203 213 225) !important;
}

.dark .settings-form input,
.dark .settings-form textarea {
    color: rgb(241 245 249) !important;
    background-color: rgba(15, 23, 42, 0.82) !important;
    border-color: rgba(100, 116, 139, 0.7) !important;
}

.dark .settings-form input:focus,
.dark .settings-form textarea:focus {
    border-color: rgb(129 140 248) !important;
}

.dark .settings-form .settings-surface {
    background-color: rgba(15, 23, 42, 0.7) !important;
}
</style>
