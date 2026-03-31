<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap gap-2">
        <component
            :is="link.url ? Link : 'span'"
            v-for="(link, index) in links"
            :key="`${index}-${link.label}`"
            :href="link.url || undefined"
            preserve-scroll
            preserve-state
            class="min-w-10 px-3 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all text-center"
            :class="[
                link.active
                    ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/25'
                    : link.url
                        ? 'glass-card text-slate-500 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-300'
                        : 'bg-slate-100/70 dark:bg-slate-900/70 text-slate-300 dark:text-slate-600 cursor-not-allowed',
            ]"
        >
            <span v-html="link.label"></span>
        </component>
    </div>
</template>
