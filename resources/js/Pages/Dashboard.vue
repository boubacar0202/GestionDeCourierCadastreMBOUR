<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import 'flowbite';
import * as bootstrap from 'bootstrap';
 
import image1 from '@/assets/image1.jpeg';
import image2 from '@/assets/image2.jpg';
import image3 from '@/assets/image3.jpg'; 
import image4 from '@/assets/image4.jpg'; 

// Tableau des images disponibles
const images = [image1, image2, image3, image4];

// Sélection aléatoire à chaque chargement
const randomImage = images[Math.floor(Math.random() * images.length)];

</script>

<template>
    <Head title="Accueil">
        <link rel="icon" sizes="512x512" href="/logo-01.png">
    </Head>

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-primary-txt"
            >
                Accueil
            </h2>
        </template>
 
        <div
            :style="`background-image: url(${randomImage}); background-size: cover; background-position: center;`"
            class="w-full"
        >
 
            <div class="relative isolate px-6 pt-10 lg:px-8">
                <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
                    <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%-11rem)] aspect-1155/678 w-144.5 -translate-x-1/2 rotate-30 bg-linear-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-288.75"></div>
                </div>
                <div class="mx-auto max-w-2xl py-32 sm:py-48 lg:py-60">
                    <div class="hidden sm:mb-8 sm:flex sm:justify-center">
                        <div class="relative rounded-full px-3 py-1 text-sm/6 text-white ring-1 ring-white hover:ring-white">
                            Enregistrement des courriers. 
                            <a href="/arrivee/create" class="font-semibold text-white">
                                <span aria-hidden="true" class="absolute inset-0"> 
                                </span><b>Créer un courrier</b> <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                    <div class="text-center">
                        <h1 class="text-5xl font-semibold tracking-tight text-balance text-white sm:text-7xl">CADASTRE MBOUR Gestion des courriers</h1>
                        <p class="mt-8 text-lg font-medium text-pretty text-white sm:text-xl/8">Direction Générale des Impôts et Domaines, Services des Centres Fiscaux de Mbour, Bureau du CADASTRE.</p>
                    </div>
                </div>
                <div aria-hidden="true" class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]">
                    <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%+3rem)] aspect-1155/678 w-144.5 -translate-x-1/2 bg-linear-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-288.75"></div>
                </div>
            </div> 
 
        </div> 
 
    </AuthenticatedLayout>
</template>
