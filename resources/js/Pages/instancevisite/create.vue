<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed  } from 'vue';
import { Head } from '@inertiajs/vue3';
import { defineProps, onMounted } from 'vue';
import { router, usePage  } from '@inertiajs/vue3'
import { Link as InertiaLink } from '@inertiajs/vue3' 
 
const props = defineProps({
    visite: Object,    
    visites: Array, 
});
  
const numeroCD = ref('');  
const annee = ref('');
const refPrenomNom = ref('');

// Normalisation pour éviter les problèmes d'espaces et de casse
function normalize(str) {
    return str?.toString().trim().toLowerCase();
}
 
//  Filtrer par date et par Numéro + Tri croissant par txt_numdordrecd
const filtereVisites = computed(() => {
    const filtered = props.visites.filter(visite => {
        const matchNumero = numeroCD.value
            ? normalize(visite.txt_numdordreVisite)?.includes(normalize(numeroCD.value))
            : true;

        const matchAnnee = annee.value
            ? new Date(visite.dt_visite).getFullYear().toString() === annee.value
            : true;

        const matchPrenomNom = refPrenomNom.value 
            ? normalize(visite.txt_prenomVisite)?.includes(normalize(refPrenomNom.value)) 
            : true;
 

        return matchNumero && matchAnnee && matchPrenomNom;
    });

    // Tri par ordre numérique de la partie avant "/"
    return filtered.sort((a, b) => {
        const numA = parseInt(a.txt_numdordreVisite?.split("/")[0] || 0, 10);
        const numB = parseInt(b.txt_numdordreVisite?.split("/")[0] || 0, 10);
        return numA - numB; // ordre croissant
    });
});
 
// Compter le nombre total de courrier
const totalvisite = computed(() => {
    return props.visites.filter(totalvisite => !!totalvisite.txt_numdordreVisite).length;
});
 
// filtrer les instances par categorie  
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};
  
// fonction d'envoi du formulaire
function supprimerVisite(visite) {
    const code = prompt("Entrez le code d'accès pour confirmer la suppression :");

    if (!code) {
        alert("Aucun code saisi. Suppression annulée.");
        return;
    }

    router.delete(route('instancevisite.destroy', visite.id), {
        data: { code_acces: code }, // ✅ on envoie le code au backend
        preserveScroll: true,
        onSuccess: () => {
            console.log('Suppression réussie');
        },
        onError: (errors) => {
            alert(errors.code_acces || "Erreur : code invalide ou suppression refusée.");
            console.error(errors);
        }
    });
}
 
</script>

<template>
    <Head title="Instances Départs">
        <link rel="icon" sizes="512x512" href="/logo-01.png">
    </Head>

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl text-primary-txt font-semibold leading-tight"
            >
                Liste des Courriers Departs
            </h2>
            
            <!-- Vérification pour afficher un message si aucune donnée -->
            <template v-if="(!departs || departs.length === 0)">
                <p>Aucun Instance Départ n'est trouvé.</p>
            </template>
           
        </template v-else>

        <div class="py-12">
            <div class="flex justify-center">
                <div class="w-full max-w-8xl">
                    <div class="bg-white shadow-md rounded-lg "> <br> 
                        <div class="mx-auto max-w-8xl sm:px-8 lg:px-12 mt-4 mb-4">
                            <div class="card-header">
                                <div class="p-4 border-b bg-primary sm:rounded-lg">
                                    <h1 class="text-3xl text-white font-bold font-bold text-center">Base de donnée des Courriers Départs</h1>
                                </div>
                            </div> 
                             
                            <div class="relative overflow-x-auto p-4 border-b bg-primary-form sm:rounded-lg mt-8">
                                <div class="flex justify-between items-center"> 
                                    <h1 class="text-1xl text-primary-txt font-bold">
                                        Liste des Courriers Départs : 
                                        <span v-if="totalCourrier>0" class="text-gray-600">
                                            ({{ totalCourrier }})
                                        </span>
                                        <span v-else class="text-red-600">
                                            Aucun enregistrement
                                        </span> 
                                    </h1>  

                                    <form @submit.prevent class="flex items-center ">
                                        <div  class="flex items-start space-x-4">
                                            <div>
                                                <input 
                                                    v-model="annee"
                                                    type="search"
                                                    maxlength="4"
                                                    id="default-search"
                                                    aria-label="Rechercher"
                                                    class="h-9 block w-20 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 
                                                        border border-primary-menu  placeholder:text-gray-400 
                                                        focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    placeholder="Date Visite"
                                                /> 
                                            </div> 
                                            <div>
                                                <input 
                                                    v-model="refPrenomNom"
                                                    type="search"
                                                    id="default-search"
                                                    aria-label="Rechercher"
                                                    class="h-9 block w-25 rounded-md bg-white px-3 py-1.5 text-base text-gray-900 
                                                        border border-primary-menu  placeholder:text-gray-400 
                                                        focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    placeholder="Prénom Nom"
                                                />
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="max-h-[600px] overflow-y-auto shadow-md sm:rounded-lg mt-8">
                                <div class="container"> 
                                    <div class="card"> 
                                        <div class="card-body">
                                            <table class="table table-auto w-full text-base text-left rtl:text-right text-gray-500 dark:text-gray-400">

                                                <thead class="sticky top-0 z-10 text-primary-txt text-center border-l border-primary-only uppercase 
                                                        bg-primary-form bg-gray-50 dark:bg-gray-700 dark:text-gray-400 whitespace-nowrap">
                                                    <tr class="h-20">
                                                        <th scope="col" class="sticky left-0 z-30 px-6 py-3 border-l border-primary-only">N°</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Numero</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Prenom & Nom</th> 
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Telephone</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Objet</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Traitement</th> 
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Date visite</th>  
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Action</th>   
                                                    </tr>
                                                </thead>
                                                        
                                                <tbody sortedVisites>
                                                    <tr v-for="(visite, index) in filtereVisites" :key="visite.id"  
                                                        class="h-20 bg-white text-primary-txt text-center font-bold whitespace-nowrap hover:bg-primary-form dark:hover:bg-primary-form">
                                                        <td scope="col" class="sticky left-0 z-0 border border-primary-only bg-white px-6 py-3 ">{{ index + 1 }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ visite.txt_numdordreVisite || '-'}}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ visite.txt_prenomVisite  || '-'}}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ visite.txt_telVisite || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ visite.txt_objetVisite || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ visite.txt_traitementVisite || '-' }}</td> 
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ formatDate(visite.dt_visite) || '-' }}</td> 
                                                        <td class="flex items px-6 py-3">
                                                            <div class="mt-2">
                                                                <InertiaLink :href="`/visite/editvisite/${visite.id}`">
                                                                    <MazBtn 
                                                                        color="white" pastel size="sm"
                                                                        class="h-8 w-28 text-white bg-gradient-to-r from-green-400 via-green-500 
                                                                        to-green-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none 
                                                                        focus:ring-green-300 dark:focus:ring-green-800 shadow-lg shadow-green-500/50 
                                                                        dark:shadow-lg dark:shadow-green-800/80 font-medium rounded-lg text-sm px-5 
                                                                        py-2.5 text-center me-2 mb-2"
                                                                    >
                                                                        Modifier
                                                                    </MazBtn>
                                                                </InertiaLink> 
                                                            </div>
                                                            <div class="container">
                                                                <p></p>
                                                            </div>
                                                            <div class="mt-2">
                                                                <MazBtn 
                                                                    color="danger" size="sm"   
                                                                    @click="() => supprimerVisite(visite)"
                                                                    class="h-8 w-28 text-white bg-gradient-to-r from-danger-500 via-danger-600 
                                                                    to-danger-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none 
                                                                    focus:ring-danger-300 dark:focus:ring-danger-800 shadow-lg shadow-danger-500/50 
                                                                    dark:shadow-lg dark:shadow-danger-800/80 font-medium rounded-lg text-sm px-5 
                                                                    py-2.5 text-center me-2 mb-2"
                                                                >
                                                                    Supprimer
                                                                </MazBtn>
                                                            </div>
                                                        </td> 
                                                     
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div><br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
 
 
<style scoped>

table tr td {
  padding-top: 0.25rem; /* équivaut à py-1 */
  padding-bottom: 0.25rem;
  line-height: 1rem; /* équivaut à leading-3 */
  font-size: 0.80rem; /* équivaut à text-xs */
}

</style>
 