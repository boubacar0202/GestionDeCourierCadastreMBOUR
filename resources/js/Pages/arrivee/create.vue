<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import MazBtn from "maz-ui/components/MazBtn";
import BaseInput from "@/Components/BaseInput.vue";
import BaseSelect from "@/Components/BaseSelect.vue";
import MazRadio from "maz-ui/components/MazRadio";
import { onMounted, ref, watch, watchEffect, computed  } from "vue";
import axios from "axios"; 
import { toast } from "@/Components/AppToast"; 
import { Inertia } from '@inertiajs/inertia';

 
 
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
    'Demande SERVICES': ['Morcellements',  'Demande de Régularisation', 'Réquisition d\'immatriculation', 'Demande Avis Technique', 'Demande de terrain/Echange', 'Prospection de terrain', 
        'Autorisation de construction', 'Demande d\'extraits de plan', 'Autorisation de lotir', 'Demande d\'états des lieux', 'Demande de délimitation', 'Demande de reconstruction', 
        'Réquisition DSCOS', 'Tribunal', 'Litiges','Demande de situation foncière', 'Demande de Cession définitive', 'Demande d\'évaluation',
        'Demande de Cession définitive a Titre Gratuit', 'Demande d\'attestation du Cadastre', 
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
                <div class="w-full max-w-8xl px-2 py-2">
                    <div class="bg-white shadow-md rounded-xl">
                        <!-- En-tête du formulaire -->
                        <div class="p-4 border-b bg-primary flex items-center justify-between rounded-t-xl gap-2">
                            <h2 class="text-2xl leading-tight text-white font-bold">Formulaire d'enregistrement des Courriers Arrivés</h2>
                        </div>
                        <!-- Corps du formulaire -->
                        <form @submit.prevent="submitForm">
                            <div class="p-6">
                                <!-- Section Parcelle -->
                                <div class="mb-6">
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-2"
                                    >  
                                        <BaseInput
                                            v-if="!show"
                                            class="sm:col-span-2"
                                            label="N° BE"
                                            name="txt_bordereau"
                                            autocomplete="on"
                                            v-model="form.txt_bordereau"
                                        />
                                    </div><br>
 
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4"
                                    >  
                                        <BaseInput
                                            label="Date Reception"
                                            name="dt_datearrivee"
                                            v-model="form.dt_datearrivee"
                                            type="date"    
                                        />
                                         
                                        <BaseInput
                                            label="N° Arrivée"
                                            name="txt_numdordre"
                                            v-model="form.txt_numdordre"   
                                        />
                                  
                                        <BaseInput
                                            label="N° Courrier"
                                            name="txt_numcourier"
                                            v-model="form.txt_numcourier"   
                                        />
                               
                                        <BaseInput
                                            label="Date Courrier"
                                            name="dt_datecourier"
                                            v-model="form.dt_datecourier"   
                                            type="date"
                                        />
 
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Ref.Courriers Arrivée"
                                            name="txt_reference"
                                            v-model="form.txt_reference"
                                        />
 
                                        <BaseSelect
                                            class="sm:col-span-2"
                                            label="Catégorie"
                                            name="txt_categorie"
                                            v-model="form.txt_categorie"
                                            @change="handleCategorieChange" 
                                            :options="[
                                                {value: 'Demande SERVICES', label: 'Demande SERVICES'},
                                                {value: 'Convocation - Invitation', label:  'Convocation - Invitation'},
                                                {value: 'Information', label: 'Information'},
                                                {value: 'Réclamation - Signalement', label: 'Réclamation - Signalement'},
                                                {value: 'Réquisition - Instruction', label: 'Réquisition - Instruction'}
                                            ]"
                                        />

                                        <BaseSelect
                                            class="sm:col-span-2"
                                            label="Designation"
                                            name="txt_designation"
                                            v-model="form.txt_designation"
                                            :options="designations"
                                            placeholder="Choisis catégorie d'abord"
                                            required
                                        />
 
                                        <BaseInput
                                            v-if="show" 
                                            label="Date Evenement"
                                            name="dt_date"
                                            type="date"
                                            v-model="form.dt_date"
                                        /> 
                                        
                                        <BaseInput
                                            v-if="show" 
                                            label="Heure Evenement"
                                            name="txt_heure"
                                            type="time"
                                            v-model="form.txt_heure"
                                        />
 
                                        <BaseInput
                                            v-if="show" 
                                            label="Lieu Evenement"
                                            name="txt_lieu"
                                            v-model="form.txt_lieu"
                                        />
  
                                        <BaseInput 
                                            class="sm:col-span-2" 
                                            label="Nombre de dossiers"
                                            name="txt_nombrepiece"
                                            v-model="form.txt_nombrepiece"
                                        />
                   
                                        <BaseInput 
                                            class="sm:col-span-2" 
                                            label="Objet"
                                            name="txt_objet"
                                            v-model="form.txt_objet"
                                        />
                                                                    
                                        <BaseInput 
                                            v-if="!show" 
                                            label="Numéro Lot"
                                            name="txt_numLot"
                                            v-model="form.txt_numLot"
                                        />
                                                           
                                        <BaseInput 
                                            v-if="!show" 
                                            step="0.01" min="0"
                                            label="Superficie"
                                            name="txt_surface"
                                            v-model="form.txt_surface"
                                        />
                                                                                                     
                                        <BaseInput 
                                            class="sm:col-span-2" 
                                            v-if="!show"  
                                            label="Prénom"
                                            name="txt_prenom"
                                            autocomplete="address-level2"
                                            v-model="form.txt_prenom"
                                        />
                                                                                                                                              
                                        <BaseInput 
                                            class="sm:col-span-2" 
                                            v-if="!show"  
                                            label="Nom"
                                            name="txt_nom"
                                            autocomplete="address-level2"
                                            v-model="form.txt_nom"
                                        />
                                                                                                                                                                                                                                     
                                        <BaseInput 
                                            class="sm:col-span-2" 
                                            v-if="!show"  
                                            label="Situation"
                                            name="txt_situation"
                                            autocomplete="address-level2"
                                            v-model="form.txt_situation"
                                        />
 
                                        <BaseInput 
                                            class="sm:col-span-2" 
                                            v-if="!show"  
                                            label="NICAD"
                                            name="txt_nicad"
                                            autocomplete="off"
                                            v-model="form.txt_nicad"
                                        />
 
                                        <BaseInput 
                                            class="sm:col-span-2" 
                                            label="Expéditeur"
                                            name="txt_expediteur"
                                            autocomplete="address-level2"
                                            v-model="form.txt_expediteur"
                                        />
                                                                                                                                                                                                                                                                                                            
                                        <BaseSelect  
                                            class="sm:col-span-2"
                                            label="Agent Traiteur"
                                            name="txt_agenttraiteur"
                                            v-model="form.txt_agenttraiteur"
                                            :options="[
                                                {value: 'Abdoul Karim KANE', label: 'Abdoul Karim KANE'},
                                                {value: 'Lamine DIAGNE', label: 'Lamine DIAGNE'},
                                                {value: 'Cheikh Tidiane WADE', label: 'Cheikh Tidiane WADE'},
                                                {value: 'Sokhna Arame BA', label: 'Sokhna Arame BA'},
                                                {value: 'Moustapha MBENGUE', label: 'Moustapha MBENGUE'},
                                                {value: 'Makhtar CISSE', label: 'Makhtar CISSE'},
                                                {value: 'Daouda LEYE', label: 'Daouda LEYE'},
                                                {value: 'Cheikh DIOP', label: 'Cheikh DIOP'},
                                                {value: 'Tening', label: 'Tening'},
                                                {value: 'Suzanne', label: 'Suzanne'},
                                                {value: 'Khady THIADOUM', label: 'Khady THIADOUM'},
                                                {value: 'Rokhaya S. SIDIBE', label: 'Rokhaya S. SIDIBE'},
                                                {value: 'Aly FAYE', label: 'Aly FAYE'},
                                                {value: 'Baidy BA', label: 'Baidy BA'},
                                                {value: 'Elhadj Senghane THIAM', label: 'Elhadj Senghane THIAM'},
                                                {value: 'Mamadou DIOUF', label: 'Mamadou DIOUF'},
                                                {value: 'Matalibé DIALLO', label: 'Matalibé DIALLO'},
                                                {value: 'Mouhamed SOW', label: 'Mouhamed SOW'},
                                                {value: 'Oumar NDIAYE', label: 'Oumar NDIAYE'},
                                                {value: 'Mme TOURE', label: 'Mme TOURE'},
                                                {value: 'Dorine DIOP', label: 'Dorine DIOP'},
                                                {value: 'Pape DIALLO', label: 'Pape DIALLO'},
                                                {value: 'Alioune NIANG', label: 'Alioune NIANG'},
                                                {value: 'Boly DIOP', label: 'Boly DIOP'},
                                                {value: 'Djiby THIANDUM', label: 'Djiby THIANDUM'},
                                                {label: 'Awa SOW', label: 'Awa SOW'}
                                            ]"
                                        />
                                                                                                                                                                                                                                                                                                             
                                        <BaseSelect
                                            class="sm:col-span-2"
                                            label="Caractères"
                                            name="txt_caractere" 
                                            v-model="form.txt_caractere"
                                            :options="[
                                                {value: 'Ordinaire', label: 'Ordinaire'},
                                                {value: 'Confidentiel', label: 'Confidentiel'},
                                                {value: 'Urgent', label: 'Urgent'},
                                                {value: 'Secret', label: 'Secret'}
                                            ]"
                                        />
                                                                                                                                                                                                                                                                                                             
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Observation"
                                            name="txt_observation" 
                                            v-model="form.txt_observation"
                                        />
                                                                                                                                                                                                                                                                                                                                                      
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Importer le Fichier PDF"
                                            name="fichier_PDF" 
                                            @change="handleFileUpload"
                                            type="file" 
                                        /> 
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


