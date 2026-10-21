<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';
import image1 from '@/assets/image1.jpeg';

const page = usePage();

const groupes = [
    [
        { key: 'totalTerrains', label: 'Dossiers' },
        { key: 'totalDelimitation', label: 'Délimitations' },
        { key: 'totalNicad', label: 'Ont des NICAD' },
        { key: 'totalSansNicad', label: 'Sans NICAD' },
    ],
    [
        { key: 'totalCourriersArrivees', label: 'Courriers arrivées' },
        { key: 'totalCourriersDeparts', label: 'Courriers départs' },
        { key: 'totalInstances', label: 'Courriers instances' },
    ],
];

const stats = groupes.flat();

const targets = computed(() =>
    Object.fromEntries(stats.map((stat) => [stat.key, Number(page.props[stat.key] ?? 0)]))
);

const nodes = {};
const frames = [];

function register(key, el) {
    if (el) nodes[key] = el;
}

function animateCount(key, target) {
    const duration = 900;
    const start = performance.now();

    const step = (now) => {
        const el = nodes[key];
        if (!el) return;
        const progress = Math.min(1, (now - start) / duration);
        el.textContent = String(Math.round(target * (1 - Math.pow(1 - progress, 3))));
        if (progress < 1) {
            frames.push(requestAnimationFrame(step));
        }
    };

    frames.push(requestAnimationFrame(step));
}

onMounted(() => {
    stats.forEach((stat) => animateCount(stat.key, targets.value[stat.key]));
});

onUnmounted(() => {
    frames.forEach((id) => cancelAnimationFrame(id));
});
</script>

<template>
    <Head v-once title="Accueil">
        <link rel="icon" sizes="512x512" href="/logo-01.png">
    </Head>

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-primary-txt">
                Accueil
            </h2>
        </template>

        <div
            class="relative min-h-[calc(100vh-8rem)] bg-cover bg-center bg-no-repeat"
            :style="{ backgroundImage: `url(${image1})` }"
        >
            <div class="flex min-h-[calc(100vh-8rem)] flex-col items-center justify-center gap-8 px-4 py-8">
                <div
                    v-for="(groupe, index) in groupes"
                    :key="index"
                    class="flex flex-wrap items-center justify-center gap-8"
                >
                    <div
                        v-for="stat in groupe"
                        :key="stat.key"
                        class="flex h-52 w-52 flex-col items-center justify-center rounded-full border-8 border-primary-only bg-white/80 text-center shadow-lg"
                    >
                        <span class="text-7xl font-bold leading-none text-primary-txt" :ref="(el) => register(stat.key, el)">0</span>
                        <h1 class="px-6 text-sm font-semibold text-primary-txt">{{ stat.label }}</h1>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
