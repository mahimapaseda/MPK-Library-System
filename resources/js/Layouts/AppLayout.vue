<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
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
const isSidebarCollapsed = ref(
    typeof window !== 'undefined'
        ? localStorage.getItem('sidebar-collapsed') === 'true'
        : false
);
const alertDialogRef = ref(null);
const { setAlertComponent } = useAlert();
const now = ref(new Date());
let clockTimer = null;

const formattedDate = computed(() => {
    return now.value.toLocaleDateString(undefined, {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
});

const formattedTime = computed(() => {
    return now.value.toLocaleTimeString(undefined, {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
});

const libraryName = computed(() => page.props.settings?.library_name ?? 'Global');
const libraryInitials = computed(() => {
    return libraryName.value
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0])
        .join('')
        .toUpperCase();
});

onMounted(() => {
    if (alertDialogRef.value) {
        setAlertComponent(alertDialogRef.value);
    }

    clockTimer = window.setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (clockTimer) {
        window.clearInterval(clockTimer);
    }
});

const isDark = ref(
    typeof window !== 'undefined'
    ? localStorage.getItem('theme') === 'dark'
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
        { label: 'Dashboard', href: '/dashboard', icon: 'dashboard', hint: 'Overview', active: page.url === '/dashboard', visible: true },
        { label: 'Checkout', href: '/issues/pos', icon: 'checkout', hint: 'Quick issue desk', active: page.url === '/issues/pos', visible: !!permissions.can_manage_circulation },
        { label: 'Books', href: '/books', icon: 'books', hint: 'Catalog', active: page.url.startsWith('/books'), visible: !!permissions.can_manage_catalog },
        { label: 'Categories', href: '/categories', icon: 'categories', hint: 'Shelves and genres', active: page.url.startsWith('/categories'), visible: !!permissions.can_manage_catalog },
        { label: 'Members', href: '/members', icon: 'members', hint: 'Readers', active: page.url.startsWith('/members'), visible: !!permissions.can_manage_catalog },
        { label: 'Book Issues', href: '/issues', icon: 'issues', hint: 'Circulation logs', active: page.url === '/issues', visible: !!permissions.can_manage_circulation },
        { label: 'Charges', href: '/fines', icon: 'issues', hint: 'Paid and waived', active: page.url.startsWith('/fines'), visible: !!permissions.can_manage_finances },
        { label: 'Reports', href: '/reports', icon: 'reports', hint: 'Analytics', active: page.url.startsWith('/reports'), visible: !!permissions.can_view_reports },
        { label: 'Settings', href: '/settings', icon: 'settings', hint: 'System config', active: page.url.startsWith('/settings'), visible: !!permissions.can_manage_settings },
    ].filter((item) => item.visible);
});

watch(() => page.url, () => {
    isMobileMenuOpen.value = false;
});

watch(isSidebarCollapsed, (val) => {
    if (typeof window !== 'undefined') {
        localStorage.setItem('sidebar-collapsed', val ? 'true' : 'false');
    }
});

const { isFullScreen, toggleFullScreen } = useFullScreen();
</script>

<template>
    <div class="h-screen bg-transparent flex transition-colors duration-500 relative overflow-hidden">
        <!-- Background decorative blobs -->
        <div class="absolute top-0 -left-4 w-72 h-72 bg-indigo-500/10 dark:bg-indigo-500/5 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute bottom-0 -right-4 w-96 h-96 bg-rose-500/10 dark:bg-rose-500/5 rounded-full blur-3xl opacity-50"></div>

        <Head :title="title" />

        <div v-if="isMobileMenuOpen" class="fixed inset-0 bg-slate-950/40 dark:bg-black/60 backdrop-blur-sm z-30 xl:hidden" @click="isMobileMenuOpen = false"></div>

        <!-- Desktop Sidebar -->
        <aside class="glass-ghost m-4 mr-0 rounded-3xl shrink-0 hidden xl:flex flex-col z-20 transition-all duration-300"
            :class="isSidebarCollapsed ? 'w-20' : 'w-72'">
            <div class="border-b border-white/10 dark:border-slate-800/50"
                :class="isSidebarCollapsed ? 'p-3' : 'p-4'">
                <div class="bg-white/50 dark:bg-slate-900/45 border border-white/40 dark:border-slate-700/40 flex items-center"
                    :class="isSidebarCollapsed ? 'rounded-3xl px-2 py-3 flex-col gap-3' : 'rounded-[28px] px-3 py-3 gap-3'">
                    <div class="shrink-0 bg-linear-to-br from-indigo-600 via-sky-500 to-cyan-400 text-white flex items-center justify-center shadow-[0_18px_40px_rgba(59,130,246,0.28)]"
                        :class="isSidebarCollapsed ? 'h-11 w-11 rounded-2xl' : 'h-12 w-12 rounded-[20px]'">
                        <span class="text-sm font-black tracking-[0.2em]">{{ libraryInitials }}</span>
                    </div>

                    <div v-if="!isSidebarCollapsed" class="min-w-0 flex-1">
                        <div class="text-[10px] font-black uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">Library Hub</div>
                        <div class="text-lg font-black text-slate-900 dark:text-white truncate">{{ libraryName }}</div>
                    </div>

                    <button @click="isSidebarCollapsed = !isSidebarCollapsed"
                        class="hidden xl:inline-flex items-center justify-center rounded-2xl bg-white/80 dark:bg-slate-800/70 text-slate-700 dark:text-slate-300 border border-slate-300/70 dark:border-slate-700/50 hover:text-indigo-600 dark:hover:text-indigo-300 transition-colors"
                        :class="isSidebarCollapsed ? 'h-10 w-10' : 'p-2'"
                        :title="isSidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                        <svg v-if="!isSidebarCollapsed" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
            </div>
            </div>

            <nav class="flex-1 overflow-y-auto"
                :class="isSidebarCollapsed ? 'px-2 py-4 space-y-2' : 'px-4 py-5 space-y-2.5'">
                <div v-if="!isSidebarCollapsed" class="px-3 pb-1 text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 dark:text-slate-500">Navigation</div>
                <Link v-for="item in navLinks" :key="item.href" :href="item.href" class="group flex items-center rounded-[22px] transition-all duration-300"
                    :title="isSidebarCollapsed ? item.label : ''"
                    :class="[
                        isSidebarCollapsed ? 'justify-center p-1.5 rounded-2xl' : 'px-3 py-3.5 gap-3',
                        item.active
                            ? (isSidebarCollapsed
                                ? 'bg-white/35 dark:bg-slate-800/70 text-indigo-700 dark:text-indigo-200 shadow-[0_10px_24px_rgba(79,70,229,0.18)]'
                                : 'bg-linear-to-r from-indigo-600 to-sky-500 text-white shadow-[0_18px_40px_rgba(79,70,229,0.28)]')
                            : 'text-slate-600 dark:text-slate-400 hover:bg-white/60 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-white'
                    ]">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border transition-colors"
                        :class="item.active
                            ? (isSidebarCollapsed
                                ? 'bg-linear-to-br from-indigo-600 to-sky-500 border-indigo-400/30 text-white shadow-[0_12px_28px_rgba(79,70,229,0.28)]'
                                : 'bg-white/18 border-white/20 text-white')
                            : 'bg-white/85 dark:bg-slate-900/60 border-slate-300/70 dark:border-slate-700/50 text-slate-700 dark:text-slate-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-300'">
                        <svg v-if="item.icon === 'dashboard'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 13h6V5H4v8zm10 6h6V5h-6v14zM4 19h6v-2H4v2zm10-6h6v-2h-6v2z"/>
                        </svg>
                        <svg v-else-if="item.icon === 'checkout'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <svg v-else-if="item.icon === 'books'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <svg v-else-if="item.icon === 'categories'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h10M4 17h16"/>
                        </svg>
                        <svg v-else-if="item.icon === 'members'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-1a4 4 0 00-5-3.87M9 20H4v-1a4 4 0 015-3.87m8-7a4 4 0 11-8 0 4 4 0 018 0zm-10 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg v-else-if="item.icon === 'issues'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/>
                        </svg>
                        <svg v-else-if="item.icon === 'reports'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18m-6-6l6-6 4 4 5-5"/>
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317a1 1 0 011.35-.936l1.84.736a1 1 0 00.742 0l1.84-.736a1 1 0 011.35.936l.207 1.965a1 1 0 00.527.79l1.628.905a1 1 0 01.413 1.4l-1.11 1.634a1 1 0 000 1.123l1.11 1.634a1 1 0 01-.413 1.4l-1.628.905a1 1 0 00-.527.79l-.207 1.965a1 1 0 01-1.35.936l-1.84-.736a1 1 0 00-.742 0l-1.84.736a1 1 0 01-1.35-.936l-.207-1.965a1 1 0 00-.527-.79l-1.628-.905a1 1 0 01-.413-1.4l1.11-1.634a1 1 0 000-1.123l-1.11-1.634a1 1 0 01.413-1.4l1.628-.905a1 1 0 00.527-.79l.207-1.965z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>

                    <div v-if="!isSidebarCollapsed" class="min-w-0 flex-1">
                        <div class="text-sm font-black truncate">{{ item.label }}</div>
                        <div class="text-[10px] font-bold uppercase tracking-[0.22em]"
                            :class="item.active ? 'text-white/70' : 'text-slate-400 dark:text-slate-500'">{{ item.hint }}</div>
                    </div>

                    <span v-if="!isSidebarCollapsed" class="text-xs font-black transition-transform"
                        :class="item.active ? 'text-white/80 translate-x-0' : 'text-slate-300 dark:text-slate-600 group-hover:translate-x-0.5'">›</span>
                </Link>
            </nav>

            <div class="border-t border-white/10 dark:border-slate-800/50" v-if="$page.props.auth?.user"
                :class="isSidebarCollapsed ? 'p-2.5' : 'p-4'">
                <div class="rounded-3xl bg-white/50 dark:bg-slate-900/45 border border-white/40 dark:border-slate-700/40 flex items-center"
                    :class="isSidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 p-3.5'">
                    <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-600 font-bold">
                        {{ $page.props.auth.user.name[0] }}
                    </div>
                    <div v-if="!isSidebarCollapsed" class="min-w-0">
                        <div class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500 truncate">{{ $page.props.auth.user.role }}</div>
                        <div class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ $page.props.auth.user.name }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile & Tablet Drawer -->
        <aside class="fixed top-0 left-0 h-full w-[86vw] max-w-xs glass-ghost rounded-r-3xl z-40 transform transition-transform duration-300 xl:hidden flex flex-col"
            :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="p-5 border-b border-white/10 dark:border-slate-800/50 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="h-12 w-12 rounded-[20px] bg-linear-to-br from-indigo-600 via-sky-500 to-cyan-400 text-white flex items-center justify-center shadow-[0_18px_40px_rgba(59,130,246,0.28)] shrink-0">
                            <span class="text-sm font-black tracking-[0.2em]">{{ libraryInitials }}</span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[10px] font-black uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">Library Hub</div>
                            <div class="text-lg font-black tracking-tight text-slate-900 dark:text-white truncate">{{ libraryName }}</div>
                        </div>
                    </div>
                    <button @click="isMobileMenuOpen = false" class="p-2 rounded-xl text-slate-500 hover:text-slate-700 dark:hover:text-slate-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="rounded-[22px] bg-white/50 dark:bg-slate-900/45 border border-white/40 dark:border-slate-700/40 px-3 py-2.5">
                    <div class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 dark:text-slate-500">Today</div>
                    <div class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ formattedDate }}</div>
                </div>
            </div>
            <nav class="p-4 space-y-2.5 overflow-y-auto">
                <Link v-for="item in navLinks" :key="`mobile-${item.href}`" :href="item.href" class="group flex items-center gap-3 px-3 py-3.5 rounded-[22px] transition-all duration-300"
                    :class="[item.active ? 'bg-linear-to-r from-indigo-600 to-sky-500 text-white shadow-[0_18px_40px_rgba(79,70,229,0.28)]' : 'text-slate-600 dark:text-slate-300 hover:bg-white/60 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-white']">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border"
                        :class="item.active
                            ? 'bg-white/18 border-white/20 text-white'
                            : 'bg-white/85 dark:bg-slate-900/60 border-slate-300/70 dark:border-slate-700/50 text-slate-700 dark:text-slate-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-300'">
                        <svg v-if="item.icon === 'dashboard'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 13h6V5H4v8zm10 6h6V5h-6v14zM4 19h6v-2H4v2zm10-6h6v-2h-6v2z"/>
                        </svg>
                        <svg v-else-if="item.icon === 'checkout'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <svg v-else-if="item.icon === 'books'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <svg v-else-if="item.icon === 'categories'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h10M4 17h16"/>
                        </svg>
                        <svg v-else-if="item.icon === 'members'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-1a4 4 0 00-5-3.87M9 20H4v-1a4 4 0 015-3.87m8-7a4 4 0 11-8 0 4 4 0 018 0zm-10 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <svg v-else-if="item.icon === 'issues'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/>
                        </svg>
                        <svg v-else-if="item.icon === 'reports'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18m-6-6l6-6 4 4 5-5"/>
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317a1 1 0 011.35-.936l1.84.736a1 1 0 00.742 0l1.84-.736a1 1 0 011.35.936l.207 1.965a1 1 0 00.527.79l1.628.905a1 1 0 01.413 1.4l-1.11 1.634a1 1 0 000 1.123l1.11 1.634a1 1 0 01-.413 1.4l-1.628.905a1 1 0 00-.527.79l-.207 1.965a1 1 0 01-1.35.936l-1.84-.736a1 1 0 00-.742 0l-1.84.736a1 1 0 01-1.35-.936l-.207-1.965a1 1 0 00-.527-.79l-1.628-.905a1 1 0 01-.413-1.4l1.11-1.634a1 1 0 000-1.123l-1.11-1.634a1 1 0 01.413-1.4l1.628-.905a1 1 0 00.527-.79l.207-1.965z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-black truncate">{{ item.label }}</div>
                        <div class="text-[10px] font-bold uppercase tracking-[0.22em]"
                            :class="item.active ? 'text-white/75' : 'text-slate-400 dark:text-slate-500'">{{ item.hint }}</div>
                    </div>
                    <span class="text-xs font-black" :class="item.active ? 'text-white/80' : 'text-slate-300 dark:text-slate-600'">›</span>
                </Link>
            </nav>
            <div class="px-4 pb-6 mt-auto" v-if="$page.props.auth?.user">
                <div class="p-4 border border-white/20 dark:border-slate-700/40 rounded-3xl bg-white/40 dark:bg-slate-900/40">
                    <div class="text-[10px] uppercase tracking-[0.24em] font-black text-slate-400 dark:text-slate-500">{{ $page.props.auth.user.role }}</div>
                    <div class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">{{ $page.props.auth.user.name }}</div>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <main class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
            <header class="min-h-16 shrink-0 flex items-center justify-between gap-3 px-4 sm:px-6 lg:px-8 glass-ghost m-3 sm:m-4 xl:ml-0 rounded-3xl z-20">
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="isMobileMenuOpen = true" class="xl:hidden p-2 rounded-xl bg-white/80 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 border border-slate-300/70 dark:border-slate-700/40">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h2 class="text-[10px] sm:text-xs font-semibold text-slate-500 dark:text-slate-500 uppercase tracking-widest truncate"><slot name="header" /></h2>
                </div>

                <div class="flex items-center gap-2">
                    <div class="hidden lg:flex flex-col items-end rounded-xl border border-slate-200/50 dark:border-slate-700/40 bg-slate-100/60 dark:bg-slate-800/60 px-3 py-1.5 leading-tight">
                        <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-300">{{ formattedDate }}</div>
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-100">{{ formattedTime }}</div>
                    </div>
                    <button @click="toggleFullScreen"
                        :title="isFullScreen ? 'Exit fullscreen' : 'Enter fullscreen'"
                        class="hidden sm:inline-flex p-1.5 sm:p-2 rounded-lg bg-white/80 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-slate-200 transition border border-slate-300/70 dark:border-slate-700/40 shrink-0">
                        <svg v-if="!isFullScreen" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        <svg v-else class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3H5a2 2 0 00-2 2v3m16-5h-3a2 2 0 00-2 2v3m5 8v3a2 2 0 01-2 2h-3m-8 0H5a2 2 0 01-2-2v-3m5 5v-3a2 2 0 00-2-2H3m18 0h-3a2 2 0 00-2 2v3"/>
                        </svg>
                    </button>

                    <button @click="isDark = !isDark"
                        class="p-1.5 sm:p-2 rounded-lg bg-white/80 dark:bg-slate-800/60 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-slate-200 transition border border-slate-300/70 dark:border-slate-700/40 shrink-0">
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
