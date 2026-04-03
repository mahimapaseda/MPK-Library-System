<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { useAlert } from '@/composables/useAlert';

const props = defineProps({
    defaultLoanDays: Number,
    maxBooksPerMember: Number,
});

const { alert } = useAlert();

const memberInput = ref(null);
const bookInput = ref(null);
const scanMode = ref(true); // Default to scan mode for speed

// ── Keyboard Shortcuts ──────────────────────────────────
const handleGlobalKey = (e) => {
    // '/' to focus book search, 'm' to focus member search (when not in input)
    if (document.activeElement.tagName === 'INPUT') return;
    
    if (e.key === '/') {
        e.preventDefault();
        bookInput.value?.focus();
    } else if (e.key === 'm') {
        e.preventDefault();
        memberInput.value?.focus();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleGlobalKey);
    memberInput.value?.focus();
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKey);
});

// ── Member Search ────────────────────────────────────────
const memberQuery = ref('');
const memberResults = ref([]);
const selectedMember = ref(null);
const memberLoading = ref(false);
let memberSearchTimeout = null;

const searchMembers = () => {
    clearTimeout(memberSearchTimeout);
    memberSearchTimeout = setTimeout(async () => {
        if (memberQuery.value.length < 2) {
            memberResults.value = [];
            return;
        }
        memberLoading.value = true;
        try {
            const { data } = await axios.get('/issues/search-members', { params: { q: memberQuery.value } });
            memberResults.value = data;
            
            // Auto-select if in scan mode and exact match ID or only one result
            if (scanMode.value && data.length === 1) {
                const query = memberQuery.value.trim().toUpperCase();
                if (data[0].member_id.toUpperCase() === query || (query.length > 5 && !isNaN(query))) {
                    selectMember(data[0]);
                    bookInput.value?.focus();
                }
            }
        } finally {
            memberLoading.value = false;
        }
    }, 150); // Faster debounce for scanner
};

const selectMember = (member) => {
    selectedMember.value = member;
    memberResults.value = [];
    memberQuery.value = member.name + ' (' + member.member_id + ')';
};

const clearMember = () => {
    selectedMember.value = null;
    memberQuery.value = '';
    memberResults.value = [];
};

// ── Book Search ──────────────────────────────────────────
const bookQuery = ref('');
const bookResults = ref([]);
const bookLoading = ref(false);
let bookSearchTimeout = null;

const searchBooks = () => {
    clearTimeout(bookSearchTimeout);
    bookSearchTimeout = setTimeout(async () => {
        if (bookQuery.value.length < 2) {
            bookResults.value = [];
            return;
        }
        bookLoading.value = true;
        try {
            const { data } = await axios.get('/issues/search-books', { params: { q: bookQuery.value } });
            bookResults.value = data;
            
            // Auto-add if in scan mode and perfect ISBN/ID match
            if (scanMode.value && data.length === 1) {
                const query = bookQuery.value.trim().toUpperCase();
                // Check if search matches ISBN (typical scanner output)
                if (data[0].isbn === query || data[0].accession_number === query) {
                    addToCart(data[0]);
                    bookQuery.value = ''; // Clear for next scan
                    bookResults.value = [];
                }
            }
        } finally {
            bookLoading.value = false;
        }
    }, 150); // Faster debounce for scanner
};

// ── Cart ─────────────────────────────────────────────────
const cart = ref([]);

const defaultDueDate = computed(() => {
    const d = new Date();
    d.setDate(d.getDate() + (props.defaultLoanDays || 14));
    return d.toISOString().split('T')[0];
});

const cartCount = computed(() => cart.value.length);
const remainingSlots = computed(() => {
    if (!props.maxBooksPerMember) return 999;
    return Math.max(0, props.maxBooksPerMember - cart.value.length);
});
const canCheckout = computed(() => !!selectedMember.value && cart.value.length > 0 && !form.processing);

const isInCart = (bookId) => cart.value.some(item => item.book_id === bookId);

const addToCart = (book) => {
    if (isInCart(book.id)) return;
    if (props.maxBooksPerMember && cart.value.length >= props.maxBooksPerMember) {
        alert(`Maximum ${props.maxBooksPerMember} books allowed per checkout.`, {
            title: 'Checkout Limit',
            type: 'warning'
        });
        return;
    }
    cart.value.push({
        book_id: book.id,
        title: book.title,
        author: book.author,
        due_date: defaultDueDate.value,
    });
};

const removeFromCart = (bookId) => {
    cart.value = cart.value.filter(item => item.book_id !== bookId);
};

const clearCart = () => {
    cart.value = [];
};

// ── Submit ───────────────────────────────────────────────
const form = useForm({});

const submitIssue = () => {
    if (!selectedMember.value || cart.value.length === 0) return;

    form.transform(() => ({
        member_id: selectedMember.value.id,
        books: cart.value.map(item => ({
            book_id: item.book_id,
            due_date: item.due_date,
        })),
    })).post('/issues/multiple', {
        preserveScroll: true,
        onSuccess: () => {
            cart.value = [];
            clearMember();
            bookQuery.value = '';
            bookResults.value = [];
            memberInput.value?.focus(); // Loop back to member
        },
    });
};

// ── Helpers ───────────────────────────────────────────────
const typeBadgeClass = (type) => ({
    student: 'bg-blue-100/70 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
    teacher: 'bg-violet-100/70 text-violet-700 dark:bg-violet-500/15 dark:text-violet-400',
    staff: 'bg-slate-100/70 text-slate-600 dark:bg-slate-700/40 dark:text-slate-400',
}[type] || 'bg-slate-100/70 text-slate-600 dark:bg-slate-700/40 dark:text-slate-400');
</script>

<template>
    <AppLayout title="Checkout POS">
        <template #header>
            <div class="w-full flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <Link href="/issues" class="h-8 w-8 rounded-xl bg-white/60 dark:bg-slate-800/80 border border-slate-200/70 dark:border-slate-700/70 text-slate-500 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-300 transition-all flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </Link>
                    <div class="min-w-0">
                        <div class="text-sm font-black text-slate-800 dark:text-white truncate">Circulation POS</div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300">Fast issue workstation</div>
                    </div>
                </div>

                <button @click="scanMode = !scanMode"
                    class="flex items-center justify-center gap-2 px-4 py-2 rounded-2xl border transition-all duration-300 text-[10px] font-black uppercase tracking-widest w-full sm:w-auto"
                    :class="scanMode ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-500/30' : 'bg-white/60 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300'">
                    <span class="h-2 w-2 rounded-full" :class="scanMode ? 'bg-white animate-pulse' : 'bg-slate-400'" />
                    {{ scanMode ? 'Scan Mode' : 'Manual Mode' }}
                </button>
            </div>
        </template>

        <div v-if="$page.props.flash?.success"
            class="mb-4 px-5 py-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 rounded-2xl flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-bold">{{ $page.props.flash.success }}</span>
        </div>

        <div class="grid grid-cols-12 gap-4 lg:gap-6 min-h-0">
            <section class="col-span-12 lg:col-span-8 grid grid-rows-[auto_1fr] gap-4 lg:gap-6 min-h-0">
                <div class="glass-card rounded-3xl p-4 lg:p-5 flex flex-wrap items-center gap-3 justify-between">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest">{{ cartCount }} in cart</span>
                        <span class="px-3 py-1.5 rounded-xl bg-white/60 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/70 text-[10px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-200">
                            {{ remainingSlots }} slots left
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-white/60 dark:bg-slate-800/80 border border-slate-200/60 dark:border-slate-700/70 text-[10px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-200">
                            Default due {{ defaultDueDate }}
                        </span>
                    </div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-200">
                        Shortcut: / books, M member
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 lg:gap-6 min-h-0">
                    <div class="glass-card rounded-3xl p-5 lg:p-6 min-h-0 flex flex-col">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 rounded-xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-700 dark:text-slate-200">Borrower</h3>
                            <Link href="/members" class="ml-auto text-[10px] font-black uppercase tracking-widest px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white">Add</Link>
                        </div>

                        <div class="relative">
                            <input
                                ref="memberInput"
                                v-model="memberQuery"
                                @input="searchMembers"
                                type="text"
                                placeholder="Name or member ID"
                                class="w-full pl-4 pr-11 py-3 bg-slate-50/70 dark:bg-slate-900/80 border-2 border-slate-100 dark:border-slate-700/60 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-400"
                            />
                            <button v-if="selectedMember" @click="clearMember" class="absolute right-3 top-2.5 h-7 w-7 rounded-lg text-slate-500 dark:text-slate-300 hover:text-rose-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div v-if="memberResults.length" class="absolute z-40 top-full left-0 right-0 mt-2 rounded-2xl overflow-hidden border border-indigo-500/30 bg-white dark:bg-slate-900 shadow-2xl">
                                <button
                                    v-for="m in memberResults" :key="m.id"
                                    @click="selectMember(m)"
                                    class="w-full px-4 py-3 text-left hover:bg-indigo-600 hover:text-white transition-all flex items-center gap-3 group"
                                >
                                    <div class="h-9 w-9 rounded-full bg-indigo-100 dark:bg-slate-800 group-hover:bg-white/20 text-indigo-600 dark:text-indigo-300 group-hover:text-white flex items-center justify-center font-black text-sm">{{ m.name[0] }}</div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-black text-slate-800 dark:text-white group-hover:text-white truncate">{{ m.name }}</div>
                                        <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-200 group-hover:text-white/80">{{ m.member_id }} · {{ m.type }}</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div v-if="selectedMember" class="mt-4 rounded-2xl p-4 bg-indigo-600 text-white flex items-center gap-3">
                            <div class="h-11 w-11 rounded-full bg-white/20 flex items-center justify-center font-black text-base">{{ selectedMember.name[0] }}</div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-black truncate">{{ selectedMember.name }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-white/80">{{ selectedMember.member_id }} · {{ selectedMember.grade || 'Staff' }}</div>
                            </div>
                            <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest" :class="typeBadgeClass(selectedMember.type)">{{ selectedMember.type }}</span>
                        </div>

                        <div v-else class="mt-4 flex-1 min-h-30 rounded-2xl border border-dashed border-slate-300 dark:border-slate-600/80 bg-transparent dark:bg-slate-950/20 flex items-center justify-center text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-200">
                            Select a member to continue
                        </div>
                    </div>

                    <div class="glass-card rounded-3xl p-5 lg:p-6 min-h-0 flex flex-col">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-700 dark:text-slate-200">Find books</h3>
                        </div>

                        <div class="relative mb-4">
                            <input
                                ref="bookInput"
                                v-model="bookQuery"
                                @input="searchBooks"
                                type="text"
                                placeholder="Title, ISBN, or author"
                                class="w-full pl-4 pr-11 py-3 bg-slate-50/70 dark:bg-slate-900/80 border-2 border-slate-100 dark:border-slate-700/60 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-400"
                            />
                            <div v-if="bookLoading" class="absolute right-3 top-2.5">
                                <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                            <div v-if="!bookLoading && bookResults.length === 0" class="h-full min-h-30 rounded-2xl border border-dashed border-slate-300 dark:border-slate-600/80 bg-transparent dark:bg-slate-950/20 flex items-center justify-center text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-200">
                                Search to load available books
                            </div>

                            <div
                                v-for="book in bookResults"
                                :key="book.id"
                                class="p-3.5 rounded-2xl border transition-all flex items-center justify-between gap-3"
                                :class="isInCart(book.id) ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white/60 dark:bg-slate-900/60 border-slate-200/70 dark:border-slate-700/70 hover:border-indigo-500/50'"
                            >
                                <div class="min-w-0">
                                    <div class="text-sm font-black truncate" :class="isInCart(book.id) ? 'text-white' : 'text-slate-800 dark:text-slate-100'">{{ book.title }}</div>
                                    <div class="text-[10px] font-bold uppercase tracking-widest" :class="isInCart(book.id) ? 'text-white/75' : 'text-slate-500 dark:text-slate-200'">{{ book.author }}</div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest" :class="isInCart(book.id) ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-200'">
                                        {{ book.available_quantity }}
                                    </span>
                                    <button v-if="!isInCart(book.id)" @click="addToCart(book)" class="h-8 w-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center hover:scale-105 active:scale-95">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                    </button>
                                    <div v-else class="h-8 w-8 rounded-xl bg-white text-indigo-600 flex items-center justify-center">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="col-span-12 lg:col-span-4 min-h-0">
                <div class="glass-card rounded-3xl p-5 lg:p-6 h-full flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-700 dark:text-slate-200">Checkout cart</h3>
                        <button v-if="cart.length" @click="clearCart" class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1.5 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-300 hover:bg-rose-500/20">Clear</button>
                    </div>

                    <div v-if="cart.length === 0" class="flex-1 min-h-45 rounded-2xl border border-dashed border-slate-300 dark:border-slate-600/80 bg-transparent dark:bg-slate-950/20 flex items-center justify-center text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-200">
                        Cart is empty
                    </div>

                    <div v-else class="flex-1 overflow-y-auto space-y-3 pr-1">
                        <div v-for="item in cart" :key="item.book_id" class="p-3.5 rounded-2xl bg-slate-50/70 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-700/70">
                            <div class="flex items-start gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-black text-slate-800 dark:text-slate-100 truncate">{{ item.title }}</div>
                                    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-200">{{ item.author }}</div>
                                </div>
                                <button @click="removeFromCart(item.book_id)" class="h-7 w-7 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-500/10">
                                    <svg class="h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <div class="mt-3 flex items-center gap-2">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-200">Due</span>
                                <input v-model="item.due_date" type="date" class="flex-1 px-2 py-1.5 rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200/70 dark:border-slate-700/70 space-y-3">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-600 dark:text-slate-300">
                            <span>Member</span>
                            <span class="truncate max-w-48 text-right">{{ selectedMember ? selectedMember.name : 'Not selected' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-600 dark:text-slate-300">
                            <span>Books</span>
                            <span>{{ cartCount }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-600 dark:text-slate-300">
                            <span>Expected return</span>
                            <span>{{ cart.length ? cart[0].due_date : '-' }}</span>
                        </div>

                        <button
                            @click="submitIssue"
                            :disabled="!canCheckout"
                            class="w-full py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 dark:disabled:bg-slate-800 disabled:text-slate-400 text-white text-xs font-black uppercase tracking-widest transition-all"
                        >
                            <span v-if="form.processing">Processing...</span>
                            <span v-else>Finalize Checkout</span>
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    </AppLayout>
</template>
