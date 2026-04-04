<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useAlert } from '@/composables/useAlert';

const props = defineProps({
    categories: Array,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');

const stats = computed(() => ({
    total: props.categories?.length ?? 0,
    withDescriptions: (props.categories ?? []).filter((category) => category.description).length,
    withoutDescriptions: (props.categories ?? []).filter((category) => !category.description).length,
}));

const form = useForm({ name: '', description: '' });
const { confirm } = useAlert();

const submit = () => {
    form.post('/categories', {
        onSuccess: () => form.reset(),
    });
};

const deleteCategory = async (id) => {
    const confirmed = await confirm('Are you sure? This will fail if books exist in this category.', {
        title: 'Delete Category',
        type: 'warning',
        confirmText: 'Delete'
    });
    if (confirmed) {
        router.delete(`/categories/${id}`);
    }
};

const applySearch = () => {
    router.get('/categories', {
        search: searchQuery.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearSearch = () => {
    searchQuery.value = '';
    applySearch();
};
</script>

<template>
    <AppLayout title="Categories" :noScroll="true">
        <template #header>Book Categories</template>

        <div class="space-y-5">
            <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Total categories</div>
                    <div class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ stats.total }}</div>
                </div>
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Documented</div>
                    <div class="mt-2 text-3xl font-black text-indigo-600 dark:text-indigo-300">{{ stats.withDescriptions }}</div>
                </div>
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Need descriptions</div>
                    <div class="mt-2 text-3xl font-black text-amber-600 dark:text-amber-300">{{ stats.withoutDescriptions }}</div>
                </div>
            </section>

            <div class="grid grid-cols-1 xl:grid-cols-[23rem_minmax(0,1fr)] gap-5">
                <section class="glass-card rounded-3xl p-6 h-fit xl:sticky xl:top-6 border-white/20 dark:border-slate-800/50">
                    <div class="mb-6">
                        <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">New category</h3>
                        <p class="text-[10px] font-bold text-slate-500 dark:text-slate-300 uppercase tracking-widest mt-0.5">Classification management</p>
                    </div>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">Category Name</label>
                            <input v-model="form.name" type="text" required placeholder="e.g. Science Fiction"
                                class="w-full px-5 py-3.5 bg-slate-50/60 dark:bg-slate-900/70 border-2 border-slate-100 dark:border-slate-700/70 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400">
                            <p v-if="form.errors.name" class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-widest">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">Description</label>
                            <textarea v-model="form.description" rows="5" placeholder="Describe this category and the type of materials it contains"
                                class="w-full px-5 py-3.5 bg-slate-50/60 dark:bg-slate-900/70 border-2 border-slate-100 dark:border-slate-700/70 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 resize-none"></textarea>
                        </div>
                        <button type="submit" :disabled="form.processing"
                            class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-2xl shadow-2xl shadow-indigo-500/20 transition-all active:scale-[0.98] disabled:opacity-50">
                            Register Category
                        </button>
                    </form>
                </section>

                <section class="glass-card rounded-3xl p-5 sm:p-6 border-white/20 dark:border-slate-800/50 flex flex-col xl:max-h-[calc(100vh-420px)] xl:overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-5 shrink-0">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white">Category registry</h3>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 mt-1">Catalog taxonomy overview</p>
                        </div>
                    </div>

                    <form @submit.prevent="applySearch" class="mb-4 shrink-0 flex flex-col sm:flex-row sm:items-center gap-2">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search category name or description"
                            class="w-full sm:flex-1 px-4 py-3 bg-slate-50/70 dark:bg-slate-900/70 border border-slate-200/70 dark:border-slate-700/70 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400"
                        />
                        <button
                            type="submit"
                            class="w-full sm:w-auto px-4 py-3 rounded-xl bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest"
                        >
                            Search
                        </button>
                        <button
                            v-if="searchQuery"
                            type="button"
                            @click="clearSearch"
                            class="w-full sm:w-auto px-3 py-3 rounded-xl bg-slate-100/80 dark:bg-slate-800/80 border border-slate-200/70 dark:border-slate-700/70 text-[10px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-200"
                        >
                            Clear
                        </button>
                    </form>

                    <div v-if="!categories?.length" class="rounded-3xl border border-dashed border-slate-300 dark:border-slate-700/70 py-16 text-center flex-1 flex flex-col items-center justify-center">
                        <div class="text-sm font-black text-slate-700 dark:text-slate-200">
                            {{ searchQuery ? 'No categories matched your search' : 'No categories established yet' }}
                        </div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 mt-2">
                            {{ searchQuery ? 'Try another keyword or clear search' : 'Create your first classification to organize the catalog' }}
                        </div>
                    </div>

                    <div v-else class="space-y-3 xl:overflow-y-auto xl:pr-2">
                        <div v-for="category in categories" :key="category.id" class="rounded-3xl border border-slate-200/70 dark:border-slate-700/70 bg-white/40 dark:bg-slate-900/50 p-5 flex flex-col sm:flex-row sm:items-start gap-4 sm:justify-between shrink-0">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-3 flex-wrap">
                                    <h4 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ category.name }}</h4>
                                    <span class="px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 border border-indigo-500/20 text-[10px] font-black uppercase tracking-widest">Category</span>
                                </div>
                                <p class="mt-3 text-sm font-bold text-slate-600 dark:text-slate-300 leading-relaxed">{{ category.description || 'No description provided yet.' }}</p>
                            </div>
                            <div class="sm:pt-1 shrink-0">
                                <button @click="deleteCategory(category.id)"
                                    class="px-4 py-2 bg-rose-500/10 text-rose-600 dark:text-rose-300 hover:bg-rose-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 border border-rose-500/20">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
