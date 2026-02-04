<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed  } from 'vue';
import { Head } from '@inertiajs/vue3';
import { defineProps, onMounted } from 'vue';
import { router, usePage  } from '@inertiajs/vue3'
import { Link as InertiaLink } from '@inertiajs/vue3' 
// import { toast } from 'vue3-toastify'
 
const props = defineProps({
    arrivee: Object,    
    arrivees: Array,  
});
   
const refPrenomNom = ref('');  
const annee = ref(''); 
const rowRefs = ref({});
const refCourrierArrivee = ref('');
const refDesignation = ref('');
 

// Extraire les désignations uniques depuis les données
const uniqueDesignations = computed(() => {
  const set = new Set(props.arrivees.map(a => a.txt_designation).filter(Boolean));
  return Array.from(set);
});

// Normalisation 
function normalize(str) {
    return str?.toString().trim().toLowerCase();
}
  
// Ajout du tri
const filtereArrivees = computed(() => {
    const now = new Date();

    const addLateFlag = (arrivee) => {
        const dtDate = arrivee.dt_date ? new Date(arrivee.dt_date) : null;

        return {
            ...arrivee,
            isLateConvocation:
                arrivee.txt_categorie === 'Convocation - Invitation' &&
                dtDate &&
                (dtDate - now) > 0 && // date future
                (dtDate - now) <= (72 * 60 * 60 * 1000) // <= 72h
        };
    };

    const filtered = props.arrivees.filter(arrivee => {
  
        const matchPrenomNom = refPrenomNom.value
        ? (
            (arrivee.txt_prenom && normalize(arrivee.txt_prenom).includes(normalize(refPrenomNom.value))) ||
            (arrivee.txt_nom && normalize(arrivee.txt_nom).includes(normalize(refPrenomNom.value))) ||
            (arrivee.txt_agenttraiteur && normalize(arrivee.txt_agenttraiteur).includes(normalize(refPrenomNom.value)))
          )
        : true;

        const matchAnnee = annee.value
            ? new Date(arrivee.dt_datearrivee).getFullYear().toString() === annee.value
            : true;

        const matchArrivee = refCourrierArrivee.value
            ? normalize(arrivee.txt_numcourier)?.includes(normalize(refCourrierArrivee.value))
            : true;
         
        const matchDesignation = refDesignation.value
            ? normalize(arrivee.txt_designation)?.includes(normalize(refDesignation.value))
            : true;
 
        return matchPrenomNom && matchAnnee && matchArrivee && matchDesignation;
    });
 
    const sorted = filtered.sort((a, b) => {
        const numA = parseInt(a.txt_numdordre?.split("/")[0] || 0, 10);
        const numB = parseInt(b.txt_numdordre?.split("/")[0] || 0, 10);
        return numA - numB; // tri croissant
    });

    return sorted.map(addLateFlag);
});


// Compter le nombre d'invitation dans 72H 
const totalConvocationsImminentes = computed(() => {
    const now = new Date();

    return props.arrivees.filter(arrivee => {
        const dtDate = arrivee.dt_date ? new Date(arrivee.dt_date) : null;

        return (
            arrivee.txt_categorie === 'Convocation - Invitation' &&
            dtDate &&
            (dtDate - now) > 0 &&
            (dtDate - now) <= (72 * 60 * 60 * 1000)
        );
    }).length;
});
 
// Compter le nombre total de courrier
const totalCourrier = computed(() => {
    return props.arrivees.filter(totalarrivee => !!totalarrivee.txt_numdordre).length;
});

// format de la date
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};

// fonction d'envoi du formulaire
function supprimerCourrierArrivee(arrivee) {
    const code = prompt("Entrez le code d'accès pour confirmer la suppression :");

    if (!code) {
        alert("Aucun code saisi. Suppression annulée.");
        return;
    }

    router.delete(route('instancearrivee.destroy', arrivee.id), {
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


// 👉 Liste filtrée (même logique mais sans length)
const convocationsDans48h = computed(() => {
    const now = new Date();

    return props.arrivees.filter(arrivee => {
        const dtDate = arrivee.dt_date ? new Date(arrivee.dt_date) : null;

        return (
            arrivee.txt_categorie === 'Convocation - Invitation' &&
            dtDate &&
            dtDate > now && 
            (dtDate - now) <= 48 * 60 * 60 * 1000
            
        );
    });
});

// 👉 Convocation(s) la/les plus proche(s)
const convocationProche = computed(() => {
    if (!convocationsDans48h.value.length) return null;

    // Prendre la date la plus proche
    const minDate = Math.min(
        ...convocationsDans48h.value.map(c => new Date(c.dt_date).getTime())
    );

    // Retourner toutes celles qui ont cette même date
    return convocationsDans48h.value.filter(
        c => new Date(c.dt_date).getTime() === minDate
    );
});
 
// Fonction pour faire défiler jusqu'à la convocation
const goToConvocation = (txt_numdordre) => {
    const row = rowRefs.value[txt_numdordre];
    if (row) {
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        // Optionnel : mettre en surbrillance la ligne pendant 2 sec
        row.classList.add('bg-yellow-300');
        setTimeout(() => row.classList.remove('bg-yellow-300'), 5000);
    }
};
const formatDateDMY = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',  // abrégé : janv., févr., sept., etc.
        year: 'numeric'
    });
};

</script>

<template>
    <Head title="Instances Arrivées">
        <link rel="icon" sizes="512x512" href="/logo-01.png">
    </Head>

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-primary-txt"
            >
                Liste des Courriers Arrivés
            </h2>
            
            <!-- Vérification pour afficher un message si aucune donnée -->
            <template class="text-xl font-semibold leading-tight text-primary-txt" v-if="(!arrivees || arrivees.length === 0)">
                <p>Aucun Courrier arrivée trouvé.</p>
            </template>
           
        </template v-else>

        <div class="py-12">
            <div class="flex justify-center">
                <div class="w-full max-w-8xl">
                    <div class="bg-white shadow-md:ml-64 rounded-xl "><br> 
                        <div class="mx-auto max-w-8xl sm:px-8 lg:px-12 mt-4 mb-4">
                            <div class="card-header">
                                <div class="p-4 border-b bg-primary sm:rounded-xl">
                                    <h1 class="text-3xl text-white font-bold text-center">Base de donnée des Courriers arrivées</h1>
                                </div>
                            </div>

                            <div class="relative overflow-x-auto p-4 border-b bg-primary-form sm:rounded-xl mt-8">
                                <div class="flex justify-between items-center">  
                                    <h1 class="text-1xl text-primary-txt font-bold">
                                        Liste des Courriers Arrivés : 
                                        <span v-if="totalCourrier>0" class="text-gray-600">
                                            ({{ totalCourrier }})
                                        </span>
                                        <span v-else class="text-red-600">
                                            Aucun enregistrement
                                        </span> 
                                    </h1> 
                                    <h2 class="text-1xl text-green-600">
                                        Convocation dans 72H : 
                                        <span class="text-green-600  font-bold">{{ totalConvocationsImminentes }}</span> 
                                    </h2>  
                                    
                                    <ul v-if="convocationProche && convocationProche.length" class="space-y-2">
                                        <li
                                            v-for="convocation in convocationProche"
                                            :key="convocation.txt_numdordre"
                                            class="p-2 h-10 bg-white border rounded-xl shadow-sm hover:bg-green-50 cursor-pointer flex justify-between items-center relative"
                                             @click="goToConvocation(convocation.txt_numdordre)"
                                        >
                                            <div class="flex items-center space-x-2">
                                                <p class="font-medium text-sm text-primary-txt"> 
                                                    Dans 48H : 📅 {{ formatDateDMY(convocation.dt_date) }} à {{ convocation.txt_heure }}
                                                </p>
                                            </div>
                                        </li>
                                    </ul>

                                    <p v-else class="text-sm text-primary-txt p-2 bg-white border rounded-xl shadow-sm italic">
                                        Pas de convocation dans les 48h
                                    </p>
 
                                    <form @submit.prevent class="flex items-center space-x-2">
                                        <div  class="flex items-start space-x-2">
                                            <div>
                                                <input 
                                                    v-model="annee"
                                                    type="search"
                                                    maxlength="4"
                                                    id="default-search"
                                                    aria-label="Rechercher"
                                                    class="h-9 block w-20 rounded-xl bg-white px-3 py-1.5 text-base text-primary-txt 
                                                        border border-primary-menu  placeholder:text-gray-400 
                                                        focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    placeholder="année"
                                                /> 
                                            </div>
                                         
                                            <div>
                                                <input 
                                                    v-model="refCourrierArrivee"
                                                    type="search"
                                                    id="default-search"
                                                    aria-label="Rechercher"
                                                    class="h-9 block w-28 rounded-xl bg-white px-3 py-1.5 text-base text-gray-900 
                                                        border border-primary-menu  placeholder:text-gray-400 
                                                        focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    placeholder="N° Courriers"
                                                />
                                            </div>
                                            
                                            <div>
                                                <input 
                                                    v-model="refPrenomNom"
                                                    type="search"
                                                    id="default-search"
                                                    aria-label="Rechercher"
                                                    class="h-9 block w-34 rounded-xl bg-white px-3 py-1.5 text-base text-primary-txt 
                                                        border border-primary-menu  placeholder:text-gray-400 
                                                        focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    placeholder="Prénom & Nom"
                                                />
                                            </div> 
 
                                            <div>
                                                <select
                                                    v-model="refDesignation"
                                                    id="select-designation"
                                                    class="h-9 block w-34 rounded-xl bg-white px-3 py-1.5 text-base text-gray-900 
                                                            border border-primary-menu  placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                >
                                                    <option value="">Tous</option>
                                                    <option 
                                                        v-for="arrivee in uniqueDesignations" 
                                                        :key="arrivee" 
                                                        :value="arrivee"
                                                        >
                                                        {{ arrivee }}
                                                    </option>
                                                </select>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                            </div>
 
                            <div class="max-h-[600px] overflow-y-auto shadow-md sm:rounded-xl mt-8">
                                <div class="container"> 
                                    <div class="card"> 
                                        <div class="card-body">
                                            <table class="table table-auto w-full text-base text-left rtl:text-right text-gray-500 dark:text-gray-400">

                                                <thead  class="sticky top-0 z-10 text-primary-txt text-center border-l border-primary-only uppercase 
                                                    bg-primary-form bg-gray-50 dark:bg-gray-700 dark:text-gray-400 whitespace-nowrap">
                                                    <tr class="h-20">
                                                        <th scope="col" class="px-6 px-6 border-l border-primary-only">N°</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Bordereau</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">N° d'ordre d'arrivée</th> 
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Date Récéption</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Categorie</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Désignation</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">N° Courier</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Date Courrier</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Référence</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Caractère</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Prénom</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Nom</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">N° Lot</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Superficie</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Situation</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">NICAD</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Date</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Heure</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Lieu</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Nombre Pièce</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Objet</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Expéditeur</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Agent Traiteur</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Observation</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">Fichier PDF</th>
                                                        <th scope="col" class="px-6 py-3 border-l border-primary-only">ACTIONS</th>
                                                    </tr>
                                                </thead>
                                                        
                                                <tbody sortedArrivees >
                                                    <tr v-for="(arrivee, index) in filtereArrivees" :key="arrivee.id" :ref="el => (rowRefs[arrivee.txt_numdordre] = el)" 
                                                        class="h-20 bg-white text-primary-txt text-center font-bold whitespace-nowrap hover:bg-primary-form dark:hover:bg-primary-form">
                                                        <td scope="col" class="sticky left-0 z-0 bg-white px-6 py-3 border border-primary-form">{{ index + 1 }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_bordereau || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only" :class="arrivee.isLateConvocation ? 'text-green-600 font-bold' : ''">{{ arrivee.txt_numdordre }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ formatDate(arrivee.dt_datearrivee) || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_categorie || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_designation || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_numcourier || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ formatDate(arrivee.dt_datecourier) }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_reference || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_caractere || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_prenom || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_nom || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_numLot || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_surface || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_situation || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_nicad || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ formatDate(arrivee.dt_date) || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_heure || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_lieu || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_nombrepiece || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_objet || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_expediteur || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_agenttraiteur || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only">{{ arrivee.txt_observation || '-' }}</td>
                                                        <td scope="col" class="px-6 py-3 border border-primary-only"> 
                                                            <div v-if="arrivee.fichierPDF">
                                                                <a :href="`/storage/${arrivee.fichierPDF}`" target="_blank" class="text-blue-600 underline">
                                                                    📄 Voir PDF
                                                                </a>
                                                            </div>
                                                            <div v-else class="text-gray-400 font-semibold italic">Aucun fichier PDF</div>
                                                        </td>
                                                
                                                        <td class="flex items px-6 py-3">
                                                            <div class="mt-2">
                                                                <InertiaLink :href="`/arrivee/editarrivee/${arrivee.id}`">
                                                                    <MazBtn 
                                                                        color="white" pastel size="medium"  
                                                                        class="h-auto w-25 text-white bg-gradient-to-r from-green-400 via-green-500 
                                                                        to-green-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none 
                                                                        focus:ring-green-300 dark:focus:ring-green-800 shadow-lg shadow-green-500/50 
                                                                        dark:shadow-lg dark:shadow-green-800/80 font-medium rounded-lg text-xs px-3 py-1 text-center me-2 mb-2" 
                                                                    >
                                                                       ✏️Modifier
                                                                    </MazBtn>
                                                                </InertiaLink> 
                                                            </div>
                                                            <div class="container">
                                                                <p></p>
                                                            </div>
                                                            <div class="mt-2">
                                                                <MazBtn 
                                                                    color="danger" size="medium"   
                                                                    @click="() => supprimerCourrierArrivee(arrivee)"
                                                                    class="h-auto w-28 text-white bg-gradient-to-r from-danger-500 via-danger-600 
                                                                    to-danger-700 hover:bg-gradient-to-br focus:ring-4 focus:outline-none 
                                                                    focus:ring-danger-300 dark:focus:ring-danger-800 shadow-lg shadow-danger-500/50 
                                                                    dark:shadow-lg dark:shadow-danger-800/80 font-medium rounded-lg text-xs px-3 py-1 text-center me-2 mb-2"
                                                                >
                                                                    🗑️Supprimer
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
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

.animate-fade-in {
    animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

table tr td {
  padding-top: 0.25rem; /* équivaut à py-1 */
  padding-bottom: 0.25rem;
  line-height: 1rem; /* équivaut à leading-3 */
  font-size: 0.80rem; /* équivaut à text-xs */
}


</style>
 
 
