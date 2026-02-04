<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import MazBtn from "maz-ui/components/MazBtn";
import MazSelect from "maz-ui/components/MazSelect";
import MazRadio from "maz-ui/components/MazRadio";
import { onMounted, ref, watch, watchEffect, computed  } from "vue";
import axios from "axios";
import { useToast } from "maz-ui"; 
import DefaultLayout from "@/Layouts/DefaultLayout.vue";
import { Inertia } from '@inertiajs/inertia';

 

defineOptions({ layout: DefaultLayout });
const toast = useToast();
const fichierPDF = ref(null);
const today = new Date().toISOString().split('T')[0];


// Récuperer le fichier PDF 
function handleFileUpload(event) {
    const file = event.target.files[0];

    if (!file) return;

    if (file.type !== "application/pdf") {
        toast.error("Veuillez sélectionner un fichier PDF valide.");
        event.target.value = ""; // reset input
        return;
    }

    if (file.size > 100 * 1024 * 1024) { // 100 Mo
        toast.error("Le fichier dépasse 100 Mo !");
        event.target.value = ""; // reset input
        return;
    }

    fichierPDF.value = file;
    form.fichierPDF = file;

    console.log("Fichier PDF sélectionné :", fichierPDF.value);
}

// Formulaire 
const form = useForm({
    //  courrierarrivee
    txt_bordereau:"",
    txt_numdordre:"", 
    txt_caractere:"",
    dt_datearrivee: "",
    txt_numcourier:"",
    dt_datecourier:"",
    txt_reference:"",
    txt_categorie:"",
    txt_designation: '',
    dt_date:"",
    txt_heure:"",
    txt_lieu:"",
    txt_nombrepiece:"",
    txt_objet:"",
    txt_nicad:"",
    txt_situation:"", 
    txt_prenom:"",
    txt_nom:"",
    txt_surface:"",
    txt_numLot:"",
    txt_expediteur:"",
    txt_agenttraiteur:"",
    txt_observation:"",
    fichierPDF: null,

});
  
// Génération automatique du numéro d'ordre
const fetchNextDossier = async (annee) => {
    try {
        const response = await axios.get(`/arrivee/next/${annee}`);
        const numero = response.data.num_dordre;

        // ✅ Formater la date au format DD-MM-YYYY
        const date = new Date(form.dt_datearrivee);
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const year = date.getFullYear();
        const dateFormatee = `${day}-${month}-${year}`;

        form.txt_numdordre = `${numero}/${dateFormatee}`;
        console.log("✅ Numéro généré :", form.txt_numdordre);

    } catch (error) {
        console.error("❌ Erreur :", error);

        const date = new Date();
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const year = date.getFullYear();
        const dateFormatee = `${day}-${month}-${year}`;

        form.txt_numdordre = `00001/${dateFormatee}`;
    }
};

// Regénérer automatiquement quand la date change
watch(() => form.dt_datearrivee, (nouvelleDate) => {
    if (nouvelleDate) {
        const annee = new Date(nouvelleDate).getFullYear();
        fetchNextDossier(annee);
    }
});


// Afficher ou cacher les champs 
const choix = [
    "Convocation - Invitation",
    "Information",
    "Réclamation - Signalement",
    "Réquisition - Instruction"
];
const show = ref(false);
const handleCategorieChange = () => {
    //show.value = form.txt_categorie === "Convocation - Invitation";
      show.value = choix.includes(form.txt_categorie);
};
watch(() => form.txt_categorie, (newValue) => {
    //show.value = newValue === "Convocation - Invitation";
      show.value = choix.includes(newValue);
});
 
// récuperer les categories de courrier arrivee
const categories = {
    "1": "Demande SERVICES",
    "2": "Convocation - Invitation",
    "3": "Information",
    "4": "Réclamation - Signalement",
    "5": "Réquisition - Instruction" 
};
const designationsParCategirie = {
    'Demande SERVICES': ['Morcellements', 'Réquisition d\'immatriculation', 'Demande Avis Technique', 'Demande de terrain/Echange', 'Prospection de terrain', 
        'Autorisation de construction', 'Demande d\'extraits de plan', 'Autorisation de lotir', 'Demande d\'états des lieux', 'Demande de délimitation', 'Demande de reconstruction', 
        'Réquisition DSCOS', 'Tribunal', 'Litiges','Demande de situation foncière', 'Demande de Cession définitive', 'Demande d\'évaluation',
        'Demande de Cession définitive a Titre Gratuit', 'Demande de Régularisation', 'Demande d\'attestation du Cadastre', 
        'Réceptions de lotissements', 'Demande de CIC', 'Duplication de CIC', 'Demande de Titre foncier', 
        'Autirisationde morceler', 'Autre'
    ],
    'Convocation - Invitation': ['Réunion', 'Audience Publique', 'Alerte', 'Visite de site', 
        'Rencontre', 'Randonnée', 'Marche', 'Session', 'Congré', 'Cérémonie', 'Inauguration',
        'Journée dédiée', 'Forum', 'Formation', 'Séminaire', 'Caravane'
    ],
    'Information': ['Note de service', 'Circulaire', 'Rapport', 
        'Transmission lettre', 'Document administratif', 'Texte Juridique',
        'Lettre', 'Rapport d\'étude impact', 'PV - Compte - rendu', 'Texte juridique', 'Décision - Arrêté', 'Délibération', 'Résolution', 'Autres'
    ],
    'Réclamation - Signalement': ['Dénonciation', 'Plainte', 
        'Alerte'
    ],
    'Réquisition - Instruction': ['Réquisition matériel', 'Instruction à suivre', 'DE' 
    ], 
 
};
 
const designations = ref([]);
watch(() => form.txt_categorie, (newCategorie) => {
    designations.value = designationsParCategirie[newCategorie] || [];
    form.txt_designation = '' || 'choisis Catégiries'
});
 
// reupèration references courrier arrivée
watch(
  () => [form.txt_numcourier, form.dt_datecourier],
  ([newNum, newDate]) => {
    if (!newDate) return;

    const date = new Date(newDate);
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();

    const dateFormatee = `${day}-${month}-${year}`; // "29-08-2026"
    form.txt_reference = `${newNum} du ${dateFormatee}`;
  }
);


// Soumission du formulaire
const submitForm = function () {  // Ajoutez `async` ici
    console.log("📤 Envoi du formulaire :", form);
    console.log("✅ Données finales envoyées à Laravel :", form.data()); 
  
    // Formulaire Laravel
    form.post(route('arrivee.store'), {
        onSuccess: () => {
            toast.success('Enregistré avec succès')
            Inertia.visit(route("arrivee.create"), { replace: true });
        },
        onError: (errors) => {
            Object.values(errors).forEach(msg => toast.error(msg))
        }
    }) 
};
 
</script>

<template>
    <Head title="Courrier Arrivées" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-primary-txt">
                Enregistrement des Courriers Arrivées
            </h2>

        </template>

        <div class="py-12">
            <div class="flex justify-center">
                <div class="w-full border border-primary-only max-w-8xl rounded-xl">
                    <div class="bg-white shadow-md-64 sm:rounded-xl">
                        <!-- En-tête du formulaire -->
                        <div   class="p-4 border-b bg-primary sm:rounded-t-xl">
                            <h1 class="text-lg text-white font-semibold">Formulaire d'enregistrement des Courriers Arrivés</h1>
                        </div>
                        <!-- Corps du formulaire -->
                        <form @submit.prevent="submitForm">
                            <div class="p-6">
                                <!-- Section Parcelle -->
                                <div class="mb-6">
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4"
                                    > 
                                        <div v-if="!show" class="sm:col-span-2">
                                            <div class="sm:col-span-2">
                                                <label 
                                                    for="txt_bordereau"
                                                    class="block text-sm/6 font-medium text-primary-txt">
                                                    N° BE
                                                </label>
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_bordereau"  
                                                        v-model="form.txt_bordereau"  
                                                        id="txt_bordereau"
                                                        autocomplete="on"
                                                        class="h-8 block w-full bg-gray-100 rounded-md bg-white px-3 py-1.5
                                                            text-base text-primary outline outline-1 -outline-offset-1 outline-primary-only 
                                                            placeholder:text-primary-dark focus:outline focus:outline-2 focus:-outline-2 
                                                            focus:outline-primary sm:text-sm/6" 
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div><br>

                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4"
                                    > 
                                        <div class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label 
                                                    for="dt_datearrivee"
                                                    class="block text-sm/6 font-medium text-primary-txt">
                                                    Date Réception
                                                </label>
                                                <div class="mt-2">
                                                    <input
                                                        type="date"
                                                        name="dt_datearrivee"
                                                        v-model="form.dt_datearrivee"   
                                                        :max="today"
                                                        required
                                                        id="dt_datearrivee"
                                                        autocomplete="off"
                                                        class="h-8  scrollbar-thin scrollbar-thumb-primary block w-full rounded-md bg-white 
                                                            px-3 py-1.5 text-base text-primary-txt outline outline-1 -outline-offset-1 outline-primary-only 
                                                            placeholder:text-primary-dark focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/" 
                                                    />
                                                </div>
                                            </div>
                                        </div> 
                                        
                                        <div class="sm:col-span-1">
                                            <div class="sm:col-span-2">
                                                <label 
                                                    for="txt_numdordre"
                                                    class="block text-sm/6 font-medium text-primary-txt">
                                                    N° Arrivée
                                                </label>
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_numdordre"   
                                                        required
                                                        v-model="form.txt_numdordre"  
                                                        id="txt_numdordre"
                                                        class="h-8 block w-full bg-gray-100 rounded-md bg-white px-3 py-1.5
                                                            text-base text-primary outline outline-1 -outline-offset-1 outline-primary-only 
                                                            placeholder:text-primary-dark focus:outline focus:outline-2 focus:-outline-2 
                                                            focus:outline-primary sm:text-sm/6" 
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                 
                                        <div class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_numcourier"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >N° Courrier</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_numcourier"
                                                        v-model="form.txt_numcourier"
                                                        required
                                                        id="txt_numcourier"
                                                        autocomplete="address-level2"
                                                        class="h-8  scrollbar-thin scrollbar-thumb-primary block w-full rounded-md bg-white 
                                                            px-3 py-1.5 text-base text-primary-txt outline outline-1 -outline-offset-1 outline-primary-only 
                                                            placeholder:text-primary-dark focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    > 
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="dt_datecourier"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Date Courrier</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="date"
                                                        name="dt_datecourier"
                                                        v-model="form.dt_datecourier"
                                                        :max="today"
                                                        required
                                                        id="dt_datecourier"
                                                        autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    > 
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_reference"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Réf.Courrier Arrivée</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_reference"
                                                        v-model="form.txt_reference"
                                                        required
                                                        id="txt_reference"
                                                        autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_categorie"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Catégorie</label
                                                >
                                                <div class="mt-2">
                                                    <MazSelect
                                                        type="select"
                                                        name="txt_categorie"
                                                        v-model="form.txt_categorie"
                                                        @change="handleCategorieChange" 
                                                        required
                                                        id="txt_categorie"
                                                        autocomplete="off"
                                                
                                                            :options="[
                                                                'Demande SERVICES',
                                                                'Convocation - Invitation',
                                                                'Information',
                                                                'Réclamation - Signalement',
                                                                'Réquisition - Instruction'
                                                            ]" 
                                                            class="h-8  w-full block w-full rounded-md bg-white px-2 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6
                                                            scrollbar-thin scrollbar-thumb-primary scrollbar-track-primary-darlk"
                                                            option-class="custom-option"   
                                                            option-selected-class="bg-primary text-white  "
                                                        
                                                    />   
                                                </div>
                                            </div>
                                        </div> 

                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                for="txt_designation"
                                                class="block text-sm/6 font-medium text-primary-txt"
                                                >
                                                Designation
                                                </label>
                                                <div class="mt-1">
                                                <MazSelect
                                                    v-model="form.txt_designation"
                                                    id="txt_designation"
                                                    name="txt_designation"
                                                    :options="designations"
                                                    placeholder="Choisis catégorie d'abord"
                                                    class="h-8  w-full block w-full rounded-md bg-white px-2 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6
                                                            scrollbar-thin scrollbar-thumb-primary scrollbar-track-primary-darlk"
                                                            option-class="custom-option"   
                                                            option-selected-class="bg-primary text-white"
                                                    required
                                                />
                                                </div>
                                            </div>
                                        </div>

                                        <div  v-if="show"  class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="dt_date"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >
                                                    Date Evenement
                                                </label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="date"
                                                        name="dt_date"
                                                        v-model="form.dt_date"  
                                                        id="dt_date"
                                                        autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div  v-if="show" class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_heure"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Heure Evenement</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="time"
                                                        name="txt_heure"
                                                        v-model="form.txt_heure" 
                                                        id="txt_heure"
                                                        autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div  v-if="show" class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_lieu"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Lieu Evenement</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_lieu"
                                                        v-model="form.txt_lieu" 
                                                        id="txt_lieu"
                                                        autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_nombrepiece"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Nombre de Dossier</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_nombrepiece"
                                                        v-model="form.txt_nombrepiece"
                                                        required
                                                        id="txt_nombrepiece"
                                                        autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_objet"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Objet</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_objet"
                                                        v-model="form.txt_objet"
                                                        required
                                                        id="txt_objet"
                                                        autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div> 
                                        <div v-if="!show" class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_numLot"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Numéro Lot</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_numLot" 
                                                        v-model="
                                                            form.txt_numLot
                                                        " 
                                                        id="txt_numLot"
                                                            autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />  
                                                </div>
                                            </div>
                                        </div>  
                                        <div v-if="!show" class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_surface"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Superficie</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="nomber"
                                                        step="0.01" min="0" 
                                                        name="txt_surface"
                                                        v-model="
                                                            form.txt_surface
                                                        " 
                                                        id="txt_surface"
                                                            autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />  
                                                </div>
                                            </div>
                                        </div> 
                                        <div v-if="!show" class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_prenom"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Prénom</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_prenom"
                                                        v-model="
                                                            form.txt_prenom
                                                        " 
                                                        id="txt_prenom"
                                                            autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />  
                                                </div>
                                            </div>
                                        </div> 
                                         <div v-if="!show" class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_nom"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Nom</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_nom"
                                                        v-model="
                                                            form.txt_nom
                                                        " 
                                                        id="txt_nom"
                                                            autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />  
                                                </div>
                                            </div>
                                        </div> 
                                        <div v-if="!show" class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_situation"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Situation</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_situation"
                                                        v-model="
                                                            form.txt_situation
                                                        " 
                                                        id="txt_situation"
                                                            autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />  
                                                </div>
                                            </div>
                                        </div> 
                                        <div v-if="!show" class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_nicad"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >NICAD</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_nicad"
                                                        v-model="
                                                            form.txt_nicad
                                                        " 
                                                        id="txt_nicad"
                                                            autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />  
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_expediteur"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Expéditeur</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_expediteur"
                                                        v-model="form.txt_expediteur"
                                                        required
                                                        id="txt_expediteur"
                                                        autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_agenttraiteur"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Agent Traiteur</label
                                                >
                                                <div class="mt-2">
                                                    <MazSelect
                                                        type="select"
                                                        name="txt_agenttraiteur"
                                                        v-model="form.txt_agenttraiteur" 
                                                        required
                                                        id="txt_agenttraiteur"
                                                        autocomplete="off"
                                                        :options="[
                                                            'Abdoul Karim KANE',
                                                            'Lamine DIAGNE',
                                                            'Cheikh Tidiane WADE',
                                                            'Sokhna Arame BA',
                                                            'Moustapha MBENGUE',
                                                            'Makhtar CISSE',
                                                            'Daouda LEYE',
                                                            'Cheikh DIOP',
                                                            'Tening',
                                                            'Suzanne',
                                                            'Khady THIADOUM',
                                                            'Rokhaya S. SIDIBE',
                                                            'Aly FAYE',
                                                            'Baidy BA',
                                                            'Elhadj Senghane THIAM',
                                                            'Mamadou DIOUF',
                                                            'Matalibé DIALLO',
                                                            'Mouhamed SOW',
                                                            'Oumar NDIAYE',
                                                            'Mme TOURE',
                                                            'Dorine DIOP',
                                                            'Pape DIALLO',
                                                            'Alioune NIANG',
                                                            'Boly DIOP',
                                                            'Djiby THIANDUM',
                                                            'Awa SOW'
                                                        ]" 
                                                        class="h-8 w-full block w-full rounded-md bg-white px-2 py-1.5 text-base text-primary-txt 
                                                        outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                        focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6
                                                        scrollbar-thin scrollbar-thumb-primary scrollbar-track-primary-darlk"
                                                        option-class="custom-option"   
                                                        option-selected-class="bg-primary text-white"
                                                    /> 
                                                </div> 
                                            </div>
                                        </div> 
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-2">
                                                <label 
                                                    for="txt_caractere"
                                                    class="block text-sm/6 font-medium text-primary-txt">
                                                    Caractères
                                                </label>
                                                <div class="mt-2">
                                                    <MazSelect
                                                    type="select"
                                                    name="txt_caractere"
                                                    v-model="form.txt_caractere" 
                                                    id="txt_caractere"
                                                    autocomplete="off"
                                                    placeholder="Choisis Caractère"
                                                        :options="[
                                                            'Ordinaire',
                                                            'Confidentiel',
                                                            'Urgent',
                                                            'Secret'
                                                        ]" 
                                                        class="h-8  w-full block w-full rounded-md bg-white px-2 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6
                                                            scrollbar-thin scrollbar-thumb-primary scrollbar-track-primary-darlk"
                                                            option-class="custom-option"   
                                                            option-selected-class="bg-primary text-white" 
                                                    />  
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-4">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_observation"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Observation</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_observation"
                                                        v-model="form.txt_observation"txt_observation
                                                        
                                                        id="txt_observation"
                                                        autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="fichier_PDF"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Importer le Fichier PDF</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="file"
                                                        name="fichier_PDF"
                                                        accept="application/pdf"
                                                        @change="handleFileUpload"
                                                        id="fichier_PDF"
                                                        autocomplete="off"
                                                        placeholder="Importer le fichier"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br /> 
                                </div> 

                                <!-- Bouton de soumission -->

                                <div class="sm:col-span-6 flex justify-center">
                                    <MazBtn 
                                        type="submit" no-shadow no-hover-effect
                                        class="w-64 h-10 text-white bg-gradient-to-r from-primary via-primary-dark 
                                            to-primary hover:bg-gradient-to-br focus:ring-4 focus:outline-none 
                                            focus:ring-blue-300 dark:focus:ring-blue-800 shadow-lg shadow-blue-500/50 
                                            dark:shadow-lg dark:shadow-blue-800/80 font-medium rounded-lg text-sm px-5 
                                            py-2.5 text-center me-2 mb-2"
                                            size="medium"
                                    > 
                                        Enregistrer
                                    </MazBtn>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


<style scoped>
/* ::v-deep permet de cibler les éléments dans le portal */
::v-deep(.custom-option:hover),
::v-deep(.custom-option:focus),
::v-deep(.custom-option[aria-selected="true"]) {
  background-color: #c7d2fe; /* Couleur claire type indigo-200 */
  color: #1e293b; /* Couleur du texte, exemple slate-800 */
}
</style>


