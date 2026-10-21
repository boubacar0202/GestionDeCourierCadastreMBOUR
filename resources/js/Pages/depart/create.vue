<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import MazBtn from "maz-ui/components/MazBtn";
import MazRadio from "maz-ui/components/MazRadio";
import { onMounted, ref, watch, watchEffect, computed  } from "vue";
import axios from "axios";
import { toast } from "@/Components/AppToast";  
import { Inertia } from '@inertiajs/inertia';  
import BaseInput from "@/Components/BaseInput.vue";
import BaseSelect from "@/Components/BaseSelect.vue";
 

const props = defineProps({
    arrivee: Object,    
    arrivees: Array,
    departs: Array,
    depart: Object,
});
 
// useForm
const form = useForm({
    //  courierdepart 
    txt_bordereaucd:"",
    txt_numdordrecd:"",
    txt_caracterecd:"",
    dt_datecouriercd:"",
    txt_categoriecd:"",
    txt_referencecourierarriveecd:"",
    txt_referencecourierdepartcd:"",
    txt_nombrepiececd:"",
    txt_referencecd:"",
    txt_objetcd:"",

    txt_nicadcd:"",
    txt_situationcd:"", 
    txt_prenomcd:"",
    txt_nomcd:"",
    txt_surfacecd:"",
    txt_numLotcd:"",

    txt_destinatairecd:"",
    dt_dateenvoicd:"",
    txt_referencereceptioncd:"",
    txt_observationcd:"",
    txt_dureetraitementcd:"",
    fichierPDFcd: null,
});
 
const fichierPDFcd = ref(null);  
// const fichierPDF = ref(null);

// Récuperer le fichier PDF 
function handleFileUploadcd(event) {
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

    fichierPDFcd.value = file;
    form.fichierPDFcd = file;

    console.log("Fichier PDF sélectionné :", fichierPDFcd.value);
}

// Comotement de la catégories
const showcd = ref(false);
const handleCategorieChangecd = () => {
    showcd.value = form.txt_categoriecd === "Reponse à un Courrier arrivé" || form.txt_categoriecd === "Dossier Retourne";
};
watch(() => form.txt_categoriecd, (newValue) => {
    showcd.value = newValue === "Reponse à un Courrier arrivé" || newValue === "Dossier Retourne";
});
  
// Automaiser le Numéro d'ordre
const fetchNextDossier = async (annee) => {
    try {
        const response = await axios.get(`/depart/next/${annee}`);
        const numero = response.data.num_dordre;
 
        const date = new Date(form.dt_datecouriercd);
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const year = date.getFullYear();
        const dateFormatee = `${day}-${month}-${year}`;

        form.txt_numdordrecd = `${numero}/${dateFormatee}`;
        console.log("✅ Numéro généré :", form.txt_numdordrecd);

    } catch (error) {
        console.error("❌ Erreur :", error);

        const date = new Date();
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const year = date.getFullYear();
        const dateFormatee = `${day}-${month}-${year}`;

        form.txt_numdordrecd = `0001/${dateFormatee}`;
    }
};

// Regénérer automatiquement quand la date change
watch(() => form.dt_datecouriercd, (nouvelleDate) => {
    if (nouvelleDate) {
        const annee = new Date(nouvelleDate).getFullYear();
        fetchNextDossier(annee);
    }
}); 
  
// Références disponibles 
const references = ref([]);  
const referenceToExpediteur = ref({});
const referenceToObject = ref({}); 
const referenceToNombrePiece = ref({}); 
const referenceToReception = ref({});
const referenceToBordereau = ref({});
const referenceToNumLot = ref({});
const referenceToPrenom = ref({});
const referenceToNom = ref({});
const referenceToSurface = ref({});
const referenceToSituation = ref({});
const referenceToNicad = ref({});
 
// Filtrage dynamique des arrivées par catégorie  
watch(() => form.txt_categoriecd, async (newCategorie) => {
    if (!newCategorie) return;

    try {
        const res = await axios.post('/fetch-references-arrivee', {
            categorie: newCategorie
        });
 
        references.value = res.data.references || []; 
 
        referenceToExpediteur.value = res.data.map_ref_to_expediteur || {};
        referenceToObject.value = res.data.map_ref_to_objet || {};
        referenceToNombrePiece.value = res.data.map_ref_to_nombrePiece || {};
        referenceToReception.value = res.data.map_ref_to_reception || {};

        referenceToBordereau.value = res.data.map_ref_to_bordoreau || {};
        referenceToNumLot.value = res.data.map_ref_to_numLot || {};
        referenceToPrenom.value = res.data.map_ref_to_prenom || {};
        referenceToNom.value = res.data.map_ref_to_nom || {};
        referenceToSurface.value = res.data.map_ref_to_surface || {};
        referenceToSituation.value = res.data.map_ref_to_situation || {};
        referenceToNicad.value = res.data.map_ref_to_nicad || {};
  
        form.txt_referencecourierarriveecd = '';
        form.txt_destinatairecd = '';
        form.txt_objetcd = ''; 
        form.txt_nombrepiececd = '';
        form.txt_referencereceptioncd = ''; 
 
        form.txt_bordereaucd = '',
        form.txt_numLotcd = '';
        form.txt_prenomcd = '';
        form.txt_nomcd = '';
        form.txt_surfacecd = '';
        form.txt_situationcd = '';
        form.txt_nicadcd = '';

    } catch (e) {
        console.error('❌ Erreur lors de la récupération des références :', e);
        references.value = []; 
        referenceToExpediteur.value = {};
        referenceToObject.value = {};
        referenceToNombrePiece.value = {};
        referenceToReception.value = {};

        referenceToBordereau.value = {};
        referenceToNumLot.value = {};
        referenceToPrenom.value = {};
        referenceToNom.value = {};
        referenceToSurface.value = {};
        referenceToSituation.value = {};
        referenceToNicad.value = {};
     }
});
 
// Surveille le changement de la référence choisie
watch(() => form.txt_referencecourierarriveecd, (selectedRef) => {
    if (selectedRef && referenceToExpediteur.value[selectedRef]) {
        form.txt_destinatairecd = referenceToExpediteur.value[selectedRef];
    } else {
        form.txt_destinatairecd = '';
    }
});

// Surveiller le categorie et récupérer l'objet 
watch(() => form.txt_referencecourierarriveecd, (selectedRef) => {
    if (selectedRef && referenceToObject.value[selectedRef]) {
        form.txt_objetcd = referenceToObject.value[selectedRef];
    } else {
        form.txt_objetcd = '';
    }
});

// Surveiller le categorie et récupérer Nombre Piècee 
watch(() => form.txt_referencecourierarriveecd, (selectedRef) => {
    if (selectedRef && referenceToNombrePiece.value[selectedRef]) {
        form.txt_nombrepiececd = referenceToNombrePiece.value[selectedRef];
    } else {
        form.txt_nombrepiececd = '';
    }
});

// Surveiller le categorie et récupérer le Num Lot 
watch(() => form.txt_referencecourierarriveecd, (selectedRef) => {
    if (selectedRef && referenceToNumLot.value[selectedRef]) {
        form.txt_numLotcd = referenceToNumLot.value[selectedRef];
    } else {
        form.txt_numLotcd = '';
    }
});

// Surveiller le categorie et récupérer le Prenom 
watch(() => form.txt_referencecourierarriveecd, (selectedRef) => {
    if (selectedRef && referenceToPrenom.value[selectedRef]) {
        form.txt_prenomcd = referenceToPrenom.value[selectedRef];
    } else {
        form.txt_prenomcd = '';
    }
});
 
// Surveiller le categorie et récupérer le Num Lot 
watch(() => form.txt_referencecourierarriveecd, (selectedRef) => {
    if (selectedRef && referenceToNom.value[selectedRef]) {
        form.txt_nomcd = referenceToNom.value[selectedRef];
    } else {
        form.txt_nomcd = '';
    }
});
 
// Surveiller le categorie et récupérer le Num Lot 
watch(() => form.txt_referencecourierarriveecd, (selectedRef) => {
    if (selectedRef && referenceToSurface.value[selectedRef]) {
        form.txt_surfacecd = referenceToSurface.value[selectedRef];
    } else {
        form.txt_surfacecd = '';
    }
});
 
// Surveiller le categorie et récupérer le Num Lot 
watch(() => form.txt_referencecourierarriveecd, (selectedRef) => {
    if (selectedRef && referenceToSituation.value[selectedRef]) {
        form.txt_situationcd = referenceToSituation.value[selectedRef];
    } else {
        form.txt_situationcd = '';
    }
});
 
// Surveiller le categorie et récupérer le Num Lot 
watch(() => form.txt_referencecourierarriveecd, (selectedRef) => {
    if (selectedRef && referenceToNicad.value[selectedRef]) {
        form.txt_nicadcd = referenceToNicad.value[selectedRef];
    } else {
        form.txt_nicadcd = '';
    }
});

// Surveiller le categorie et récupérer le Num Lot 
watch(() => form.txt_referencecourierarriveecd, (selectedRef) => {
    if (selectedRef && referenceToBordereau.value[selectedRef]) {
        form.txt_bordereaucd = referenceToBordereau.value[selectedRef];
    } else {
        form.txt_bordereaucd = (form.txt_numdordrecd || '') + '/MFB/DSF/CSF-MB/BCAD';    }
});
 
// reupèration references courrier depart
watch(
  () => [form.txt_numdordrecd, form.dt_datecouriercd],
  ([newNum, newDate]) => {
    if (!newNum || !newDate){
        form.txt_referencecd = '';
        return;
    }  

    // Sécuriser le parsing de la date
    const parseDate = (str) => {
        if (!str) return null; 
        let parts = str.includes("-") ? str.split("-") : str.split("/");
        if (parts.length !== 3) return null;

        if (parts[0].length === 4) { 
            return new Date(`${parts[0]}-${parts[1]}-${parts[2]}`);
        } else { 
            return new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
        }
    };

    const date = parseDate(newDate);
    if (!date || isNaN(date.getTime())) {
        form.txt_referencecd = "Date invalide";
        return;
    }

    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    const dateFormatee = `${day}-${month}-${year}`;  
 
    const partsNumero = newNum.split("/");
    const complementRef = partsNumero[partsNumero.length - 2].trim();

    form.txt_referencecd = `${complementRef}/MFB/DGID/CSF/CSF-MB/BCAD du ${dateFormatee}`;

  }
);
  
// 🔹 1. Extraire et normaliser la date d'arrivée depuis la référence
const dateArrivee = ref(''); 
watch(
    () => form.txt_referencecourierarriveecd,
    (newReference) => {
        if (!newReference) {
            dateArrivee.value = '';
            return;
        }

        const parts = newReference.split(' du ');
        if (parts.length === 2) {
            let dateString = parts[1].trim();
            dateString = normalizeDate(dateString);
            dateArrivee.value = dateString;
            console.log("✅ Date extraite normalisée :", dateString);
        } else {
            dateArrivee.value = '';
            console.warn("⚠️ Format inattendu :", newReference);
        }
    }
);

// // 🔹 2. Fonction intelligente de normalisation
function normalizeDate(dateStr) {
    if (!dateStr) return "";

    // Remplacer ., / par -
    dateStr = dateStr.replace(/[./]/g, "-");
 
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
        return dateStr; // déjà ISO
    }
 
    if (/^\d{2}-\d{2}-\d{4}$/.test(dateStr)) {
        const [jour, mois, annee] = dateStr.split("-");
        return `${annee}-${mois}-${jour}`;
    }
 
    if (/^\d{2}-\d{2}-\d{2}$/.test(dateStr)) {
        const [jour, mois, annee] = dateStr.split("-");
        return `20${annee}-${mois}-${jour}`;
    }
 
    return "";
}

// 🔹 3. Calcul automatique de la durée de traitement
watch(
    () => [dateArrivee.value, form.dt_dateenvoicd, form.txt_categoriecd],
    ([arrivee, envoie, categorie]) => {
        if (!["Reponse à un Courrier arrivé", "Dossier Retourne"].includes(categorie)) {
        form.txt_dureetraitementcd = "";
        return;
        }

        if (!arrivee || !envoie) return;

        // ✅ Convertir "JJ/MM/AAAA" → "AAAA-MM-JJ" si nécessaire
        const toISO = (date) =>
        /^\d{2}\/\d{2}\/\d{4}$/.test(date)
            ? date.split("/").reverse().join("-")
            : date;

        const dArr = new Date(toISO(arrivee));
        const dEnv = new Date(toISO(envoie));

        const diff = Math.ceil((dEnv - dArr) / (1000 * 3600 * 24));

        form.txt_dureetraitementcd = diff <= 0 ? "24 heures" : `${diff} jours`;
    }
);
 
// Soumission du formulaire
const submitForm = function () {  // Ajoutez `async` ici 
    const today = new Date().toISOString().split('T')[0];

    if (form.dt_dateenvoicd > today) {
        toast.error("La date d'envoi dépasse d'aujourd'hui. CORRIGER");
        return;
    }
    // Formulaire Laravel
    form.post(route('depart.store'), {   
        onSuccess: () => {
            toast.success('Enregistré avec succès')
            Inertia.visit(route("depart.create"), { replace: true });
        },
        onError: (errors) => {
            Object.values(errors).forEach(msg => toast.error(msg))
        }
    })

};

</script>

<template>
    <Head title="Courrier Départ" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-primary-txt">
                Enregistrement des Courriers Départs 
            </h2>

        </template>

        <div class="py-12">
            <div class="flex justify-center">
                <div class="w-full max-w-8xl px-2 py-2">
                    <div class="bg-white shadow-md rounded-xl">
                        <!-- En-tête du formulaire -->
                        <div   class="p-4 border-b bg-primary rounded-t-xl">
                            <h2 class="text-2xl leading-tight text-white font-bold">Formulaire d'enregistrement des Courriers Départs</h2>
                        </div>
                        <!-- Corps du formulaire -->
                        <form @submit.prevent="submitForm">
                            <div class="p-6">
                                <!-- Section Parcelle --> 
                                <div class="mb-6">
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4"
                                    > 

                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="N° BE"
                                            name="txt_bordereaucd"
                                            autocomplete="on"
                                            v-model="form.txt_bordereaucd"
                                        />

                                    </div><br>
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4"
                                    > 
                                        
                                        <BaseInput
                                            label="N° Départ"
                                            name="txt_numdordrecd"
                                            v-model="form.txt_numdordrecd"
                                            required
                                        />
                                                                                
                                        <BaseInput
                                            label="Date Courrier"
                                            name="dt_datecouriercd"
                                            type="date"
                                            v-model="form.dt_datecouriercd"
                                            required
                                        />
                                                
                                        <BaseSelect
                                            class="sm:col-span-2"
                                            label="Catégorie"
                                            name="txt_categoriecd" 
                                            v-model="form.txt_categoriecd"
                                            required
                                            @change="handleCategorieChangecd"
                                            style="max-height: 200px;" 
                                            :options="[
                                                {value: 'Reponse à un Courrier arrivé', label: 'Reponse à un Courrier arrivé'},
                                                {value: 'Demande', label: 'Demande'},
                                                {value: 'Transmission de Documents', label: 'Transmission de Documents'},
                                                {value: 'Information', label: 'Information'},
                                                {value: 'Alerte', label: 'Alerte'},
                                                {value: 'Signalement', label: 'Signalement'},
                                                {value: 'Dossier Retourne', label: 'Dossier Retourne'}
                                            ]"
                                        />

                                        <BaseSelect
                                            v-if="showcd"
                                            class="sm:col-span-2"
                                            label="Ref.Courrier Arrivée à Repondre"
                                            name="txt_referencecourierarriveecd"
                                            v-model="form.txt_referencecourierarriveecd"
                                            :options="references"
                                            placeholder="Choisir une référence"
                                        />  
                                                                      
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Nombre de pièces"
                                            name="txt_nombrepiececd" 
                                            v-model="form.txt_nombrepiececd"
                                            required
                                        />
                                                                                                                                
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Référence Courrier"
                                            name="txt_referencecd" 
                                            v-model="form.txt_referencecd"
                                            required
                                        />
                                                                                                                                                                        
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Objet"
                                            name="txt_objetcd" 
                                            v-model="form.txt_objetcd"
                                            required
                                        />
                                                                                                                                                                                                                
                                        <BaseInput 
                                            label="Numéro lot"
                                            name="txt_numLotcd" 
                                            v-model="form.txt_numLotcd" 
                                        />
                                                                                                                                                                                                                                                        
                                        <BaseInput 
                                            type="nomber"
                                            label="Superficie"
                                            name="txt_surfacecd" 
                                            step="0.01" min="0" 
                                            v-model="form.txt_surfacecd" 
                                        />
                                                                                                                                                                                                                     
                                        <BaseInput 
                                            class="sm:col-span-2"
                                            label="Prénom"
                                            name="txt_prenomcd" 
                                            v-model="form.txt_prenomcd" 
                                        />
                                                                                                                                                                                                                    
                                        <BaseInput 
                                            class="sm:col-span-2"
                                            label="Nom"
                                            name="txt_nomcd" 
                                            v-model="form.txt_nomcd" 
                                            autocomplete="off"
                                        />
                                                                                                                                                                                                                    
                                        <BaseInput 
                                            class="sm:col-span-2"
                                            label="Situation"
                                            name="txt_situationcd" 
                                            v-model="form.txt_situationcd" 
                                            autocomplete="on"
                                        />
                                                                                                                                                                                                                   
                                        <BaseInput 
                                            class="sm:col-span-2"
                                            label="NICAD"
                                            name="txt_nicadcd" 
                                            v-model="form.txt_nicadcd" 
                                            autocomplete="off"
                                        />
                                                                                                                                                                                                                    
                                        <BaseInput 
                                            class="sm:col-span-2"
                                            label="Destinataire"
                                            name="txt_destinatairecd" 
                                            v-model="form.txt_destinatairecd" 
                                            autocomplete="off"
                                        />
                                                                                                                                                                                                                    
                                        <BaseInput 
                                            class="sm:col-span-2"
                                            label="Ref. Décharge"
                                            name="txt_referencereceptioncd" 
                                            v-model="form.txt_referencereceptioncd" 
                                            autocomplete="off"
                                        />
                                                                                                                                                                                                                   
                                        <BaseInput 
                                            class="sm:col-span-1"
                                            label="Date d'envoie"
                                            name="dt_dateenvoicd" 
                                            type="date"
                                            v-model="form.dt_dateenvoicd" 
                                            autocomplete="off"
                                        />
                                                                                                                                                                                                                    
                                        <BaseInput 
                                            v-if="showcd" 
                                            class="sm:col-span-1"
                                            label="Durrée de traitement"
                                            name="txt_dureetraitementcd"
                                            v-model="form.txt_dureetraitementcd" 
                                            autocomplete="off"
                                        />
                                                                                                                                                                                                                   
                                        <BaseSelect 
                                            class="sm:col-span-2"
                                            label="Caractères"
                                            name="txt_caracterecd"
                                            v-model="form.txt_caracterecd" 
                                            :options="[
                                                {value: 'Confidentiel', label: 'Confidentiel'},
                                                {value: 'Urgent', label: 'Urgent'},
                                                {value: 'Secret', label: 'Secret'}
                                            ]"
                                        /> 
                                                                                                                                                                                                                    
                                        <BaseInput  
                                            class="sm:col-span-3"
                                            label="Observation"
                                            name="txt_observationcd"
                                            v-model="form.txt_observationcd" 
                                            autocomplete="off"
                                        />
                                                                                                                                                                                                                 
                                        <BaseInput  
                                            type="file"
                                            class="sm:col-span-2"
                                            label="Importer le fichier PDF"
                                            name="fichier_PDFcd"
                                            @change="handleFileUploadcd" 
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

