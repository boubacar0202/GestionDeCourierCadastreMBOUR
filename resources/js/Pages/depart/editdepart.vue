<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import MazBtn from "maz-ui/components/MazBtn";
import BaseInput from "@/Components/BaseInput.vue";
import BaseSelect from "@/Components/BaseSelect.vue";
import { onMounted, ref, watch, watchEffect, computed  } from "vue";
import axios from "axios";
import { toast } from "@/Components/AppToast";  
import { Inertia } from '@inertiajs/inertia';  
import { router } from '@inertiajs/vue3' 
   
  
const { departs, arrivees } = defineProps({
    arrivee: Object,
    depart: Object,
    departs: Object,
    arrivees: Array, 
})
 
const form = useForm({
    //  courierdepart 
    txt_bordereaucd: departs?.txt_bordereaucd || '',
    txt_numdordrecd: departs?.txt_numdordrecd || '',
    dt_datecouriercd: departs?.dt_datecouriercd || '',
    txt_caracterecd: departs?.txt_caracterecd || '', 
    txt_categoriecd: departs?.txt_categoriecd || '',
    txt_referencecourierarriveecd: departs?.txt_referencecourierarriveecd || '',
    txt_referencecourierdepartcd: departs?.txt_referencecourierdepartcd || '',
    txt_nombrepiececd: departs?.txt_nombrepiececd || '',
    txt_referencecd: departs?.txt_referencecd || '',
    txt_objetcd: departs?.txt_objetcd || '',

    txt_nicadcd: departs?.txt_nicadcd || '',
    txt_situationcd: departs?.txt_situationcd || '',
    txt_prenomcd: departs?.txt_prenomcd || '',
    txt_nomcd: departs?.txt_nomcd || '',
    txt_surfacecd: departs?.txt_surfacecd || '',
    txt_numLotcd: departs?.txt_numLotcd || '',

    txt_destinatairecd: departs?.txt_destinatairecd || '',
    dt_dateenvoicd: departs?.dt_dateenvoicd || '',
    txt_referencereceptioncd: departs?.txt_referencereceptioncd || '',
    txt_observationcd: departs?.txt_observationcd || '',
    txt_dureetraitementcd: departs?.txt_dureetraitementcd || '',
    fichierPDFcd: departs?.fichierPDFcd || null,
});
  
const show = ref(false);
const handleCategorieChangecd = () => {
    show.value = form.txt_categoriecd === "Reponse à un Courrier arrivé" || form.txt_categoriecd === "Dossier Retourné";
};
watch(() => form.txt_categoriecd, (newValue) => {
    show.value = newValue === "Reponse à un Courrier arrivé" || newValue === "Dossier Retourné";
});

 
// récuperer les categories de courrier arrivee
const categoriescd = {
    "1": "Reponse à un Courrier arrivé",
    "2": "Demande",
    "3": "Transmission de Documents",
    "4": "Information", 
}; 
const designationsParCategiriecd = {
    'Reponse': ['27/C.C.C.KD du 24/01/2025', '0017/SRPS/K du 16/042025'], 
};
 
// Références disponibles 
const references = ref([]);
// const expediteurs = ref([]);
 
const referenceToExpediteur = ref({});
const referenceToObject = ref({}); 
 
// Surveille le changement de catégorie pour charger les références liées
watch(() => form.txt_categoriecd, async (newCategorie) => {
    if (!newCategorie) return;

    try {
        const res = await axios.post('/fetch-references-arrivee', {
            categorie: newCategorie
        });

        // ✅ Mise à jour des références
        references.value = res.data.references || [];

        // ✅ Mapping de référence vers expéditeur
        referenceToExpediteur.value = res.data.map_ref_to_expediteur || {};
        referenceToObject.value = res.data.map_ref_to_objet || {};

        // ✅ Réinitialisation
        form.txt_referencecourierarriveecd = '';
        form.txt_destinatairecd = '';
        form.txt_objetcd = '';
    } catch (e) {
        console.error('❌ Erreur lors de la récupération des références :', e);
        references.value = [];
        referenceToExpediteur.value = {};
        referenceToObject.value = {};
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
 
// reupèration references courrier depart
watch(
    () => [form.txt_numdordrecd, form.dt_datecouriercd],
    ([newNum, newDate]) => {
        const formattedDate = new Date(newDate).toLocaleDateString('fr-FR');
        form.txt_referencecd = `${newNum}/MFB/DGID/CSF/CSF-MB/BCAD du ${formattedDate}`;

        // form.txt_referencecd = newNum + '/MFB/DGID/CSF/CSF-KDG/BCAD' + ' du '  + newDate
    }
);
 
// Surveille le changement de catégorie pour charger les références liées
watch(() => form.txt_categoriecd, async (newCategorie) => {
    if (!newCategorie) return;

    try {
        const res = await axios.post('/fetch-references-arrivee', {
            categorie: newCategorie
        });

        // ✅ Mise à jour des références
        references.value = res.data.references || [];

        // ✅ Mapping de référence vers expéditeur
        referenceToExpediteur.value = res.data.map_ref_to_expediteur || {};

        // ✅ Réinitialisation
        form.txt_referencecourierarriveecd = '';
        form.txt_destinatairecd = '';
    } catch (e) {
        console.error('❌ Erreur lors de la récupération des références :', e);
        references.value = [];
        referenceToExpediteur.value = {};
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

    dateStr = dateStr.replace(/[./]/g, "-");

    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr;
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
// 🔹 Calcul automatique de la durée de traitement
watchEffect(() => {
    if (!["Reponse à un Courrier arrivé", "Dossier Retourne"].includes(form.txt_categoriecd)) {
        form.txt_dureetraitementcd = "";
        return;
    }

    const ref = form.txt_referencecourierarriveecd;
    const dateEnvoi = form.dt_dateenvoicd;

    if (!ref || !dateEnvoi) return;

    // Extraire la date depuis "XXX du 29/10/2025"
    const match = ref.match(/du\s*(\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4})/i);
    if (!match) return;

    const [jour, mois, annee] = match[1].replace(/[.\-]/g, "/").split("/");
    const dateArrivee = new Date(`${annee.length === 2 ? "20" + annee : annee}-${mois}-${jour}`);
    const dEnv = new Date(dateEnvoi.replace(/(\d{2})\/(\d{2})\/(\d{4})/, "$3-$2-$1"));

    const diff = Math.ceil((dEnv - dateArrivee) / (1000 * 3600 * 24));
    form.txt_dureetraitementcd = diff <= 0 ? "24 heures" : `${diff} jours`;
});
 
// Surveille le changement de fichier PDF
function handleFileChangecd(event) {
    const file = event.target.files[0];
    if (file) {
        form.fichierPDFcd = file;
        console.log("Fichier sélectionné:", file.name);
    } else {
        form.fichierPDFcd = null;
    }
}

// ✅ Fonction suppression fichier PDF
const deleteFile = (depart) => {
    if (!depart || !depart.id) {
        console.error('Aucun départ fourni à deleteFile')
        return
    }

    if (confirm('Voulez-vous vraiment supprimer ce fichier PDF ?')) {
        router.delete(`/depart/delete-pdf/${depart.id}`, {
            onSuccess: () => {
                depart.fichierPDFcd = null
                toast.success('Fichier supprimé avec succès ✅')
            },
            onError: (error) => {
                console.error('Erreur lors de la suppression du PDF :', error)
                toast.error('Erreur lors de la suppression du fichier ❌')
            },
        })
    }
}

async function submit() {
    try {
        console.log("📤 Envoi du formulaire départ...");

        const formData = new FormData();
        formData.append('_method', 'PUT'); // Laravel update 
        formData.append('txt_bordereaucd', form.txt_bordereaucd);
        formData.append('txt_numdordrecd', form.txt_numdordrecd);
        formData.append('dt_datecouriercd', form.dt_datecouriercd);
        formData.append('txt_caracterecd', form.txt_caracterecd); 
        formData.append('txt_referencecourierarriveecd', form.txt_referencecourierarriveecd);
        formData.append('dt_dateenvoicd', form.dt_dateenvoicd);
        formData.append('txt_referencecd', form.txt_referencecd);
        formData.append('txt_nombrepiececd', form.txt_nombrepiececd);
        formData.append('txt_categoriecd', form.txt_categoriecd); 
        formData.append('txt_destinatairecd', form.txt_destinatairecd);
        formData.append('txt_objetcd', form.txt_objetcd); 
        
        formData.append('txt_nicadcd', form.txt_nicadcd);
        formData.append('txt_situationcd', form.txt_situationcd);
        formData.append('txt_prenomcd', form.txt_prenomcd)
        formData.append('txt_nomcd', form.txt_nomcd);
        formData.append('txt_surfacecd', form.txt_surfacecd);
        formData.append('txt_numLotcd', form.txt_numLotcd);

        formData.append('txt_referencereceptioncd', form.txt_referencereceptioncd);
        formData.append('txt_dureetraitementcd', form.txt_dureetraitementcd);
        formData.append('txt_observationcd', form.txt_observationcd);

        if (form.fichierPDFcd instanceof File) {
            formData.append('fichierPDFcd', form.fichierPDFcd);
        }

        // Debug
        for (let [key, value] of formData.entries()) {
            console.log(key + ':', value);
        }

        await axios.post(route('depart.update', departs.id), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        toast.success('Modification réussie !');
        Inertia.visit(route('instancedepart.create'), { replace: true });

    } catch (error) {
        console.error('Erreur détaillée:', error.response?.data);
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(err => toast.error(err));
        } else {
            toast.error('Erreur lors de la modification');
        }
    }
}


</script>

<template>
    <Head title="Courrier Départ" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl text-primary-txt font-semibold leading-tight">
                Modification Courrier Départ
                
            </h2>

        </template>

        <div class="py-12">
            <div class="flex justify-center">
                <div class="w-full max-w-8xl px-2 py-2">
                    <div class="bg-white shadow-md rounded-xl">
                        <!-- En-tête du formulaire -->
                        <div   class="p-4 border-b bg-primary rounded-t-xl">
                            <h2 class="text-2xl leading-tight text-white font-bold">Modification du courrier départ</h2>
                        </div>
                        <!-- Corps du formulaire -->
                        <form @submit.prevent="submit">
                            <div class="p-6">
                                <!-- Section Parcelle -->
                                <h5 class="text-lg text-primary-txt font-bold">
                                    Modification du Courrier N° : {{ departs?.txt_numdordrecd }}
                                </h5>
                                <br />
                                <div class="mb-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="N° BE"
                                            name="txt_bordereau"
                                            v-model="form.txt_bordereaucd"
                                            autocomplete="on"
                                        />
                                    </div><br>
                                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="N° Dordre"
                                            name="txt_numdordrecd"
                                            v-model="form.txt_numdordrecd"
                                            required
                                        />
                                        <BaseInput
                                            label="Date Courrier"
                                            name="dt_datecouriercd"
                                            type="date"
                                            v-model="form.dt_datecouriercd"
                                        />
                                        <BaseSelect
                                            class="sm:col-span-2"
                                            label="Catégorie"
                                            name="txt_categoriecd"
                                            v-model="form.txt_categoriecd"
                                            @change="handleCategorieChangecd"
                                            :options="[
                                                { value: 'Reponse à un Courrier arrivé', label: 'Reponse à un Courrier arrivé' },
                                                { value: 'Demande', label: 'Demande' },
                                                { value: 'Transmission de Documents', label: 'Transmission de Documents' },
                                                { value: 'Information', label: 'Information' },
                                                { value: 'Alerte', label: 'Alerte' },
                                                { value: 'Signalement', label: 'Signalement' },
                                                { value: 'Dossier Retourné', label: 'Dossier Retourné' },
                                            ]"
                                        />
                                        <BaseSelect
                                            class="sm:col-span-2"
                                            label="Ref.Courrier Arrivée à Repondre"
                                            name="txt_referencecourierarriveecd"
                                            v-model="form.txt_referencecourierarriveecd"
                                            :options="references"
                                            placeholder="Choisir une référence"
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Nbre de pièces"
                                            name="txt_nombrepiececd"
                                            v-model="form.txt_nombrepiececd"
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
                                            label="Numéro Lot"
                                            name="txt_numLotcd"
                                            v-model="form.txt_numLotcd"
                                            autocomplete="off"
                                        />
                                        <BaseInput
                                            label="Superficie"
                                            name="txt_surfacecd"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            v-model="form.txt_surfacecd"
                                            autocomplete="off"
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Prénom"
                                            name="txt_prenomcd"
                                            v-model="form.txt_prenomcd"
                                            autocomplete="off"
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
                                            autocomplete="off"
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
                                            required
                                        />
                                        <BaseInput
                                            label="Réf.Récéption"
                                            name="txt_referencereceptioncd"
                                            v-model="form.txt_referencereceptioncd"
                                            required
                                        />
                                        <BaseInput
                                            label="Date d'envoi"
                                            name="dt_dateenvoicd"
                                            type="date"
                                            v-model="form.dt_dateenvoicd"
                                            required
                                        />
                                        <BaseInput
                                            v-if="!show"
                                            class="sm:col-span-2"
                                            label="Durrée de traitement"
                                            name="txt_dureetraitementcd"
                                            v-model="form.txt_dureetraitementcd"
                                        />
                                        <BaseSelect
                                            class="sm:col-span-2"
                                            label="Caractères"
                                            name="txt_caracterecd"
                                            v-model="form.txt_caracterecd"
                                            :options="[
                                                { value: 'Connfidentiel', label: 'Confidentiel' },
                                                { value: 'Urgent', label: 'Urgent' },
                                                { value: 'Secret', label: 'Secret' },
                                            ]"
                                        />
                                        <BaseInput
                                            class="sm:col-span-4"
                                            label="Observation"
                                            name="txt_observationcd"
                                            v-model="form.txt_observationcd"
                                        />
                                        <div class="sm:col-span-2">
                                            <BaseInput
                                                label="Importer le Fichier PDF"
                                                name="fichierPDFcd"
                                                type="file"
                                                accept="application/pdf"
                                                autocomplete="off"
                                                @change="handleFileChangecd"
                                            />
                                            <div v-if="departs?.fichierPDFcd" class="mt-2 flex items-center space-x-2">
                                                <a :href="`/fichiers/${departs.fichierPDFcd}`" target="_blank" class="text-sm text-blue-600 underline">
                                                    Voir le PDF
                                                </a>
                                                <button type="button" @click="deleteFile(departs)" class="text-sm text-red-600 hover:text-red-800">
                                                    Supprimer
                                                </button>
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

