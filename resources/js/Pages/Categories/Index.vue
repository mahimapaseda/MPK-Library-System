<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

defineProps({
    categories: Array
});

const form = useForm({ name: '', description: '' });

const submit = () => {
    form.post('/categories', {
        onSuccess: () => form.reset(),
    });
};

const deleteCategory = (id) => {
    if (confirm('Are you sure? This will fail if books exist in this category.')) {
        router.delete(`/categories/${id}`);
    }
}
</script>

<template>
    <AppLayout title="Categories">
        <template #header>Book Categories</template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Add Form -->
            <div class="glass-card rounded-3xl p-8 h-fit sticky top-6 border-white/20 dark:border-slate-800/50">
                <div class="mb-6">
                    <h3 class="text-xl font-black text-slate-800 dark:text-white tracking-tight">New Category</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Classification Management</p>
                </div>
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Category Name</label>
                        <input v-model="form.name" type="text" required placeholder="e.g. Science Fiction"
                            class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        <p v-if="form.errors.name" class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-widest">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Abstract / Description</label>
                        <textarea v-model="form.description" rows="4" placeholder="Briefly describe this category assets…"
                            class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none"></textarea>
                    </div>
                    <button type="submit" :disabled="form.processing"
                        class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-2xl shadow-2xl shadow-indigo-500/20 hover:glow-indigo transition-all active:scale-[0.98] disabled:opacity-50">
                        Register Category
                    </button>
                </form>
            </div>

            <!-- List -->
            <div class="lg:col-span-2 glass-card rounded-3xl overflow-hidden border-white/20 dark:border-slate-800/50">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-900/50">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Class Name</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Description</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 dark:divide-slate-800/30">
                        <tr v-if="!categories?.length">
                            <td colspan="3" class="px-8 py-12 text-center">
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-relaxed">No categories established yet.<br>Create your first classification to organize the catalog.</div>
                            </td>
                        </tr>
                        <tr v-for="category in categories" :key="category.id" class="group hover:bg-white/40 dark:hover:bg-white/5 transition-all">
                            <td class="px-8 py-6">
                                <div class="text-sm font-black text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors uppercase tracking-tight">{{ category.name }}</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">System Entity</div>
                            </td>
                            <td class="px-8 py-6 text-xs font-bold text-slate-500 dark:text-slate-400 italic">
                                {{ category.description || 'No description provided' }}
                            </td>
                            <td class="px-8 py-6 text-right">
                                <button @click="deleteCategory(category.id)"
                                    class="px-4 py-2 bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95 border border-rose-500/20 opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0">
                                    Purge
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
