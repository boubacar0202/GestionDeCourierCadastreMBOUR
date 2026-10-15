<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import image1 from '@/assets/image1.jpeg';

const page = usePage();

const stats = [
    { key: 'totalCourriersArrivees', label: 'Courriers arrivées' },
    { key: 'totalCourriersDeparts', label: 'Courriers départs' },
    { key: 'totalVisites', label: 'Visiteurs' },
    { key: 'totalInstances', label: 'Courriers instances' },
];

const targets = computed(() =>
    Object.fromEntries(stats.map((stat) => [stat.key, Number(page.props[stat.key] ?? 0)]))
);

const display = ref(Object.fromEntries(stats.map((stat) => [stat.key, 0])));

function animateCount(key, target) {
    const duration = 900;
    const start = performance.now();

    const step = (now) => {
        const progress = Math.min(1, (now - start) / duration);
        display.value[key] = Math.round(target * (1 - Math.pow(1 - progress, 3)));
        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };

    requestAnimationFrame(step);
}

onMounted(() => {
    stats.forEach((stat) => animateCount(stat.key, targets.value[stat.key]));
});
</script>

<template>
    <Head title="Accueil">
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
            <div class="absolute inset-0 bg-[#2c1810]/40"></div>
            <div class="relative flex min-h-[calc(100vh-8rem)] flex-col items-center justify-center gap-8 px-4 py-8">
                <div class="flex flex-wrap items-center justify-center gap-8">
                    <div
                        v-for="stat in stats"
                        :key="stat.key"
                        class="flex h-52 w-52 flex-col items-center justify-center rounded-full border-8 border-primary-only bg-white/85 text-center shadow-lg"
                    >
                        <span class="text-6xl font-bold text-primary-txt">{{ display[stat.key] }}</span>
                        <h1 class="px-6 text-sm font-semibold uppercase text-primary-txt">{{ stat.label }}</h1>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
