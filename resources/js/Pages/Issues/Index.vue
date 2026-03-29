<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, watch } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    issues: Object,
    books: Array,
    members: Array,
    filters: Object
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const book_id = ref(props.filters?.book_id || '');
const member_id = ref(props.filters?.member_id || '');
const showIssueModal = ref(false);

// Multi-parameter filter watch
let timeout = null;
watch(() => ({
    search: search.value,
    status: status.value,
    book_id: book_id.value,
    member_id: member_id.value
}), (params) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/issues', params, { 
            preserveState: true, 
            replace: true,
            preserveScroll: true
        });
    }, 300);
}, { deep: true });

const resetFilters = () => {
    search.value = '';
    status.value = '';
    book_id.value = '';
    member_id.value = '';
    router.get('/issues', {}, { preserveState: false });
};

const issueForm = useForm({
    book_id: '',
    member_id: '',
    due_date: new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
});

const submitIssueForm = () => {
    issueForm.post('/issues', {
        onSuccess: () => {
            showIssueModal.value = false;
            issueForm.reset();
        }
    });
};

const returnBook = (id) => {
    if (confirm('Are you sure you want to mark this book as returned?')) {
        router.post(`/issues/${id}/return`);
    }
};
</script>

<template>
    <AppLayout title="Book Issues">
        <template #header>Manage Book Issues</template>

        <div class="mb-5 sm:mb-6 flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <div class="relative w-full sm:w-96 group">
                    <svg class="absolute left-4 top-3.5 h-5 w-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Search by book title or member name…"
                        class="w-full pl-11 pr-5 py-3 bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <button @click="showIssueModal = true" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs sm:text-sm font-black uppercase tracking-widest shadow-2xl shadow-indigo-500/20 hover:glow-indigo transition-all flex items-center justify-center gap-2 active:scale-95 flex-1 sm:flex-none">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Quick Issue
                    </button>
                    <Link href="/issues/pos" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs sm:text-sm font-black uppercase tracking-widest shadow-2xl shadow-emerald-500/20 hover:glow-emerald transition-all flex items-center justify-center gap-2 active:scale-95 flex-1 sm:flex-none">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                        </svg>
                        POS
                    </Link>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="flex flex-wrap items-center gap-3">
                <select v-model="status" class="px-4 py-2 bg-white/30 dark:bg-slate-900/30 backdrop-blur-md border-2 border-slate-100 dark:border-slate-800/50 rounded-xl text-xs font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 transition-all">
                    <option value="">All Statuses</option>
                    <option value="issued">Still Issued</option>
                    <option value="returned">Returned</option>
                    <option value="overdue">Overdue</option>
                </select>

                <select v-model="book_id" class="px-4 py-2 bg-white/30 dark:bg-slate-900/30 backdrop-blur-md border-2 border-slate-100 dark:border-slate-800/50 rounded-xl text-xs font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 transition-all max-w-[150px]">
                    <option value="">All Books</option>
                    <option v-for="book in books" :key="book.id" :value="book.id">{{ book.title }}</option>
                </select>

                <select v-model="member_id" class="px-4 py-2 bg-white/30 dark:bg-slate-900/30 backdrop-blur-md border-2 border-slate-100 dark:border-slate-800/50 rounded-xl text-xs font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 transition-all max-w-[150px]">
                    <option value="">All Members</option>
                    <option v-for="member in members" :key="member.id" :value="member.id">{{ member.name }}</option>
                </select>

                <button v-if="search || status || book_id || member_id" 
                    @click="resetFilters"
                    class="text-[10px] font-black uppercase tracking-widest text-rose-500 hover:text-rose-600 transition-colors ml-auto">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="glass-card rounded-3xl overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-900/50">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none">Resource Asset</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none">Borrower Entity</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none">Issue Date</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none">Deadline</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none">Status</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none text-right">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 dark:divide-slate-800/30">
                        <tr v-if="!issues.data?.length">
                            <td colspan="6" class="px-8 py-12 text-center">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No Active Records Found</div>
                            </td>
                        </tr>
                        <tr v-for="issue in issues.data" :key="issue.id" class="group hover:bg-white/40 dark:hover:bg-white/5 transition-colors">
                            <td class="px-8 py-5">
                                <div class="text-sm font-black text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors tracking-tight">{{ issue.book.title }}</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">Physical Asset</div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ issue.member.name }}</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5 font-mono">{{ issue.member.member_id }}</div>
                            </td>
                            <td class="px-8 py-5 text-[10px] font-bold text-slate-500 dark:text-slate-500 uppercase tracking-widest">{{ new Date(issue.issued_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) }}</td>
                            <td class="px-8 py-5 text-[10px] font-black uppercase tracking-widest" :class="issue.status === 'overdue' ? 'text-rose-500' : 'text-slate-500 dark:text-slate-400'">
                                {{ new Date(issue.due_date).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) }}
                            </td>
                            <td class="px-8 py-5">
                                <span :class="{
                                    'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20': issue.status === 'issued',
                                    'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20': issue.status === 'returned',
                                    'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20': issue.status === 'overdue',
                                    'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20': issue.status === 'lost',
                                }" class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border">
                                    {{ issue.status }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <button v-if="issue.status === 'issued' || issue.status === 'overdue'" 
                                    @click="returnBook(issue.id)"
                                    class="px-4 py-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 border border-indigo-500/20">
                                    Finalize Return
                                </button>
                                <span v-else class="text-[10px] font-black text-slate-300 dark:text-slate-700 uppercase tracking-widest">Completed</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-white/5 dark:border-slate-800/30 flex items-center justify-between">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Displaying Global Circulation History</span>
                <div class="flex gap-2">
                    <button class="px-3 py-1.5 glass-card text-[10px] font-black text-slate-400 uppercase tracking-widest rounded-lg hover:text-indigo-500 transition-colors cursor-not-allowed">Prev</button>
                    <button class="px-3 py-1.5 bg-indigo-600 text-[10px] font-black text-white uppercase tracking-widest rounded-lg glow-indigo transition-all">1</button>
                    <button class="px-3 py-1.5 glass-card text-[10px] font-black text-slate-400 uppercase tracking-widest rounded-lg hover:text-indigo-500 transition-colors cursor-not-allowed">Next</button>
                </div>
            </div>
        </div>

        <!-- Issue Modal -->
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showIssueModal" class="fixed inset-0 bg-slate-950/40 dark:bg-black/60 backdrop-blur-md flex items-center justify-center p-4 z-50">
                <div class="glass-card rounded-4xl shadow-2xl border-white/20 dark:border-slate-700/30 w-full max-w-lg overflow-hidden">
                    <div class="px-8 py-6 border-b border-white/10 dark:border-slate-800/50 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Direct Asset Issue</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Circulation Control System</p>
                        </div>
                        <button @click="showIssueModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white transition-all hover:rotate-90">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitIssueForm" class="p-8 space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Selected Asset</label>
                            <select v-model="issueForm.book_id" required
                                class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none">
                                <option value="" disabled>Search Asset Catalog…</option>
                                <option v-for="book in books" :key="book.id" :value="book.id">{{ book.title }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Designated Borrower</label>
                            <select v-model="issueForm.member_id" required
                                class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all appearance-none">
                                <option value="" disabled>Search Member Database…</option>
                                <option v-for="member in members" :key="member.id" :value="member.id">{{ member.name }} ({{ member.member_id }})</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Expiration Deadline</label>
                            <input v-model="issueForm.due_date" type="date" required
                                class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-black text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                        
                        <div class="pt-4 flex justify-end gap-3">
                            <button type="button" @click="showIssueModal = false"
                                class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">Discard</button>
                            <button type="submit" :disabled="issueForm.processing"
                                class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-2xl shadow-2xl shadow-indigo-500/20 hover:glow-indigo transition-all active:scale-95 disabled:opacity-50">Authorize Issue</button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </AppLayout>
</template>
