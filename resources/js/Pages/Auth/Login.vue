<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-transparent relative overflow-hidden">
        <Head title="Log in" />

        <!-- Background orbs -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-400/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-violet-400/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-sm mx-4 bg-white/10 dark:bg-slate-900/40 backdrop-blur-2xl rounded-3xl shadow-2xl shadow-indigo-500/10 dark:shadow-none border border-white/20 dark:border-slate-700/40 p-8">
            <!-- Logo mark -->
            <div class="mb-7 text-center">
                <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-linear-to-br from-indigo-500 to-violet-600 shadow-lg shadow-indigo-500/30 mb-4">
                    <svg class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-semibold text-slate-800 dark:text-slate-100">School Library</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Sign in to your account</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-800 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Email</label>
                    <input v-model="form.email" type="email" required autocomplete="email"
                        class="w-full px-4 py-2.5 bg-slate-50/80 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/50 rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/40 focus:border-transparent transition"
                        placeholder="admin@school.lk">
                    <p v-if="form.errors.email" class="mt-1.5 text-xs text-rose-500">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-800 dark:text-slate-400 mb-1.5 uppercase tracking-wide">Password</label>
                    <input v-model="form.password" type="password" required autocomplete="current-password"
                        class="w-full px-4 py-2.5 bg-slate-50/80 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/50 rounded-xl text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/40 focus:border-transparent transition"
                        placeholder="••••••••">
                </div>

                <button type="submit" :disabled="form.processing"
                    class="w-full mt-2 py-2.5 bg-indigo-500 hover:bg-indigo-600 active:bg-indigo-700 disabled:opacity-60 text-white font-semibold text-sm rounded-xl shadow-sm shadow-indigo-500/20 transition">
                    {{ form.processing ? 'Signing in…' : 'Sign In' }}
                </button>

                <p class="text-center text-xs text-slate-600 dark:text-slate-500 pt-1">
                    Default: admin@school.lk / password
                </p>
            </form>
        </div>
    </div>
</template>
