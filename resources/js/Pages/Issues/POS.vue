<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    defaultLoanDays: Number,
    maxBooksPerMember: Number,
});

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

const isInCart = (bookId) => cart.value.some(item => item.book_id === bookId);

const addToCart = (book) => {
    if (isInCart(book.id)) return;
    if (props.maxBooksPerMember && cart.value.length >= props.maxBooksPerMember) {
        alert(`Maximum ${props.maxBooksPerMember} books allowed per checkout.`);
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
    <AppLayout title="Checkout">
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-3">
                    <Link href="/dashboard" class="text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all hover:scale-110 active:scale-90">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </Link>
                    <span class="font-black tracking-tight">Checkout</span>
                </div>
                
                <button @click="scanMode = !scanMode" 
                    class="flex items-center gap-2 px-4 py-1.5 rounded-full border-2 transition-all duration-500"
                    :class="scanMode ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg shadow-indigo-500/30' : 'bg-slate-100 dark:bg-slate-800 border-transparent text-slate-500'">
                    <div class="w-2 h-2 rounded-full" :class="scanMode ? 'bg-white animate-pulse' : 'bg-slate-400'"></div>
                    <span class="text-[10px] font-black uppercase tracking-widest">{{ scanMode ? 'Active Scanning' : 'Manual Entry' }}</span>
                </button>
            </div>
        </template>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success"
            class="mb-6 px-6 py-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center gap-3 glow-indigo">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-bold">{{ $page.props.flash.success }}</span>
        </div>

        <!-- POS Layout -->
        <div class="flex gap-6 h-[calc(100vh-12rem)] min-h-[600px]">

            <!-- ── LEFT PANEL ─────────────────────────────── -->
            <div class="flex-1 flex flex-col gap-6 min-w-0 overflow-hidden">
                <!-- Book Search -->
                <div class="glass-card p-6 rounded-3xl flex-1 flex flex-col overflow-hidden">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-600 dark:text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-800 dark:text-slate-400">Book Inventory Search</h3>
                    </div>

                    <div class="relative mb-6">
                        <input
                            ref="bookInput"
                            v-model="bookQuery"
                            @input="searchBooks"
                            type="text"
                            placeholder="Title, ISBN, or Author..."
                            class="w-full pl-5 pr-12 py-3.5 bg-slate-50/50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-500 dark:placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-sans"
                        />
                        <div v-if="bookLoading" class="absolute right-4 top-3.5">
                            <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto pr-2 space-y-3">
                        <div v-if="!bookLoading && bookResults.length === 0" class="h-full flex flex-col items-center justify-center text-slate-500 dark:text-slate-700">
                            <svg class="h-20 w-20 mb-4 opacity-50 dark:opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <span class="text-sm font-black uppercase tracking-widest">Awaiting Search Query</span>
                        </div>

                        <div v-for="book in bookResults" :key="book.id" 
                            class="p-4 rounded-2xl border-2 transition-all flex items-center justify-between group"
                            :class="isInCart(book.id) ? 'bg-indigo-600 border-indigo-600 text-white glow-indigo' : 'bg-white/50 dark:bg-slate-800/80 border-slate-50 dark:border-slate-700/50 hover:border-indigo-500/50'">
                             <div class="min-w-0">
                                <div class="font-black text-sm text-slate-800 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ book.title }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-800 dark:text-slate-200">Authored by {{ book.author }}</div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="px-2 py-1 bg-white/20 dark:bg-black/30 rounded-lg text-[10px] font-black uppercase tracking-tight text-slate-700 dark:text-slate-300">
                                    {{ book.available_quantity }} IN STOCK
                                </div>
                                <button v-if="!isInCart(book.id)" @click="addToCart(book)"
                                    class="p-2 bg-indigo-600 text-white rounded-xl hover:scale-110 active:scale-90 transition-all shadow-lg shadow-indigo-500/20">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                </button>
                                <div v-else class="h-9 w-9 bg-white text-indigo-600 rounded-xl flex items-center justify-center">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── RIGHT PANEL: Sidebar Summary ───────────────────────── -->
            <div class="w-96 shrink-0 flex flex-col gap-6">
                <!-- Member Lookup -->
                <div class="glass-card p-6 rounded-3xl">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 bg-indigo-500/10 rounded-lg text-indigo-600 dark:text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex items-center gap-2 flex-1">
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-800 dark:text-slate-400">Member Verification</h3>
                            <Link href="/members" class="ml-auto p-1 bg-indigo-600 text-white rounded-lg hover:scale-110 active:scale-95 transition-all shadow-md shadow-indigo-500/20 tooltip" title="Add New Member">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <div class="relative">
                        <input
                            ref="memberInput"
                            v-model="memberQuery"
                            @input="searchMembers"
                            type="text"
                            placeholder="Search by name or Member ID..."
                            class="w-full pl-5 pr-12 py-3.5 bg-slate-50/50 dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-500 dark:placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-sans"
                        />
                        <button v-if="selectedMember" @click="clearMember"
                            class="absolute right-4 top-3.5 text-slate-400 hover:text-rose-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <!-- Member dropdown results -->
                        <div v-if="memberResults.length"
                            class="absolute top-full left-0 right-0 mt-2 glass-card dark:bg-slate-800 rounded-2xl shadow-2xl z-30 overflow-hidden border-2 border-indigo-500/30">
                            <button
                                v-for="m in memberResults" :key="m.id"
                                @click="selectMember(m)"
                                class="w-full text-left px-5 py-4 hover:bg-indigo-600 hover:text-white transition-all flex items-center gap-4 group">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-slate-800 group-hover:bg-white/20 text-indigo-600 dark:text-indigo-400 group-hover:text-white flex items-center justify-center font-black text-sm transition-colors">
                                    {{ m.name[0] }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-black text-sm text-slate-800 dark:text-white transition-colors group-hover:text-white">{{ m.name }}</div>
                                    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-800 dark:text-slate-200">
                                        {{ m.member_id }} · {{ m.type }}
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Selected Member Card -->
                    <transition enter-active-class="transition duration-300 ease-out" enter-from-class="transform -translate-y-2 opacity-0" enter-to-class="transform translate-y-0 opacity-100">
                        <div v-if="selectedMember" class="mt-4 p-4 bg-indigo-600 text-white rounded-2xl flex items-center gap-4 glow-indigo">
                            <div class="h-12 w-12 rounded-full bg-white/20 flex items-center justify-center font-black text-lg">
                                {{ selectedMember.name[0] }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-black text-sm">{{ selectedMember.name }}</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest opacity-70">
                                    {{ selectedMember.member_id }} · {{ selectedMember.type }} · {{ selectedMember.grade || 'Staff' }}
                                </div>
                            </div>
                            <div class="h-8 w-8 rounded-full bg-white/20 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                    </transition>
                </div>

                <!-- Checkout Cart -->
                <div class="glass-card p-6 rounded-3xl flex-1 flex flex-col overflow-hidden relative">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-indigo-600 rounded-lg text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-800 dark:text-slate-400">Checkout Cart</h3>
                        </div>
                        <span v-if="cart.length > 0" class="px-2 py-1 bg-indigo-600 text-white text-[10px] font-black rounded-lg">{{ cart.length }} ITEM{{ cart.length > 1 ? 'S' : '' }}</span>
                    </div>

                    <!-- Cart Content -->
                    <div v-if="cart.length === 0" class="flex-1 flex flex-col items-center justify-center text-slate-500 dark:text-slate-700 opacity-70">
                        <svg class="h-16 w-16 mb-4 opacity-50 dark:opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="text-xs font-black uppercase tracking-widest">Cart is Empty</span>
                    </div>

                    <div v-else class="flex-1 overflow-y-auto space-y-4 pr-1">
                        <div v-for="item in cart" :key="item.book_id" class="p-4 bg-slate-50/50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800 rounded-2xl group border-l-4 border-l-indigo-500">
                            <div class="flex justify-between items-start mb-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-black text-slate-800 dark:text-white truncate">{{ item.title }}</div>
                                    <div class="text-[10px] font-bold text-slate-800 dark:text-slate-300 uppercase tracking-widest leading-none mt-1">{{ item.author }}</div>
                                </div>
                                <button @click="removeFromCart(item.book_id)" class="text-slate-300 hover:text-rose-500 transition-colors p-1">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">DUE DATE</span>
                                <input v-model="item.due_date" type="date" 
                                    class="flex-1 bg-white dark:bg-slate-900 border-0 rounded-lg text-xs font-bold text-slate-700 dark:text-slate-300 py-1.5 focus:ring-2 focus:ring-indigo-500 transition-all"/>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800/50 space-y-4">
                        <div v-if="cart.length > 0" class="flex justify-between items-center px-2">
                            <span class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Estimated Return</span>
                            <span class="text-xs font-black text-indigo-600 dark:text-indigo-400">{{ cart[0].due_date }}</span>
                        </div>
                        <button
                            @click="submitIssue"
                            :disabled="!selectedMember || cart.length === 0 || form.processing"
                            class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-100 dark:disabled:bg-slate-800 disabled:text-slate-400 text-white font-black uppercase tracking-widest rounded-2xl transition-all shadow-2xl shadow-indigo-500/30 hover:glow-indigo flex items-center justify-center gap-3">
                            <span v-if="form.processing" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Processing...
                            </span>
                            <span v-else>Finalize Checkout</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
