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
import { router } from '@inertiajs/vue3';
 
defineOptions({ layout: DefaultLayout });
 
const { arrivees } = defineProps({
    arrivees: Object,  
})
 
const form = useForm({
    //  courrierarrivee
    txt_bordereau: arrivees?.txt_bordereau || '',
    txt_numdordre: arrivees?.txt_numdordre || '',
    dt_datearrivee: arrivees?.dt_datearrivee || '',  
    txt_caractere: arrivees?.txt_caractere || '', 
    txt_numcourier: arrivees?.txt_numcourier || '',
    dt_datecourier: arrivees?.dt_datecourier || '',
    txt_reference: arrivees?.txt_reference || '',
    txt_categorie: arrivees?.txt_categorie || '',
    txt_designation: arrivees?.txt_designation || '',
    dt_date: arrivees?.dt_date || '',
    txt_heure: arrivees?.txt_heure || '',
    txt_lieu: arrivees?.txt_lieu || '',
    txt_nombrepiece: arrivees?.txt_nombrepiece || '',
    txt_objet: arrivees?.txt_objet || '',
    txt_nicad: arrivees?.txt_nicad || '',
    txt_situation: arrivees?.txt_situation || '',
    txt_prenom: arrivees?.txt_prenom || '',
    txt_nom: arrivees?.txt_nom || '',
    txt_surface: arrivees?.txt_surface || '',
    txt_numLot: arrivees?.txt_numLot || '',
    txt_expediteur: arrivees?.txt_expediteur || '',
    txt_agenttraiteur: arrivees?.txt_agenttraiteur || '',
    txt_observation: arrivees?.txt_observation || '',
    fichierPDF: arrivees?.fichierPDF || null, 
}); 
 
const show = ref(false);
const handleCategorieChange = () => {
    show.value = form.txt_categorie === "Convocation - Invitation";
};
watch(() => form.txt_categorie, (newValue) => {
    show.value = newValue === "Convocation - Invitation";
});
 
// récuperer les categories de courrier arrivee
const categories = {
    "1": "Demande SERVICES",
    "2": "Convocation - Invitation",
    "3": "Information",
    "4": "Réclamation - Signalement",
    "5": "Réquisition - Instruction"
};
const designationsParCategorie = {
    'Demande SERVICES': ['Morcellements', 'Réquisition d\'immatriculation',  'Demande Avis Technique',  'Demande de terrain / Echange', 'Prospection de terrain', 
        'Autorisation de construction', 'Autorisation de lotir', 'Demande d\états des lieux', 'Deamnde de délimitation/reconstruction', 
        'Réquisition DSCOS, Tribunal, Litiges','Demande de situation foncière', 'Demande de Cession définitive',
        'Demande de Cession définitive a Titre Gratuit', 'Demande de Régularisation', 'Demande d\'attestation du Cadastre', 
        'Réceptions de lotissements', 'Demande de CIC', 'Duplication de CIC', 'Demande de Titre foncier', 
        'Autirisationde morceler'
    ],
    'Convocation - Invitation': ['Réunion', 'Alerte', 'Visite de site', 
        'Rencontre', 'Randonnée - Marche', 'Session', 'Congré', 'Cérémonie', 'Inauguration', ' Congré',
        'Journée dédiée', 'Forum', 'Formation', 'Séminaire'
    ],
    'Information': ['Note de service - Curculaire', 'Rapport - PV Compte rendu', 
        'Arrêté - Décision - Délibération', 'Document administratif', 'Texte Juridique'
    ],
    'Réclamation - Signalement': ['Dénonciation', 'Plainte', 
        'Alerte'
    ],
    'Réquisition - Instruction': ['Réquisition matériel', 'Instruction à suivre', 'DE' 
    ], 
 
};
  
// récupérer les désignations et modifier la valeur de la désignation
const designations = ref([]);  

const chargerDesignations = (categorie) => {
    const libelleCategorie = categories[categorie];
    let liste = designationsParCategorie[libelleCategorie] || [];

    // Injecter la désignation existante si elle n’est pas dedans
    if (form.txt_designation && !liste.includes(form.txt_designation)) {
        liste = [form.txt_designation, ...liste];
        designations.value = liste;
    }
 
}; 
onMounted(() => {
    if (form.txt_categorie) {
        chargerDesignations(form.txt_categorie);
    }
}); 
watch(() => form.txt_categorie, (newCategorie, oldCategorie) => {
    chargerDesignations(newCategorie);

    if (newCategorie !== oldCategorie) {
        designations.value = designationsParCategorie[newCategorie] || [];
        form.txt_designation = '';
    }
});
  
// reupèration references courrier arrivée
watch(
    () => [form.txt_numcourier, form.dt_datecourier],
    ([newNum, newDate]) => {
        form.txt_reference = newNum + ' du ' + newDate;
    }
);
  
// 📁 Gestion du changement de fichier
function handleFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        form.fichierPDF = file;
        console.log("Fichier sélectionné:", file.name);
    } else {
        form.fichierPDF = null;
    }
}

//  suppression fichier PDF 
const deleteFile = (arrivee) => {
    if (!arrivee?.id) {
            toast.error('Aucun courrier sélectionné pour suppression ❌');
        return;
    }

    const confirmation = confirm('Voulez-vous vraiment supprimer ce fichier PDF ?');
    if (!confirmation) return;

    router.delete(`/arrivee/delete-pdf/${arrivee.id}`, {
        onSuccess: () => {
            arrivee.fichierPDF = null; // mettre à jour la vue
            toast.success('Fichier PDF supprimé avec succès ✅');
        },
        onError: (error) => {
            console.error('Erreur lors de la suppression du PDF :', error);
            toast.error(error?.message || 'Erreur lors de la suppression du fichier ❌');
        }
    });
}

  
async function submit() {
    try {
        console.log("📤 Envoi avec axios...");
        
        const formData = new FormData();
        
        // Ajouter tous les champs UNE SEULE FOIS
        formData.append('_method', 'PUT'); // Important pour Laravel
        formData.append('txt_bordereau', form.txt_bordereau);
        formData.append('txt_numdordre', form.txt_numdordre);
        formData.append('dt_datearrivee', form.dt_datearrivee);
        formData.append('txt_caractere', form.txt_caractere);
        formData.append('txt_numcourier', form.txt_numcourier);
        formData.append('dt_datecourier', form.dt_datecourier);
        formData.append('txt_reference', form.txt_reference);
        formData.append('txt_categorie', form.txt_categorie);
        formData.append('txt_designation', form.txt_designation);
        formData.append('dt_date', form.dt_date);
        formData.append('txt_heure', form.txt_heure);
        formData.append('txt_lieu', form.txt_lieu);
        formData.append('txt_nombrepiece', form.txt_nombrepiece);
        formData.append('txt_objet', form.txt_objet);
        formData.append('txt_nicad', form.txt_nicad);
        formData.append('txt_situation', form.txt_situation);
        formData.append('txt_prenom', form.txt_prenom)
        formData.append('txt_nom', form.txt_nom);
        formData.append('txt_surface', form.txt_surface);
        formData.append('txt_numLot', form.txt_numLot);
        formData.append('txt_expediteur', form.txt_expediteur);
        formData.append('txt_agenttraiteur', form.txt_agenttraiteur);
        formData.append('txt_observation', form.txt_observation);
        
        // Ajouter le fichier
        if (form.fichierPDF instanceof File) {
            formData.append('fichierPDF', form.fichierPDF);
        }

        // Debug : vérifiez ce qui est envoyé
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ', value);
        }

        const response = await axios.post(route('arrivee.update', arrivees.id), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });

        toast.success('Modification réussie!');
        Inertia.visit(route("instancearrivee.create"), { replace: true });
        
    } catch (error) {
        console.error('Erreur détaillée:', error.response?.data);
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach(err => {
                toast.error(err);
            });
        } else {
            toast.error('Erreur lors de la modification');
        }
    }
}
 
</script>

<template>
    <Head title="Courrier Arrivées" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-primary-txt">
                Modification : Courriers Arrivées
            </h2>

        </template>

        <div class="py-12">
            <div class="flex justify-center">
                <div class="w-full max-w-8xl rounded-xl">
                    <div class="bg-white shadow-md rounded-xl">
                        <!-- En-tête du formulaire -->
                        <div   class="p-4 border-b bg-primary rounded-t-xl">
                            <h1 class="text-lg text-white font-semibold">Formulaire de Modification des Courriers Arrivées</h1>
                        </div>
                        <!-- Corps du formulaire -->
                        <form @submit.prevent="submit" enctype="multipart/form-data">
                            <div class="p-6">
                                <!-- Section Parcelle -->
                                <h5 class="text-lg text-primary-txt font-bold">
                                    Modification du Courrier N° : {{ arrivees.txt_numdordre }}
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
                                            <div class="sm:col-span-2">
                                                <label 
                                                    for="txt_numdordre"
                                                    class="block text-sm/6 font-medium text-primary-txt">
                                                    N° Dordre Arrivée
                                                </label>
                                                <div class="mt-2">
                                                    <input
                                                        type="txt"
                                                        name="txt_numdordre" 
                                                        required
                                                        v-model="form.txt_numdordre"  
                                                        id="txt_numdordre"
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
                                                    for="dt_datearrivee"
                                                    class="block text-sm/6 font-medium text-primary-txt">
                                                    Date Reception
                                                </label>
                                                <div class="mt-2">
                                                    <input
                                                        type="date"
                                                        name="dt_datearrivee"
                                                        v-model="form.dt_datearrivee"  
                                                        required
                                                        id="dt_datearrivee"
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
                                                        autocomplete="off"
                                                        class="h-8  scrollbar-thin scrollbar-thumb-primary scrollbar-track-gray-300 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
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
                                                        required
                                                        id="dt_datecourier"
                                                        autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
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
                                                    for="txt_categorie"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Catégorie</label
                                                >
                                                <div class="mt-2">
                                                    <select
                                                        type="select"
                                                        name="txt_categorie"
                                                        v-model="form.txt_categorie"
                                                        @change="handleCategorieChange" 
                                                        required
                                                        id="txt_categorie"
                                                        autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                        
                                                    > 
                                                        <option selected disabled>Choisis Catégorie</option>
                                                        <option value="Demande SERVICES">Demande SERVICES</option>
                                                        <option value="Convocation - Invitation">Convocation - Invitation</option>
                                                        <option value="Information">Information</option>
                                                        <option value="Réclamation - Signalement">Réclamation - Signalement</option>
                                                        <option value="Réquisition - Instruction">Réquisition - Instruction</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_designation"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Designation</label
                                                >
                                                <div class="mt-1">
                                                    <select
                                                        type="select"
                                                        name="txt_designation"
                                                        v-model="form.txt_designation" 
                                                        required
                                                        id="txt_designation"
                                                        autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    >
                                                        <option selected disabled>Choisis Designation</option> 
                                                        <option v-for="(designation, index) in designations" :key="index" :value="designation">
                                                            {{ designation }} 
                                                        </option> 
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div  v-if="!show"  class="sm:col-span-1">
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
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div  v-if="!show" class="sm:col-span-1">
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
                                                        autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div  v-if="!show" class="sm:col-span-1">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_lieu"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Lieu Evenement</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="txt"
                                                        name="txt_lieu"
                                                        v-model="form.txt_lieu" 
                                                        id="txt_lieu"
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
                                                    for="txt_numLot"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >N° Lot</label
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
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_surface"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Superficie</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="number"
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
                                        <div class="sm:col-span-2">
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
                                                    for="txt_nombrepiece"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Nbre Pièce</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="txt"
                                                        name="txt_nombrepiece"
                                                        v-model="form.txt_nombrepiece"
                                                        required
                                                        id="txt_nombrepiece"
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
                                                    for="txt_objet"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Objet</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="txt"
                                                        name="txt_objet"
                                                        v-model="form.txt_objet"
                                                        required
                                                        id="txt_objet"
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
                                                        type="txt"
                                                        name="txt_expediteur"
                                                        v-model="form.txt_expediteur"
                                                        required
                                                        id="txt_expediteur"
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
                                                    >Agent Traiteur</label
                                                >
                                                <div class="mt-2">
                                                    <select
                                                        type="select"
                                                        name="txt_agenttraiteur"
                                                        v-model="form.txt_agenttraiteur"
                                                        required
                                                        id="txt_agenttraiteur"
                                                        autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    > 
                                                        <option value='Abdoul Karim KANE'>Abdoul Karim KANE</option>,
                                                        <option value="Lamine DIAGNE">Lamine DIAGNE</option>,
                                                        <option value="Cheikh Tidiane WADE">Cheikh Tidiane WADE</option>
                                                        <option value="Sokhna Arame BA">Sokhna Arame BA</option>
                                                        <option value="Moustapha MBENGUE">Moustapha MBENGUE</option>
                                                        <option value="Makhtar CISSE">Makhtar CISSE</option>
                                                        <option value="Daouda LEYE">Daouda LEYE</option>
                                                        <option value="Cheikh DIOP">Cheikh DIOP</option>
                                                        <option value="Tening">Tening</option>,
                                                        <option value="Suzanne">Suzanne</option>,
                                                        <option value="Khady THIADOUM">Khady THIADOUM</option>,
                                                        <option value="Rokhaya S. SIDIBE">Rokhaya S. SIDIBE</option>,
                                                        <option value="Aly FAYE">Aly FAYE</option>,
                                                        <option value="Baidy BA">Baidy BA</option>,
                                                        <option value="Elhadj Senghane THIAM">Elhadj Senghane THIAM</option>,
                                                        <option value="Mamadou DIOUF">Mamadou DIOUF</option>,
                                                        <option value="Matalibé DIALLO">Matalibé DIALLO</option>,
                                                        <option value="Mouhamed SOW">Mouhamed SOW</option>,
                                                        <option value="Oumar NDIAYE">Oumar NDIAYE</option>,
                                                        <option value="Mme TOURE">Mme TOURE</option>,
                                                        <option value="Dorine DIOP">Dorine DIOP</option>,
                                                        <option value="Pape DIALLO">Pape DIALLO</option>,
                                                        <option value="Alioune NIANG">Alioune NIANG</option>,
                                                        <option value="Boly DIOP">Boly DIOP</option>
                                                    </select>
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
                                                    <select
                                                        type="txt"
                                                        name="txt_caractere" 
                                                        readonly
                                                        v-model="form.txt_caractere"  
                                                        id="txt_caractere"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6" 
                                                    >
                                                        <option selected disabled>Choisis Caractères</option>
                                                        <option value="Confidentiel">Confidentiel</option>
                                                        <option value="Urgent">Urgent</option>
                                                        <option value="Secret">Secret</option>
                                                    </select>
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
                                                        type="txt"
                                                        name="txt_observation"
                                                        v-model="form.txt_observation"  
                                                        id="txt_observation"
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
                                                    for="fichier_PDF"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Importer le Fichier PDF</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="file"
                                                        name="fichierPDF"
                                                        accept="application/pdf"
                                                        @change="handleFileChange"
                                                        id="fichierPDF"
                                                        autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-gray-400 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    />
                                                </div>  
                                                <!-- Pour ajouter un bouton de suppression de fichier -->
                                                <div v-if="arrivees?.fichierPDF" class="mt-2 flex items-center space-x-2">
                                                    <a :href="`/storage/${arrivees.fichierPDF}`" target="_blank" 
                                                    class="text-blue-600 underline text-sm">
                                                        📄 Voir le PDF
                                                    </a>
                                                    <button @click="deleteFile(arrivees)" 
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

