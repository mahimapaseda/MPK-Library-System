<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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

const deleteBook = (id) => {
    if (confirm('Are you sure you want to delete this book?')) {
        router.delete(`/books/${id}`);
    }
}
</script>

<template>
    <AppLayout title="Books Management">
        <template #header>Books Inventory</template>

        <div class="mb-5 sm:mb-6 flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <div class="relative w-full sm:w-96 group">
                    <svg class="absolute left-4 top-3.5 h-5 w-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                    </svg>
                    <input v-model="search" type="text" placeholder="Search by title, author, or ISBN…"
                        class="w-full pl-11 pr-5 py-3 bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                </div>
                <button @click="showAddModal = true"
                    class="px-5 sm:px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-xs sm:text-sm font-black uppercase tracking-widest shadow-2xl shadow-indigo-500/20 hover:glow-indigo transition-all flex items-center justify-center gap-2 shrink-0 active:scale-95 w-full sm:w-auto">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    Add New Book
                </button>
            </div>

            <!-- Filter Bar -->
            <div class="flex flex-wrap items-center gap-3">
                <select v-model="category_id" class="px-4 py-2 bg-white/30 dark:bg-slate-900/30 backdrop-blur-md border-2 border-slate-100 dark:border-slate-800/50 rounded-xl text-xs font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 transition-all">
                    <option value="">All Categories</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>

                <select v-model="language" class="px-4 py-2 bg-white/30 dark:bg-slate-900/30 backdrop-blur-md border-2 border-slate-100 dark:border-slate-800/50 rounded-xl text-xs font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 transition-all">
                    <option value="">All Languages</option>
                    <option value="English">English</option>
                    <option value="Sinhala">Sinhala</option>
                    <option value="Tamil">Tamil</option>
                </select>

                <button @click="available = available === 'true' ? 'false' : 'true'" 
                    class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all flex items-center gap-2 border-2"
                    :class="available === 'true' ? 'bg-emerald-500/10 border-emerald-500/50 text-emerald-600 dark:text-emerald-400' : 'bg-white/30 dark:bg-slate-900/30 border-slate-100 dark:border-slate-800/50 text-slate-400'">
                    <div class="w-1.5 h-1.5 rounded-full" :class="available === 'true' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></div>
                    In Stock Only
                </button>

                <button v-if="search || category_id || language || available === 'true'" 
                    @click="resetFilters"
                    class="text-[10px] font-black uppercase tracking-widest text-rose-500 hover:text-rose-600 transition-colors ml-auto">
                    Reset Filters
                </button>
            </div>
        </div>

        <div class="glass-card rounded-3xl overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full min-w-215 text-left border-collapse">
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
                        <tr v-for="book in books.data" :key="book.id" class="group hover:bg-white/40 dark:hover:bg-white/5 transition-colors">
                            <td class="px-4 sm:px-6 lg:px-8 py-5">
                                <div class="text-sm font-black text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ book.title }}</div>
                                <div class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-tighter mt-0.5">{{ book.author }}</div>
                            </td>
                            <td class="px-4 sm:px-6 lg:px-8 py-5 text-xs text-slate-700 dark:text-slate-400 font-mono tracking-tighter">{{ book.isbn || 'NO ISBN' }}</td>
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
                                    <span class="text-[10px] font-black uppercase tracking-widest" :class="book.available_quantity > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-rose-500'">
                                        {{ book.available_quantity }} / {{ book.total_quantity }} AVAIL.
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 lg:px-8 py-5 text-right">
                                <div class="flex justify-end space-x-2">
                                    <button class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-500/10 dark:hover:text-indigo-400 rounded-xl transition-all hover:scale-110 active:scale-90">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                    </button>
                                    <button @click="deleteBook(book.id)" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-500/10 dark:hover:text-rose-400 rounded-xl transition-all hover:scale-110 active:scale-90">
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
                <!-- Simplified Pagination placeholder for now -->
                <div class="flex gap-2">
                    <button class="px-3 py-1.5 glass-card text-[10px] font-black text-slate-400 uppercase tracking-widest rounded-lg hover:text-indigo-500 transition-colors cursor-not-allowed">Prev</button>
                    <button class="px-3 py-1.5 bg-indigo-600 text-[10px] font-black text-white uppercase tracking-widest rounded-lg glow-indigo transition-all">1</button>
                    <button class="px-3 py-1.5 glass-card text-[10px] font-black text-slate-400 uppercase tracking-widest rounded-lg hover:text-indigo-500 transition-colors cursor-not-allowed">Next</button>
                </div>
            </div>
        </div>

        <!-- Add Book Modal -->
        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showAddModal" class="fixed inset-0 bg-slate-950/40 dark:bg-black/60 backdrop-blur-md flex items-center justify-center p-4 z-50">
                <div class="glass-card rounded-4xl shadow-2xl border-white/20 dark:border-slate-700/30 w-full max-w-xl overflow-hidden">
                    <div class="px-8 py-6 border-b border-white/10 dark:border-slate-800/50 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">Register New Book</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Inventory Catalog Management</p>
                        </div>
                        <button @click="showAddModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white transition-all hover:rotate-90">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitAddForm" class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Book Title</label>
                                <input v-model="addForm.title" type="text" required
                                    class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                <p v-if="addForm.errors.title" class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-widest">{{ addForm.errors.title }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Primary Author</label>
                                <input v-model="addForm.author" type="text" required
                                    class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">ISBN Number</label>
                                <input v-model="addForm.isbn" type="text"
                                    class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Category</label>
                                <select v-model="addForm.category_id" required
                                    class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                    <option value="" disabled>Choose Category…</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Language</label>
                                <input v-model="addForm.language" type="text"
                                    class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Initial Stock</label>
                                <input v-model="addForm.total_quantity" type="number" min="1" required
                                    class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="showAddModal = false"
                                class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">Discard</button>
                            <button type="submit" :disabled="addForm.processing"
                                class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-2xl shadow-2xl shadow-indigo-500/20 hover:glow-indigo transition-all active:scale-95 disabled:opacity-50">Register Book</button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </AppLayout>
</template>
