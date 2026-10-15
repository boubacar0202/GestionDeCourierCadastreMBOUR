<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed  } from 'vue';
import { Head } from '@inertiajs/vue3';
import { defineProps, onMounted } from 'vue';
import { router, usePage  } from '@inertiajs/vue3'
import { Link as InertiaLink } from '@inertiajs/vue3'
import BaseInput from '@/Components/BaseInput.vue'


// import { toast } from 'vue3-toastify' 
const props = defineProps({
    arrivee: Object,    
    arrivees: Array,
    departs: Array,
    depart: Object,
});
   
const refCourrierArrivee = ref('');
 
// Normalisation pour éviter les problèmes d'espaces et de casse
function normalize(str) {
    return str?.toString().trim().toLowerCase();
}
 
// filtrer les instances par categorie  
const filteredInstance = computed(() => {
    const now = new Date();

    const addIsOverdueFlag = (arrivee) => {
        const dtCourrier = arrivee.dt_datearrivee ? new Date(arrivee.dt_datearrivee) : null;

        return {
            ...arrivee,
            isOverdue:
                dtCourrier &&  
                (now - dtCourrier) >= (10 * 24 * 60 * 60 * 1000) // plus de 10 jours
        };
    };

    // Fonction de normalisation pour la recherche insensible à la casse
    const normalize = (text) => {
        if (!text) return '';
        return text.toString()
            .toLowerCase()
            .trim()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
    };

    return props.arrivees
        .filter(arrivee => {
            // Vérifie si le courrier existe déjà dans les départs
            const existeDansDepart = props.departs.some(
                depart => depart.txt_referencecourierarriveecd === arrivee.txt_reference
            );
            
            // Filtre principal par catégorie
            const matchCategorie = arrivee.txt_categorie === 'Demande SERVICES' && !existeDansDepart;
            
            // Filtre optionnel par référence
            const searchTerm = refCourrierArrivee.value;
            const matchArrivee = searchTerm
                ? normalize(arrivee.txt_reference)?.includes(normalize(searchTerm))
                : true;

            return matchCategorie && matchArrivee;
        })
        .map(addIsOverdueFlag);
});
   
// Compter le nombre d'instance
const totalInstance = computed(() => {
    return filteredInstance.value.filter(instance => instance.isOverdue).length;
});

// Compter le nombre total de courrier
const totalCourrierInstance = computed(() => {
    return filteredInstance.value.filter(totalCourrierInstance => !!totalCourrierInstance.txt_numdordre).length;
});

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};
 
</script>

<template>
    <Head title="Instances">
        <link rel="icon" sizes="512x512" href="/logo-01.png">
    </Head>

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-primary-txt"
            >
                Courriers en Instance
            </h2>
            
            <!-- Vérification pour afficher un message si aucune donnée -->
            <template class="text-xl font-semibold leading-tight text-primary-dark" v-if="(!arrivees || arrivees.length === 0)">
                <p>Aucun Courrier arrivée trouvé.</p>
            </template>
           
        </template v-else>

        <div class="py-12">
            <div class="flex justify-center">
                <div class="w-full max-w-8xl">
                    <div class="bg-white shadow-md rounded-xl">
                        <div class="p-4 border-b bg-primary rounded-t-xl">
                            <h1 class="text-lg text-white font-semibold">Liste des courriers en instance</h1>
                        </div>
                        <div class="p-6">
                            <div class="p-4 bg-primary-form border border-primary-only rounded-xl">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <h1 class="text-lg text-primary-txt font-bold">
                                        Instances
                                        <span v-if="totalCourrierInstance>0" class="text-primary-txt">
                                            ({{ totalCourrierInstance }})
                                        </span>
                                        <span v-else class="text-red-600">
                                            Aucun enregistrement
                                        </span> 
                                    </h1>  

                                    <h2 class="text-sm font-bold text-primary">
                                        Instances de plus de 10 jours :
                                        <span class="text-primary-dark">{{ totalInstance }}</span>
                                    </h2>   

                                    <BaseInput
                                        class="w-64"
                                        label="Référence"
                                        name="filtre-instance"
                                        type="search"
                                        placeholder="Reférence Courrrier Arrivée"
                                        v-model="refCourrierArrivee"
                                    />
                                </div>
                            </div>

                            <div class="p-4">
                                <div class="max-h-[600px] overflow-x-auto shadow-md rounded-xl">
                                            <table class="table table-auto w-full text-sm text-left text-primary-txt">

                                                <thead class="sticky top-0 z-10 text-xs text-primary-txt font_bold uppercase text-center 
                                                        uppercase bg-primary-footer text-primary-txt whitespace-nowrap">
                                                    <tr class="h-10">  
                                                        <th scope="col" class="px-6 px-6 border-l border-primary-only">N°</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">N° d'ordre d'arrivée</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Catégorie</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Date Arrivée</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">N° Courrier</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Date Courrier</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Référence</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Caractère</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Désignation</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Date</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Heure</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Lieu</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Nombre Pièces</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Objet</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Expéditeur</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Agent Traiteur</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Observation</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Fichier PDF</th> 
                                                    </tr>
                                                </thead>
                                                        
                                                <tbody sortedinstance>
                                                    <tr v-for="(arrivee, index) in filteredInstance" :key="arrivee.id"  
                                                        class="bg-white text-primary-txt leading-8 border border-primary-only 
                                                            text-center text-sm hover:bg-primary-form dark:hover:bg-primary-form">
                                                        <td scope="col" class="sticky left-0 z-0 bg-white px-6 py-3 text-center text-primary-txt border border-primary-only font-bold whitespace-nowrap">{{ index + 1 }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only font-bold" :class="arrivee.isOverdue ? 'text-red-600 font-bold' : ''">{{ arrivee.txt_numdordre }}</td> 
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ arrivee.txt_categorie || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ formatDate(arrivee.dt_datearrivee) || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ arrivee.txt_numcourier || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ formatDate(arrivee.dt_datecourier) }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ arrivee.txt_reference || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ arrivee.txt_caractere || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ arrivee.txt_designation || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ formatDate(arrivee.dt_date) || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ arrivee.txt_heure || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ arrivee.txt_lieu || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ arrivee.txt_nombrepiece || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ arrivee.txt_objet || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ arrivee.txt_expediteur || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ arrivee.txt_agenttraiteur || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only">{{ arrivee.txt_observation || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 text-center whitespace-nowrap border border-primary-only"> 
                                                            <div v-if="arrivee.fichierPDF">
                                                                <a :href="`/storage/${arrivee.fichierPDF}`" target="_blank" class="text-blue-600 font-bold underline">
                                                                    Voir PDF
                                                                </a>
                                                            </div>
                                                            <div v-else class="italic text-primary-txt/60">Aucun fichier PDF</div>
                                                        </td>
            
                                                    </tr>
                                                </tbody>
                                            </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
 
