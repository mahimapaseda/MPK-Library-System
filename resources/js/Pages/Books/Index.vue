<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import { useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useAlert } from '@/composables/useAlert';

const props = defineProps({
    books: Object,
    categories: Array,
    filters: Object
});

const search = ref(props.filters?.search || '');
const category_id = ref(props.filters?.category_id || '');
const language = ref(props.filters?.language || '');
const available = ref(props.filters?.available || 'false');
const showAddModal = ref(false);

const stats = computed(() => {
    const rows = props.books?.data ?? [];

    return {
        visibleTitles: rows.length,
        inStockTitles: rows.filter((book) => Number(book.available_quantity) > 0).length,
        lowStockTitles: rows.filter((book) => Number(book.available_quantity) > 0 && Number(book.available_quantity) <= 2).length,
        visibleCopies: rows.reduce((sum, book) => sum + Number(book.available_quantity || 0), 0),
    };
});

const { confirm } = useAlert();

const addForm = useForm({
    title: '',
    author: '',
    isbn: '',
    category_id: '',
    language: 'English',
    total_quantity: 1,
    location_rack: ''
});

const submitAddForm = () => {
    addForm.post('/books', {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.reset();
        }
    });
};

// Multi-parameter filter watch
let timeout = null;
watch(() => ({
    search: search.value,
    category_id: category_id.value,
    language: language.value,
    available: available.value
}), (params) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/books', params, { 
            preserveState: true, 
            replace: true,
            preserveScroll: true
        });
    }, 300);
}, { deep: true });

const resetFilters = () => {
    search.value = '';
    category_id.value = '';
    language.value = '';
    available.value = 'false';
    router.get('/books', {}, { preserveState: false });
};

const deleteBook = async (id) => {
    const confirmed = await confirm('Are you sure you want to delete this book?', {
        title: 'Delete Book',
        type: 'error',
        confirmText: 'Delete'
    });
    if (confirmed) {
        router.delete(`/books/${id}`);
    }
}
</script>

<template>
    <AppLayout title="Books Management">
        <template #header>Books Inventory</template>

        <div class="space-y-5">
            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Visible titles</div>
                    <div class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ stats.visibleTitles }}</div>
                </div>
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Titles in stock</div>
                    <div class="mt-2 text-3xl font-black text-emerald-600 dark:text-emerald-300">{{ stats.inStockTitles }}</div>
                </div>
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Low-stock titles</div>
                    <div class="mt-2 text-3xl font-black text-amber-600 dark:text-amber-300">{{ stats.lowStockTitles }}</div>
                </div>
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Visible copies</div>
                    <div class="mt-2 text-3xl font-black text-indigo-600 dark:text-indigo-300">{{ stats.visibleCopies }}</div>
                </div>
            </section>

            <section class="glass-card rounded-3xl p-4 sm:p-5 lg:p-6 space-y-4">
                <div class="flex flex-col xl:flex-row xl:items-center gap-4 justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Catalog workspace</h3>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 mt-1">Filter, review stock, and register titles</p>
                    </div>
                    <button @click="showAddModal = true"
                        class="px-5 sm:px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs sm:text-sm font-black uppercase tracking-widest shadow-2xl shadow-indigo-500/20 transition-all flex items-center justify-center gap-2 shrink-0 active:scale-95 w-full xl:w-auto">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                        Add New Book
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-[minmax(0,1.5fr)_repeat(2,minmax(0,0.8fr))_auto] gap-3 items-end">
                    <div class="relative group">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300 mb-2 px-1">Search catalog</label>
                        <svg class="absolute left-4 top-[2.65rem] h-5 w-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                        </svg>
                        <input v-model="search" type="text" placeholder="Title, author, ISBN, or accession"
                            class="w-full pl-11 pr-5 py-3 bg-white/60 dark:bg-slate-900/70 border-2 border-slate-100 dark:border-slate-700/70 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300 mb-2 px-1">Category</label>
                        <select v-model="category_id" class="w-full px-4 py-3 bg-white/60 dark:bg-slate-900/70 border-2 border-slate-100 dark:border-slate-700/70 rounded-2xl text-xs font-bold text-slate-800 dark:text-white">
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300 mb-2 px-1">Language</label>
                        <select v-model="language" class="w-full px-4 py-3 bg-white/60 dark:bg-slate-900/70 border-2 border-slate-100 dark:border-slate-700/70 rounded-2xl text-xs font-bold text-slate-800 dark:text-white">
                            <option value="">All Languages</option>
                            <option value="English">English</option>
                            <option value="Sinhala">Sinhala</option>
                            <option value="Tamil">Tamil</option>
                        </select>
                    </div>

                    <button @click="available = available === 'true' ? 'false' : 'true'"
                        class="w-full px-4 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 border-2"
                        :class="available === 'true' ? 'bg-emerald-500/10 border-emerald-500/50 text-emerald-600 dark:text-emerald-300' : 'bg-white/40 dark:bg-slate-900/50 border-slate-100 dark:border-slate-700/70 text-slate-500 dark:text-slate-300'">
                        <div class="w-1.5 h-1.5 rounded-full" :class="available === 'true' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></div>
                        In Stock Only
                    </button>

                    <button v-if="search || category_id || language || available === 'true'"
                        @click="resetFilters"
                        class="px-4 py-3 rounded-2xl border border-rose-500/20 bg-rose-500/10 text-[10px] font-black uppercase tracking-widest text-rose-500 hover:bg-rose-500/15">
                        Reset
                    </button>
                </div>
            </section>

            <section class="glass-card rounded-3xl overflow-hidden">
            <div class="lg:hidden divide-y divide-white/5 dark:divide-slate-800/30">
                <div v-if="!books.data?.length" class="px-5 py-14 text-center">
                    <div class="text-sm font-black text-slate-700 dark:text-slate-200">No books match the current filters</div>
                    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 mt-2">Try widening your search or add a new title</div>
                </div>

                <article v-for="book in books.data" :key="`mobile-${book.id}`" class="p-4 space-y-3">
                    <div>
                        <div class="text-sm font-black text-slate-800 dark:text-slate-100">{{ book.title }}</div>
                        <div class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest mt-1">{{ book.author }}</div>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300">
                        <span>{{ book.isbn || 'NO ISBN' }}</span>
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">{{ book.category.name }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="text-right">
                            <span class="text-[10px] font-black uppercase tracking-widest" :class="book.available_quantity > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-500'">
                                {{ book.available_quantity }} / {{ book.total_quantity }} ACTIVE
                            </span>
                            <div class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300">
                                Lost {{ book.lost_copies_count || 0 }} · Damaged {{ book.damaged_copies_count || 0 }}
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="p-2 bg-white/80 dark:bg-slate-800/60 border border-slate-300/70 dark:border-slate-700/50 text-slate-700 dark:text-slate-300 hover:text-indigo-600 hover:bg-indigo-500/10 dark:hover:text-indigo-300 rounded-xl transition-all" title="Edit UI not yet connected">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                            </button>
                            <button @click="deleteBook(book.id)" class="p-2 bg-white/80 dark:bg-slate-800/60 border border-slate-300/70 dark:border-slate-700/50 text-slate-700 dark:text-slate-300 hover:text-rose-500 hover:bg-rose-500/10 dark:hover:text-rose-300 rounded-xl transition-all">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>
                    </div>
                </article>
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full min-w-245 text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-900/50">
                            <th class="px-4 sm:px-6 lg:px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none">Book Details</th>
                            <th class="px-4 sm:px-6 lg:px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none">Identification</th>
                            <th class="px-4 sm:px-6 lg:px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none">Category</th>
                            <th class="px-4 sm:px-6 lg:px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none text-center">Stock Level</th>
                            <th class="px-4 sm:px-6 lg:px-8 py-4 text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-none text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 dark:divide-slate-800/30">
                        <tr v-if="!books.data?.length">
                            <td colspan="5" class="px-8 py-16 text-center">
                                <div class="text-sm font-black text-slate-700 dark:text-slate-200">No books match the current filters</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 mt-2">Try widening your search or add a new title</div>
                            </td>
                        </tr>
                        <tr v-for="book in books.data" :key="book.id" class="group hover:bg-white/40 dark:hover:bg-white/5 transition-colors">
                            <td class="px-4 sm:px-6 lg:px-8 py-5">
                                <div class="text-sm font-black text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ book.title }}</div>
                                <div class="text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-tighter mt-0.5">{{ book.author }}</div>
                            </td>
                            <td class="px-4 sm:px-6 lg:px-8 py-5 text-xs text-slate-700 dark:text-slate-300 font-mono tracking-tighter">{{ book.isbn || 'NO ISBN' }}</td>
                            <td class="px-4 sm:px-6 lg:px-8 py-5">
                                <span class="px-3 py-1 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-500/20">{{ book.category.name }}</span>
                            </td>
                            <td class="px-4 sm:px-6 lg:px-8 py-5">
                                <div class="flex flex-col items-center">
                                    <div class="w-24 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden mb-1.5 border border-white/10 dark:border-slate-700/50">
                                        <div class="h-full rounded-full transition-all duration-1000" 
                                            :class="book.available_quantity > 0 ? 'bg-indigo-500' : 'bg-rose-500'"
                                            :style="{ width: (book.available_quantity / book.total_quantity * 100) + '%' }"></div>
                                    </div>
                                    <div class="text-center">
                                        <span class="text-[10px] font-black uppercase tracking-widest" :class="book.available_quantity > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-500'">
                                            {{ book.available_quantity }} / {{ book.total_quantity }} ACTIVE
                                        </span>
                                        <div class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300">
                                            Issued {{ book.issued_copies_count || 0 }} · Lost {{ book.lost_copies_count || 0 }} · Damaged {{ book.damaged_copies_count || 0 }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 lg:px-8 py-5 text-right">
                                <div class="flex justify-end space-x-2">
                                    <button class="p-2 bg-white/80 dark:bg-slate-800/60 border border-slate-300/70 dark:border-slate-700/50 text-slate-700 dark:text-slate-300 hover:text-indigo-600 hover:bg-indigo-500/10 dark:hover:text-indigo-300 rounded-xl transition-all hover:scale-110 active:scale-90" title="Edit UI not yet connected">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                    </button>
                                    <button @click="deleteBook(book.id)" class="p-2 bg-white/80 dark:bg-slate-800/60 border border-slate-300/70 dark:border-slate-700/50 text-slate-700 dark:text-slate-300 hover:text-rose-500 hover:bg-rose-500/10 dark:hover:text-rose-300 rounded-xl transition-all hover:scale-110 active:scale-90">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-4 sm:px-6 lg:px-8 py-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-white/5 dark:border-slate-800/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Displaying {{ books.from }} – {{ books.to }} of {{ books.total }} Records</span>
                <PaginationControls :links="books.links || []" />
            </div>
            </section>
        </div>

        <!-- Add Book Modal -->
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showAddModal" class="fixed inset-0 bg-white/24 dark:bg-black/60 backdrop-blur-md flex items-center justify-center p-4 z-50">
                <div class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-4xl border border-white/70 bg-white/78 shadow-[0_24px_80px_rgba(15,23,42,0.22)] backdrop-blur-2xl dark:border-slate-700/30 dark:bg-slate-900/40">
                    <div class="px-4 sm:px-8 py-4 sm:py-6 border-b border-slate-200/70 dark:border-slate-800/50 flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Register New Book</h3>
                            <p class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest mt-0.5">Inventory Catalog Management</p>
                        </div>
                        <button @click="showAddModal = false" class="p-2 text-slate-500 hover:text-slate-700 dark:hover:text-white transition-all hover:rotate-90">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitAddForm" class="p-4 sm:p-8 space-y-5 sm:space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-600 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">Book Title</label>
                                <input v-model="addForm.title" type="text" required
                                    class="w-full px-5 py-3.5 bg-white/72 dark:bg-slate-950/50 border-2 border-slate-300/70 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-900 dark:text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                <p v-if="addForm.errors.title" class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-widest">{{ addForm.errors.title }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-600 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">Primary Author</label>
                                <input v-model="addForm.author" type="text" required
                                    class="w-full px-5 py-3.5 bg-white/72 dark:bg-slate-950/50 border-2 border-slate-300/70 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-900 dark:text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">ISBN Number</label>
                                <input v-model="addForm.isbn" type="text"
                                    class="w-full px-5 py-3.5 bg-white/72 dark:bg-slate-950/50 border-2 border-slate-300/70 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-900 dark:text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">Category</label>
                                <select v-model="addForm.category_id" required
                                    class="w-full px-5 py-3.5 bg-white/72 dark:bg-slate-950/50 border-2 border-slate-300/70 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                    <option value="" disabled>Choose Category…</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">Language</label>
                                <input v-model="addForm.language" type="text"
                                    class="w-full px-5 py-3.5 bg-white/72 dark:bg-slate-950/50 border-2 border-slate-300/70 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-900 dark:text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-600 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">Initial Stock</label>
                                <input v-model="addForm.total_quantity" type="number" min="1" required
                                    class="w-full px-5 py-3.5 bg-white/72 dark:bg-slate-950/50 border-2 border-slate-300/70 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-900 dark:text-white placeholder:text-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="showAddModal = false"
                                class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-600 hover:text-slate-800 dark:hover:text-white transition-colors">Discard</button>
                            <button type="submit" :disabled="addForm.processing"
                                class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-2xl shadow-2xl shadow-indigo-500/20 hover:glow-indigo transition-all active:scale-95 disabled:opacity-50">Register Book</button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </AppLayout>
</template>
