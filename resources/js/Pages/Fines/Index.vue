<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import { computed, ref, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    fines: Object,
    filters: Object,
});

const page = usePage();
const currencySymbol = computed(() => page.props.settings?.currency_symbol || 'LKR');
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const showResolveModal = ref(false);
const selectedFine = ref(null);
const resolveForm = useForm({
    status: 'paid',
    resolution_notes: '',
});

const stats = computed(() => {
    const rows = props.fines?.data ?? [];

    return {
        visible: rows.length,
        unpaid: rows.filter((fine) => fine.status === 'unpaid').length,
        paid: rows.filter((fine) => fine.status === 'paid').length,
        waived: rows.filter((fine) => fine.status === 'waived').length,
    };
});

let timeout = null;
watch(() => ({ search: search.value, status: status.value }), (params) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/fines', params, { preserveState: true, replace: true, preserveScroll: true });
    }, 300);
}, { deep: true });

const resetFilters = () => {
    search.value = '';
    status.value = '';
    router.get('/fines', {}, { preserveState: false });
};

const openResolveModal = (fine, nextStatus) => {
    selectedFine.value = fine;
    resolveForm.status = nextStatus;
    resolveForm.resolution_notes = fine.resolution_notes || '';
    showResolveModal.value = true;
};

const closeResolveModal = () => {
    showResolveModal.value = false;
    selectedFine.value = null;
    resolveForm.reset();
    resolveForm.status = 'paid';
};

const submitResolution = () => {
    if (!selectedFine.value) {
        return;
    }

    resolveForm.post(`/fines/${selectedFine.value.id}/resolve`, {
        preserveScroll: true,
        onSuccess: () => closeResolveModal(),
    });
};

const chargeLabel = (fine) => {
    const issueStatus = fine.issue?.status;

    if (issueStatus === 'lost') return 'Lost Item Charge';
    if (issueStatus === 'damaged') return 'Damaged Item Charge';
    return 'Overdue Charge';
};
</script>

<template>
    <AppLayout title="Charges">
        <template #header>Charge Resolution</template>

        <div class="space-y-5">
            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Visible charges</div>
                    <div class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ stats.visible }}</div>
                </div>
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Unpaid</div>
                    <div class="mt-2 text-3xl font-black text-rose-600 dark:text-rose-300">{{ stats.unpaid }}</div>
                </div>
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Paid</div>
                    <div class="mt-2 text-3xl font-black text-emerald-600 dark:text-emerald-300">{{ stats.paid }}</div>
                </div>
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Waived</div>
                    <div class="mt-2 text-3xl font-black text-slate-700 dark:text-slate-100">{{ stats.waived }}</div>
                </div>
            </section>

            <section class="glass-card rounded-3xl p-4 sm:p-5 lg:p-6 space-y-4">
                <div>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Charge ledger</h3>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 mt-1">Resolve lost, damaged, and overdue charges as paid or waived</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)_auto] gap-3 items-end">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300 mb-2 px-1">Search charges</label>
                        <input v-model="search" type="text" placeholder="Member, book, or accession"
                            class="w-full px-4 py-3 bg-white/60 dark:bg-slate-900/70 border-2 border-slate-100 dark:border-slate-700/70 rounded-2xl text-sm font-bold text-slate-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300 mb-2 px-1">Status</label>
                        <select v-model="status" class="w-full px-4 py-3 bg-white/60 dark:bg-slate-900/70 border-2 border-slate-100 dark:border-slate-700/70 rounded-2xl text-xs font-bold text-slate-800 dark:text-white">
                            <option value="">All Statuses</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="paid">Paid</option>
                            <option value="waived">Waived</option>
                        </select>
                    </div>

                    <button v-if="search || status" @click="resetFilters"
                        class="px-4 py-3 rounded-2xl border border-rose-500/20 bg-rose-500/10 text-[10px] font-black uppercase tracking-widest text-rose-500 hover:bg-rose-500/15">
                        Reset
                    </button>
                </div>
            </section>

            <section class="glass-card rounded-3xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-270 text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-900/50">
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Member</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Charge</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Accession</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Amount</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Status</th>
                                <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 dark:divide-slate-800/30">
                            <tr v-if="!fines.data?.length">
                                <td colspan="6" class="px-8 py-16 text-center text-sm font-black text-slate-700 dark:text-slate-200">No charges found for the current filters</td>
                            </tr>
                            <tr v-for="fine in fines.data" :key="fine.id" class="group hover:bg-white/40 dark:hover:bg-white/5 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="text-sm font-black text-slate-800 dark:text-slate-100">{{ fine.member.name }}</div>
                                    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 mt-1">{{ fine.member.member_id }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-sm font-black text-slate-800 dark:text-slate-100">{{ fine.issue?.book?.title || 'General charge' }}</div>
                                    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 mt-1">{{ chargeLabel(fine) }}</div>
                                </td>
                                <td class="px-8 py-5 text-xs font-mono font-bold text-slate-600 dark:text-slate-300">{{ fine.issue?.copy?.accession_number || 'LEGACY' }}</td>
                                <td class="px-8 py-5 text-sm font-black text-slate-800 dark:text-slate-100">{{ currencySymbol }} {{ Number(fine.amount).toFixed(2) }}</td>
                                <td class="px-8 py-5">
                                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border"
                                        :class="{
                                            'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20': fine.status === 'unpaid',
                                            'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20': fine.status === 'paid',
                                            'bg-slate-500/10 text-slate-700 dark:text-slate-300 border-slate-500/20': fine.status === 'waived',
                                        }">
                                        {{ fine.status }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div v-if="fine.status === 'unpaid'" class="flex justify-end gap-2">
                                        <button @click="openResolveModal(fine, 'waived')" class="px-4 py-2 bg-slate-500/10 text-slate-700 dark:text-slate-300 hover:bg-slate-700 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest border border-slate-500/20">Waive</button>
                                        <button @click="openResolveModal(fine, 'paid')" class="px-4 py-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest border border-emerald-500/20">Mark Paid</button>
                                    </div>
                                    <span v-else class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500">Resolved</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-8 py-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-white/5 dark:border-slate-800/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin charge resolution ledger</span>
                    <PaginationControls :links="fines.links || []" />
                </div>
            </section>
        </div>

        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showResolveModal" class="fixed inset-0 bg-slate-950/40 dark:bg-black/60 backdrop-blur-md flex items-center justify-center p-4 z-50">
                <div class="glass-card rounded-4xl shadow-2xl border-white/20 dark:border-slate-700/30 w-full max-w-xl overflow-hidden">
                    <div class="px-8 py-6 border-b border-white/10 dark:border-slate-800/50 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Resolve Charge</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Mark this charge as paid or waived</p>
                        </div>
                        <button @click="closeResolveModal" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white transition-all hover:rotate-90">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitResolution" class="p-8 space-y-6">
                        <div class="rounded-2xl bg-slate-50/60 dark:bg-slate-950/60 border border-slate-200/70 dark:border-slate-700/70 p-4">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Charge record</div>
                            <div class="mt-2 text-sm font-black text-slate-800 dark:text-white">{{ selectedFine?.issue?.book?.title || 'General charge' }}</div>
                            <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 mt-1">{{ selectedFine?.member?.name }} · {{ currencySymbol }} {{ selectedFine ? Number(selectedFine.amount).toFixed(2) : '0.00' }}</div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Resolution</label>
                            <select v-model="resolveForm.status" class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500">
                                <option value="paid">Paid</option>
                                <option value="waived">Waived</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Resolution Notes</label>
                            <textarea v-model="resolveForm.resolution_notes" rows="4"
                                class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500"
                                placeholder="Receipt number, waiver reason, or approval note"></textarea>
                        </div>

                        <div class="pt-4 flex justify-end gap-3">
                            <button type="button" @click="closeResolveModal" class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">Cancel</button>
                            <button type="submit" :disabled="resolveForm.processing" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-2xl transition-all disabled:opacity-50">Save Resolution</button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </AppLayout>
</template>
