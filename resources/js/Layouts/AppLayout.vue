<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { useFullScreen } from '@/Composables/useFullScreen';
import { Link, Head, usePage } from '@inertiajs/vue3';
import AlertDialog from '@/Components/AlertDialog.vue';
import { useAlert } from '@/composables/useAlert';

defineProps({
    title: String,
    noScroll: {
        type: Boolean,
        default: false
    }
});

const page = usePage();
const isMobileMenuOpen = ref(false);
const alertDialogRef = ref(null);
const { setAlertComponent } = useAlert();

onMounted(() => {
    if (alertDialogRef.value) {
        setAlertComponent(alertDialogRef.value);
    }
});

const isDark = ref(
    typeof window !== 'undefined'
        ? localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
        : false
);

watch(isDark, (val) => {
    if (typeof document !== 'undefined') {
        document.documentElement.classList.toggle('dark', val);
        localStorage.setItem('theme', val ? 'dark' : 'light');
    }
}, { immediate: true });

const navLinks = computed(() => {
    const permissions = page.props.auth?.permissions ?? {};

    return [
        { label: 'Dashboard', href: '/dashboard', active: page.url === '/dashboard', visible: true },
        { label: 'Checkout', href: '/issues/pos', active: page.url === '/issues/pos', visible: !!permissions.can_manage_circulation },
        { label: 'Books', href: '/books', active: page.url.startsWith('/books'), visible: !!permissions.can_manage_catalog },
        { label: 'Categories', href: '/categories', active: page.url.startsWith('/categories'), visible: !!permissions.can_manage_catalog },
        { label: 'Members', href: '/members', active: page.url.startsWith('/members'), visible: !!permissions.can_manage_catalog },
        { label: 'Book Issues', href: '/issues', active: page.url === '/issues', visible: !!permissions.can_manage_circulation },
        { label: 'Reports', href: '/reports', active: page.url.startsWith('/reports'), visible: !!permissions.can_view_reports },
        { label: 'Settings', href: '/settings', active: page.url.startsWith('/settings'), visible: !!permissions.can_manage_settings },
    ].filter((item) => item.visible);
});

watch(() => page.url, () => {
    isMobileMenuOpen.value = false;
});

const { isFullScreen, toggleFullScreen } = useFullScreen();
</script>

<template>
    <div class="min-h-screen bg-transparent flex transition-colors duration-500 relative overflow-hidden">
        <!-- Background decorative blobs -->
        <div class="absolute top-0 -left-4 w-72 h-72 bg-indigo-500/10 dark:bg-indigo-500/5 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute bottom-0 -right-4 w-96 h-96 bg-rose-500/10 dark:bg-rose-500/5 rounded-full blur-3xl opacity-50"></div>

        <Head :title="title" />

        <div v-if="isMobileMenuOpen" class="fixed inset-0 bg-slate-950/40 dark:bg-black/60 backdrop-blur-sm z-30 xl:hidden" @click="isMobileMenuOpen = false"></div>

        <!-- Desktop Sidebar -->
        <aside class="w-72 glass-ghost m-4 mr-0 rounded-3xl shrink-0 hidden xl:flex flex-col z-20">
            <div class="p-8 text-2xl font-black tracking-tight bg-linear-to-br from-indigo-600 to-indigo-400 bg-clip-text text-transparent border-b border-white/10 dark:border-slate-800/50">
                {{ $page.props.settings?.library_name ?? 'Global' }}
            </div>

            <nav class="flex-1 p-6 space-y-2">
                <Link v-for="item in navLinks" :key="item.href" :href="item.href" class="group flex items-center space-x-3 px-4 py-3 rounded-2xl transition-all duration-300"
                    :class="[item.active ? 'bg-indigo-600 text-white glow-indigo shadow-indigo-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-800/50 hover:text-indigo-600 dark:hover:text-indigo-400']">
                    <span class="font-bold">{{ item.label }}</span>
                </Link>
            </nav>

            <div class="p-6 border-t border-white/10 dark:border-slate-800/50" v-if="$page.props.auth?.user">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-600 font-bold">
                        {{ $page.props.auth.user.name[0] }}
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs text-slate-500 dark:text-slate-500 truncate">{{ $page.props.auth.user.role }}</div>
                        <div class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ $page.props.auth.user.name }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile & Tablet Drawer -->
        <aside class="fixed top-0 left-0 h-full w-[86vw] max-w-xs glass-ghost rounded-r-3xl z-40 transform transition-transform duration-300 xl:hidden"
            :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="p-6 border-b border-white/10 dark:border-slate-800/50 flex items-center justify-between">
                <div class="text-lg font-black tracking-tight bg-linear-to-br from-indigo-600 to-indigo-400 bg-clip-text text-transparent">
                    {{ $page.props.settings?.library_name ?? 'Global' }}
                </div>
                <button @click="isMobileMenuOpen = false" class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:hover:text-slate-200">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <nav class="p-4 space-y-2">
                <Link v-for="item in navLinks" :key="`mobile-${item.href}`" :href="item.href" class="group flex items-center px-4 py-3 rounded-2xl transition-all duration-300"
                    :class="[item.active ? 'bg-indigo-600 text-white glow-indigo shadow-indigo-500/20' : 'text-slate-600 dark:text-slate-300 hover:bg-white/60 dark:hover:bg-slate-800/50 hover:text-indigo-600 dark:hover:text-indigo-400']">
                    <span class="font-bold">{{ item.label }}</span>
                </Link>
            </nav>
            <div class="px-4 pb-6 mt-auto" v-if="$page.props.auth?.user">
                <div class="p-4 border border-white/20 dark:border-slate-700/40 rounded-2xl bg-white/40 dark:bg-slate-900/40">
                    <div class="text-[10px] uppercase tracking-widest font-black text-slate-400">{{ $page.props.auth.user.role }}</div>
                    <div class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">{{ $page.props.auth.user.name }}</div>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="min-h-16 shrink-0 flex items-center justify-between gap-3 px-4 sm:px-6 lg:px-8 glass-ghost m-3 sm:m-4 xl:ml-0 rounded-3xl z-20">
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="isMobileMenuOpen = true" class="xl:hidden p-2 rounded-xl bg-slate-100/60 dark:bg-slate-800/60 text-slate-500 dark:text-slate-300 border border-slate-200/50 dark:border-slate-700/40">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h2 class="text-[10px] sm:text-xs font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-widest truncate"><slot name="header" /></h2>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="toggleFullScreen"
                        class="p-1.5 sm:p-2 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition border border-slate-200/50 dark:border-slate-700/40 shrink-0">
                        <svg v-if="!isFullScreen" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        <svg v-else class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <button @click="isDark = !isDark"
                        class="p-1.5 sm:p-2 rounded-lg bg-slate-100/60 dark:bg-slate-800/60 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition border border-slate-200/50 dark:border-slate-700/40 shrink-0">
                        <svg v-if="!isDark" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg v-else class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m3.343-5.657l-.707.707m12.728 12.728l-.707.707M6.343 17.657l-.707-.707M17.657 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>
                </div>
            </header>
            <div class="flex-1 px-3 pb-3 sm:px-4 sm:pb-4 lg:px-6 lg:pb-6 xl:px-6 xl:pb-6"
                :class="noScroll ? 'overflow-hidden flex flex-col' : 'overflow-y-auto'">
                <slot />
            </div>
        </main>

        <!-- Alert Dialog (Global) -->
        <AlertDialog ref="alertDialogRef" />
    </div>
</template>
