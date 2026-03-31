<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PaginationControls from '@/Components/PaginationControls.vue';
import { useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useAlert } from '@/composables/useAlert';

const props = defineProps({
    members: Object,
    memberTotals: Object,
    filters: Object
});

const search = ref(props.filters?.search || '');
const filterType = ref(props.filters?.type || '');
const grade = ref(props.filters?.grade || '');
const showAddModal = ref(false);

const stats = computed(() => {
    return {
        visibleMembers: props.members?.total ?? 0,
        students: props.memberTotals?.students ?? 0,
        teachers: props.memberTotals?.teachers ?? 0,
        staff: props.memberTotals?.staff ?? 0,
    };
});

const { confirm } = useAlert();

const memberForm = useForm({
    member_id: '',
    name: '',
    email: '',
    password: '',
    type: 'student',
    grade: '',
    contact_number: ''
});

const submitAddForm = () => {
    memberForm.post('/members', {
        onSuccess: () => {
            showAddModal.value = false;
            memberForm.reset();
        }
    });
};

// Multi-parameter filter watch
let timeout = null;
watch(() => ({
    search: search.value,
    type: filterType.value,
    grade: grade.value
}), (params) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get('/members', params, { 
            preserveState: true, 
            replace: true,
            preserveScroll: true
        });
    }, 300);
}, { deep: true });

const resetFilters = () => {
    search.value = '';
    filterType.value = '';
    grade.value = '';
    router.get('/members', {}, { preserveState: false });
};

const deleteMember = async (id) => {
    const confirmed = await confirm('Are you sure you want to delete this member?', {
        title: 'Delete Member',
        type: 'error',
        confirmText: 'Delete'
    });
    if (confirmed) {
        router.delete(`/members/${id}`);
    }
}
</script>

<template>
    <AppLayout title="Members Management" :noScroll="true">
        <template #header>Library Members</template>

        <div class="space-y-5">
            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Visible members</div>
                    <div class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ stats.visibleMembers }}</div>
                </div>
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Students</div>
                    <div class="mt-2 text-3xl font-black text-blue-600 dark:text-blue-300">{{ stats.students }}</div>
                </div>
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Teachers</div>
                    <div class="mt-2 text-3xl font-black text-violet-600 dark:text-violet-300">{{ stats.teachers }}</div>
                </div>
                <div class="glass-card rounded-3xl p-5">
                    <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">Staff</div>
                    <div class="mt-2 text-3xl font-black text-slate-700 dark:text-slate-100">{{ stats.staff }}</div>
                </div>
            </section>

            <section class="glass-card rounded-3xl p-4 sm:p-5 lg:p-6 space-y-4">
                <div class="flex flex-col xl:flex-row xl:items-center gap-4 justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Member registry workspace</h3>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 mt-1">Search, segment, and onboard borrowers</p>
                    </div>
                    <button @click="showAddModal = true"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-sm font-black uppercase tracking-widest shadow-2xl shadow-indigo-500/20 transition-all flex items-center gap-2 shrink-0 active:scale-95 w-full xl:w-auto justify-center">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                        Add New Member
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-[minmax(0,1.4fr)_auto_16rem_auto] gap-3 items-end">
                    <div class="relative group">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300 mb-2 px-1">Search registry</label>
                        <svg class="absolute left-4 top-[2.65rem] h-5 w-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                        </svg>
                        <input v-model="search" type="text" placeholder="Name, member ID, or grade"
                            class="w-full pl-11 pr-5 py-3 bg-white/60 dark:bg-slate-900/70 border-2 border-slate-100 dark:border-slate-700/70 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400">
                    </div>

                    <div class="flex items-center bg-white/40 dark:bg-slate-900/50 border-2 border-slate-100 dark:border-slate-700/70 rounded-2xl p-1 overflow-x-auto">
                        <button v-for="t in ['', 'student', 'teacher', 'staff']" :key="t"
                            @click="filterType = t"
                            class="px-3 py-2 rounded-xl text-xs font-bold transition-all capitalize whitespace-nowrap"
                            :class="filterType === t ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-500 dark:text-slate-300 hover:bg-white/60 dark:hover:bg-slate-800/60'">
                            {{ t || 'All' }}
                        </button>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300 mb-2 px-1">Grade / Dept</label>
                        <input v-model="grade" type="text" placeholder="Grade filter"
                            class="w-full px-4 py-3 bg-white/60 dark:bg-slate-900/70 border-2 border-slate-100 dark:border-slate-700/70 rounded-2xl text-xs font-bold text-slate-800 dark:text-white placeholder:text-slate-400">
                    </div>

                    <button v-if="search || filterType || grade"
                        @click="resetFilters"
                        class="px-4 py-3 rounded-2xl border border-rose-500/20 bg-rose-500/10 text-[10px] font-black uppercase tracking-widest text-rose-500 hover:bg-rose-500/15">
                        Reset
                    </button>
                </div>
            </section>

            <section class="glass-card rounded-3xl overflow-hidden flex flex-col max-h-[calc(100vh-460px)]">
            <div class="overflow-x-auto overflow-y-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-900/50">
                            <th class="px-8 py-4 text-[10px] font-black text-slate-800 dark:text-slate-400 uppercase tracking-widest leading-none">Member Registry</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-800 dark:text-slate-400 uppercase tracking-widest leading-none">Classification</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-800 dark:text-slate-400 uppercase tracking-widest leading-none">Grade / Dept</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-800 dark:text-slate-400 uppercase tracking-widest leading-none">Contact Info</th>
                            <th class="px-8 py-4 text-[10px] font-black text-slate-800 dark:text-slate-400 uppercase tracking-widest leading-none text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 dark:divide-slate-800/30">
                        <tr v-if="!members.data?.length">
                            <td colspan="5" class="px-8 py-16 text-center">
                                <div class="text-sm font-black text-slate-700 dark:text-slate-200">No members match the current filters</div>
                                <div class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-300 mt-2">Try another segment or add a new registry entry</div>
                            </td>
                        </tr>
                        <tr v-for="member in members.data" :key="member.id" class="group hover:bg-white/40 dark:hover:bg-white/5 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-full bg-linear-to-br from-indigo-500/20 to-slate-500/10 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black text-xs border border-indigo-500/20 group-hover:scale-110 transition-transform">
                                        {{ member.name[0] }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-black text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ member.name }}</div>
                                        <div class="text-[10px] font-bold text-slate-800 dark:text-slate-300 uppercase tracking-tighter mt-0.5 font-mono">{{ member.member_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border" :class="{
                                    'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20': member.type === 'student',
                                    'bg-violet-500/10 text-violet-600 dark:text-violet-400 border-violet-500/20': member.type === 'teacher',
                                    'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20': member.type === 'staff',
                                }">{{ member.type }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-xs font-bold text-slate-800 dark:text-slate-300">{{ member.grade || '—' }}</td>
                            <td class="px-8 py-5 text-xs font-bold text-slate-800 dark:text-slate-300 tracking-tighter">{{ member.contact_number || 'NO CONTACT' }}</td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex justify-end space-x-2">
                                    <button class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-500/10 dark:hover:text-indigo-400 rounded-xl transition-all hover:scale-110 active:scale-90" title="Edit UI not yet connected">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                    </button>
                                    <button @click="deleteMember(member.id)" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-500/10 dark:hover:text-rose-400 rounded-xl transition-all hover:scale-110 active:scale-90">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-4 bg-slate-50/50 dark:bg-slate-900/50 border-t border-white/5 dark:border-slate-800/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shrink-0">
                <span class="text-[10px] font-black text-slate-600 dark:text-slate-400 uppercase tracking-widest">Displaying {{ members.from }} – {{ members.to }} of {{ members.total }} Records</span>
                <PaginationControls :links="members.links || []" />
            </div>
            </section>

        </div>

        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
            <div v-if="showAddModal" class="fixed inset-0 bg-slate-900/20 dark:bg-black/60 backdrop-blur-md flex items-center justify-center p-4 z-50">
                <div class="bg-white/80 dark:bg-slate-900/40 backdrop-blur-2xl rounded-4xl shadow-2xl border-2 border-white/50 dark:border-slate-700/30 w-full max-w-lg overflow-hidden">
                    <div class="px-8 py-6 border-b border-white/10 dark:border-slate-800/50 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Onboard New Member</h3>
                            <p class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest mt-0.5">Member Database Management</p>
                        </div>
                        <button @click="showAddModal = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-white transition-all hover:rotate-90">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitAddForm" class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-900 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">Member Identification</label>
                                <input v-model="memberForm.member_id" type="text" required placeholder="e.g. STU001"
                                    class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                <p v-if="memberForm.errors.member_id" class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-widest">{{ memberForm.errors.member_id }}</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-900 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">Account Type</label>
                                <select v-model="memberForm.type" required
                                    class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                    <option value="student">Student</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-900 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">Institutional Email</label>
                                <input v-model="memberForm.email" type="email" required placeholder="name@institution.com"
                                    class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                <p v-if="memberForm.errors.email" class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-widest">{{ memberForm.errors.email }}</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-900 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">Initial Password</label>
                                <input v-model="memberForm.password" type="password" required placeholder="Minimum 8 characters"
                                    class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                <p v-if="memberForm.errors.password" class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-widest">{{ memberForm.errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-900 dark:text-slate-300 uppercase tracking-widest mb-2 px-1">Contact Terminal</label>
                                <input v-model="memberForm.contact_number" type="text"
                                    class="w-full px-5 py-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-2 border-slate-100 dark:border-slate-800/50 rounded-2xl text-sm font-bold text-slate-800 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="showAddModal = false"
                                class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-600 hover:text-slate-800 dark:hover:text-white transition-colors">Discard</button>
                            <button type="submit" :disabled="memberForm.processing"
                                class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-2xl shadow-2xl shadow-indigo-500/20 hover:glow-indigo transition-all active:scale-95 disabled:opacity-50">Create Registry</button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </AppLayout>
</template>
