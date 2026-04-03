<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/member/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Member Login" />

    <div class="min-h-screen bg-transparent flex items-center justify-center p-6 relative overflow-hidden font-sans">
        <!-- Ambient Glow -->
        <div class="absolute top-1/4 -left-20 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-1/4 -right-20 w-96 h-96 bg-violet-600/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Logo area -->
            <div class="flex flex-col items-center mb-10 text-center">
                <div class="h-16 w-16 rounded-2xl bg-linear-to-br from-indigo-600 to-violet-600 flex items-center justify-center shadow-2xl shadow-indigo-500/40 glow-indigo mb-6 scale-110">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h1 class="text-3xl font-black tracking-tighter text-white uppercase">Library <span class="text-indigo-500">Member</span></h1>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-2">Personal Resource Access</p>
            </div>

            <div class="glass-card p-10 rounded-[2.5rem] shadow-2xl border border-white/5 relative h-full">
                <!-- Header -->
                <div class="mb-8">
                    <h2 class="text-xl font-black text-white tracking-tight uppercase">Sign In</h2>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest leading-relaxed">Enter your institutional credentials to access your dashboard</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2 ml-1">Email Address</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white font-bold text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-700"
                            placeholder="name@institution.com"
                        />
                        <div v-if="form.errors.email" class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-widest">{{ form.errors.email }}</div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2 ml-1">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500">Security Password</label>
                            <a href="#" class="text-[9px] font-black uppercase tracking-widest text-indigo-400 hover:text-indigo-300 transition-colors">Forgot?</a>
                        </div>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            class="w-full px-5 py-4 bg-white/5 border border-white/10 rounded-2xl text-white font-bold text-sm focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all placeholder:text-slate-700"
                            placeholder="••••••••"
                        />
                        <div v-if="form.errors.password" class="mt-2 text-[10px] font-bold text-rose-500 uppercase tracking-widest">{{ form.errors.password }}</div>
                    </div>

                    <div class="flex items-center gap-3 ml-1">
                        <input type="checkbox" v-model="form.remember" id="remember" class="w-4 h-4 rounded bg-white/5 border-white/10 text-indigo-600 focus:ring-indigo-500/20">
                        <label for="remember" class="text-[10px] font-black uppercase tracking-widest text-slate-500 cursor-pointer select-none">Stay Signed In</label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-5 bg-indigo-600 hover:bg-indigo-700 text-white font-black uppercase tracking-widest rounded-2xl transition-all shadow-2xl shadow-indigo-500/30 hover:glow-indigo active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3">
                        <span v-if="form.processing">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                        <span v-else>Access Portal</span>
                    </button>
                </form>

                <div class="mt-10 pt-8 border-t border-white/5 text-center">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Administrative Access?</p>
                    <Link href="/login" class="mt-2 inline-block text-[10px] font-black uppercase tracking-widest text-indigo-400 hover:text-indigo-300 transition-colors">Go to Staff Login</Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.glass-card {
    -webkit-backdrop-filter: blur(32px) saturate(160%);
    backdrop-filter: blur(32px) saturate(160%);
    background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.08) 100%);
}
.glow-indigo {
    box-shadow: 0 0 50px -10px rgba(99,102,241,0.5);
}
</style>
