<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import MazBtn from "maz-ui/components/MazBtn";
import MazRadio from "maz-ui/components/MazRadio";
import { onMounted, ref, watch, watchEffect, computed  } from "vue";
import axios from "axios";
import { toast } from "@/Components/AppToast";  
import DefaultLayout from "@/Layouts/DefaultLayout.vue";
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
            <h2 class="text-xl text-primary-txt font-semibold leading-tight text-gray-800">
                Modification Courrier Départ
                
            </h2>

        </template>

        <div class="py-12">
            <div class="flex justify-center">
                <div class="w-full max-w-8xl rounded-xl">
                    <div class="bg-white shadow-md rounded-xl">
                        <!-- En-tête du formulaire -->
                        <div   class="p-4 border-b bg-primary rounded-t-xl">
                            <h1 class="text-lg text-white font-semibold">Formulaire de Modification des Courriers Départs </h1>
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
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4"
                                    > 
                                        <div class="sm:col-span-2">
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
                                                        v-model="form.txt_bordereaucd"  
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
                                        <div class="sm:col-span-2">
                                            <label 
                                                for="txt_numdordrecd"
                                                class="block text-sm/6 font-medium text-primary-txt">
                                                N° Dordre
                                            </label>
                                            <div class="mt-2">
                                                <input
                                                    type="text"
                                                    name="txt_numdordrecd"
                                                    v-model="form.txt_numdordrecd" 
                                                    required 
                                                    id="txt_numdordrecd"
                                                    class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                        outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                        focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                />
                                            </div>
                                        </div>  
                                        <div class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label 
                                                    for="dt_datecouriercd"
                                                    class="block text-sm/6 font-medium text-primary-txt">
                                                    Date Courrier
                                                </label>
                                                <div class="mt-2">
                                                    <input
                                                        type="date"
                                                        name="dt_datecouriercd"
                                                        v-model="form.dt_datecouriercd"  
                                                        id="dt_datecouriercd"
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
                                                    for="txt_categoriecd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Catégorie</label
                                                >
                                                <div class="mt-2">
                                                    <select
                                                        name="txt_categoriecd"
                                                        v-model="
                                                            form.txt_categoriecd
                                                        "
                                                        id="txt_categoriecd"
                                                        autocomplete="address-level2"
                                                        @change="handleCategorieChangecd"
                                                        class="h-8  scrollbar-thin scrollbar-thumb-primary scrollbar-track-gray-300 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                        style="max-height: 200px;" 
                                                    >
                                                        <option selected desabled></option>
                                                        <option value="Reponse à un Courrier arrivé">Reponse à un Courrier arrivé</option>
                                                        <option value="Demande">Demande</option>
                                                        <option value="Transmission de Documents">Transmission de Documents</option>
                                                        <option value="Information">Information</option> 
                                                        <option value="Alerte">Alerte</option>
                                                        <option value="Signalement">Signalement</option>
                                                        <option value="Dossier Retourné">Dossier Retourné</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label for="txt_referencecourierarriveecd" class="block text-sm/6 font-medium text-primary-txt">
                                                    Ref.Courrier Arrivée à Repondre
                                                </label> 
                                                <div class="mt-2">
                                                    <select
                                                        name="txt_referencecourierarriveecd"
                                                        v-model="form.txt_referencecourierarriveecd"
                                                        id="txt_referencecourierarriveecd"
                                                        autocomplete="address-level2" 
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    >
                                                        <option value="">Choisir une référence</option>
                                                        <option selected disabled>{{ departs?.txt_referencecourierarriveecd }}</option>
                                                        <option v-for="(ref, index) in references" :key="index" :value="ref">
                                                            {{ ref }}
                                                        </option>  
                                                    </select>
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_nombrepiececd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Nbre de pièces</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_nombrepiececd"
                                                        v-model="
                                                            form.txt_nombrepiececd
                                                        "
                                                        id="txt_nombrepiececd"
                                                        autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-2">
                                                <label
                                                    for="txt_referencecd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Référence Courrier</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="txt"
                                                        name="txt_referencecd"
                                                        v-model="
                                                            form.txt_referencecd
                                                        "
                                                        required
                                                        id="txt_referencecd"
                                                        autocomplete="address-level2"
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
                                                    for="txt_objetcd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Objet</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="txt"
                                                        name="txt_objetcd"
                                                        v-model="
                                                            form.txt_objetcd
                                                        "
                                                        required
                                                        id="txt_objetcd"
                                                        autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_numLotcd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Numéro Lot</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_numLotcd" 
                                                        v-model="
                                                            form.txt_numLotcd
                                                        " 
                                                        id="txt_numLotcd"
                                                            autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />  
                                                </div>
                                            </div>
                                        </div>  
                                        <div class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_surfacecd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Superficie</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="nomber"
                                                        step="0.01" min="0" 
                                                        name="txt_surfacecd"
                                                        v-model="
                                                            form.txt_surfacecd
                                                        " 
                                                        id="txt_surfacecd"
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
                                                    for="txt_prenomcd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Prénom</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_prenomcd"
                                                        v-model="
                                                            form.txt_prenomcd
                                                        " 
                                                        id="txt_prenomcd"
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
                                                    for="txt_nomcd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Nom</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_nomcd"
                                                        v-model="
                                                            form.txt_nomcd
                                                        " 
                                                        id="txt_nomcd"
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
                                                    for="txt_situationcd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Situation</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_situationcd"
                                                        v-model="
                                                            form.txt_situationcd
                                                        " 
                                                        id="txt_situationcd"
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
                                                    for="txt_nicadcd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >NICAD</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_nicadcd"
                                                        v-model="
                                                            form.txt_nicadcd
                                                        " 
                                                        id="txt_nicadcd"
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
                                                    for="txt_destinatairecd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Destinataire</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_destinatairecd"
                                                        v-model="
                                                            form.txt_destinatairecd
                                                        "
                                                        required
                                                        id="txt_destinatairecd"
                                                            autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />  
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_referencereceptioncd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Réf.Récéption</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="txt"
                                                        name="txt_referencereceptioncd"
                                                        v-model="
                                                            form.txt_referencereceptioncd
                                                        "
                                                        required
                                                        id="txt_referencereceptioncd"
                                                        autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="dt_dateenvoicd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Date d'envoi</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="date"
                                                        name="dt_dateenvoicd"
                                                        v-model="
                                                            form.dt_dateenvoicd
                                                        "
                                                        required
                                                        id="dt_dateenvoicd"
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
                                                    for="txt_dureetraitement"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Durrée de traitement</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="txt"
                                                        name="txt_dureetraitementcd"
                                                        v-model="
                                                            form.txt_dureetraitementcd
                                                        " 
                                                        id="txt_dureetraitementcd"
                                                        autocomplete="address-level2"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label 
                                                for="txt_caracterecd"
                                                class="block text-sm/6 font-medium text-primary-txt">
                                                Caractères
                                            </label>
                                            <div class="mt-2">
                                                <select
                                                    type="text"
                                                    name="txt_caracterecd"
                                                    v-model="form.txt_caracterecd" 
                                                    id="txt_caracterecd"
                                                    class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                        outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                        focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                >
                                                    <option selected desabled></option>
                                                    <option value="Connfidentiel">Confidentiel</option>
                                                    <option value="Urgent">Urgent</option>
                                                    <option value="Secret">Secret</option> 
                                                </select>
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
                                                        type="txt"
                                                        name="txt_observationcd"
                                                        v-model="
                                                            form.txt_observationcd
                                                        " 
                                                        id="txt_observationcd"
                                                        autocomplete="address-level2"
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
                                                    for="fichierPDFcd"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Importer le Fichier PDF</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="file"
                                                        name="fichierPDFcd"
                                                        accept="application/pdf"
                                                        @change="handleFileChangecd"
                                                        id="fichierPDFcd"
                                                        autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                                <!-- Pour ajouter un bouton de suppression de fichier -->
                                                <div v-if="departs?.fichierPDFcd" class="mt-2 flex items-center space-x-2">
                                                    <a :href="`/storage/${departs.fichierPDFcd}`" target="_blank" 
                                                    class="text-blue-600 underline text-sm">
                                                        📄 Voir le PDF
                                                    </a>
                                                    <button @click="deleteFile(departs)" 
                                                            class="text-red-600 text-sm hover:text-red-800">
                                                        🗑️ Supprimer
                                                    </button>
                                                </div>
                                                <!-- Fin du bouton de suppression -->
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

