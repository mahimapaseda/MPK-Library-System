<script setup>
defineProps({
    value: [String, Number],
    label: String,
    sublabel: { type: String, default: null },
    color: { type: String, default: 'indigo' }, // indigo, emerald, amber, rose, slate
    size: { type: String, default: 'normal' }, // normal, large
    icon: { type: String, default: null },
});

const colorMap = {
    indigo: { bg: 'bg-indigo-500/10 dark:bg-indigo-400/10', text: 'text-indigo-600 dark:text-indigo-400', accent: 'text-indigo-500/80' },
    emerald: { bg: 'bg-emerald-500/10 dark:bg-emerald-400/10', text: 'text-emerald-600 dark:text-emerald-400', accent: 'text-emerald-500/80' },
    amber: { bg: 'bg-amber-500/10 dark:bg-amber-400/10', text: 'text-amber-600 dark:text-amber-400', accent: 'text-amber-500/80' },
    rose: { bg: 'bg-rose-500/10 dark:bg-rose-500/10', text: 'text-rose-600 dark:text-rose-400', accent: 'text-rose-500/80' },
    slate: { bg: 'bg-slate-500/10 dark:bg-slate-400/10', text: 'text-slate-600 dark:text-slate-400', accent: 'text-slate-500/80' },
};
</script>

<template>
    <!-- Large variant (2-col span) -->
    <div v-if="size === 'large'" class="sm:col-span-2 xl:col-span-1 bento-card p-5 sm:p-8 flex flex-col justify-between group">
        <div class="flex justify-between items-start">
            <div :class="[colorMap[color].bg, colorMap[color].text, 'p-4 rounded-2xl group-hover:scale-110 transition-transform duration-500']">
                <slot name="icon" />
            </div>
            <div class="text-right">
                <span :class="[colorMap[color].accent, 'text-xs font-black uppercase tracking-widest']">{{ sublabel }}</span>
                <h3 class="text-3xl sm:text-4xl font-black text-slate-800 dark:text-white mt-1">{{ value }}</h3>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ label }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">Updated just now</p>
        </div>
        <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/10 transition-colors"></div>
    </div>

    <!-- Normal variant (centered) -->
    <div v-else class="bento-card p-5 sm:p-6 flex flex-col items-center justify-center text-center group">
        <div :class="[colorMap[color].bg, colorMap[color].text, 'p-3 rounded-xl mb-4 group-hover:glow-indigo transition-all']">
            <slot name="icon" />
        </div>
        <div class="text-3xl font-black text-slate-800 dark:text-white">{{ value }}</div>
        <div class="text-xs font-bold text-slate-500 dark:text-slate-500 uppercase mt-1">{{ label }}</div>
    </div>
</template>
