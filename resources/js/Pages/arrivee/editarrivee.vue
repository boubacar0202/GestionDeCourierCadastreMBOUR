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
import { router } from '@inertiajs/vue3';
 
 
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
                <div class="w-full max-w-8xl px-2 py-2">
                    <div class="bg-white shadow-md rounded-xl">
                        <!-- En-tête du formulaire -->
                        <div   class="p-4 border-b bg-primary rounded-t-xl">
                            <h2 class="text-2xl leading-tight text-white font-bold">Modification du courrier arrivée</h2>
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
                                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="N° BE"
                                            name="txt_bordereau"
                                            v-model="form.txt_bordereau"
                                            autocomplete="on"
                                        />
                                    </div><br>
                                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                        <BaseInput
                                            label="N° Dordre Arrivée"
                                            name="txt_numdordre"
                                            v-model="form.txt_numdordre"
                                            autocomplete="off"
                                            required
                                        />
                                        <BaseInput
                                            label="Date Reception"
                                            name="dt_datearrivee"
                                            type="date"
                                            v-model="form.dt_datearrivee"
                                            required
                                        />
                                        <BaseInput
                                            label="N° Courrier"
                                            name="txt_numcourier"
                                            v-model="form.txt_numcourier"
                                            autocomplete="off"
                                            required
                                        />
                                        <BaseInput
                                            label="Date Courrier"
                                            name="dt_datecourier"
                                            type="date"
                                            v-model="form.dt_datecourier"
                                            autocomplete="off"
                                            required
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Réf.Courrier Arrivée"
                                            name="txt_reference"
                                            v-model="form.txt_reference"
                                            autocomplete="off"
                                            required
                                        />
                                        <BaseSelect
                                            class="sm:col-span-2"
                                            label="Catégorie"
                                            name="txt_categorie"
                                            v-model="form.txt_categorie"
                                            placeholder="Choisis Catégorie"
                                            required
                                            @change="handleCategorieChange"
                                            :options="[
                                                { value: 'Demande SERVICES', label: 'Demande SERVICES' },
                                                { value: 'Convocation - Invitation', label: 'Convocation - Invitation' },
                                                { value: 'Information', label: 'Information' },
                                                { value: 'Réclamation - Signalement', label: 'Réclamation - Signalement' },
                                                { value: 'Réquisition - Instruction', label: 'Réquisition - Instruction' },
                                            ]"
                                        />
                                        <BaseSelect
                                            class="sm:col-span-2"
                                            label="Designation"
                                            name="txt_designation"
                                            v-model="form.txt_designation"
                                            :options="designations"
                                            placeholder="Choisis Designation"
                                            required
                                        />
                                        <BaseInput
                                            v-if="!show"
                                            label="Date Evenement"
                                            name="dt_date"
                                            type="date"
                                            v-model="form.dt_date"
                                            autocomplete="off"
                                        />
                                        <BaseInput
                                            v-if="!show"
                                            label="Heure Evenement"
                                            name="txt_heure"
                                            type="time"
                                            v-model="form.txt_heure"
                                            autocomplete="off"
                                        />
                                        <BaseInput
                                            v-if="!show"
                                            label="Lieu Evenement"
                                            name="txt_lieu"
                                            v-model="form.txt_lieu"
                                            autocomplete="address-level2"
                                        />
                                        <BaseInput
                                            label="N° Lot"
                                            name="txt_numLot"
                                            v-model="form.txt_numLot"
                                            autocomplete="off"
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Superficie"
                                            name="txt_surface"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            v-model="form.txt_surface"
                                            autocomplete="off"
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Prénom"
                                            name="txt_prenom"
                                            v-model="form.txt_prenom"
                                            autocomplete="off"
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Nom"
                                            name="txt_nom"
                                            v-model="form.txt_nom"
                                            autocomplete="off"
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Situation"
                                            name="txt_situation"
                                            v-model="form.txt_situation"
                                            autocomplete="off"
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="NICAD"
                                            name="txt_nicad"
                                            v-model="form.txt_nicad"
                                            autocomplete="off"
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Nbre Pièce"
                                            name="txt_nombrepiece"
                                            v-model="form.txt_nombrepiece"
                                            autocomplete="off"
                                            required
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Objet"
                                            name="txt_objet"
                                            v-model="form.txt_objet"
                                            autocomplete="off"
                                            required
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Expéditeur"
                                            name="txt_expediteur"
                                            v-model="form.txt_expediteur"
                                            autocomplete="off"
                                            required
                                        />
                                        <BaseSelect
                                            class="sm:col-span-2"
                                            label="Agent Traiteur"
                                            name="txt_agenttraiteur"
                                            v-model="form.txt_agenttraiteur"
                                            required
                                            :options="[
                                                'Abdoul Karim KANE', 'Lamine DIAGNE', 'Cheikh Tidiane WADE', 'Sokhna Arame BA',
                                                'Moustapha MBENGUE', 'Makhtar CISSE', 'Daouda LEYE', 'Cheikh DIOP', 'Tening',
                                                'Suzanne', 'Khady THIADOUM', 'Rokhaya S. SIDIBE', 'Aly FAYE', 'Baidy BA',
                                                'Elhadj Senghane THIAM', 'Mamadou DIOUF', 'Matalibé DIALLO', 'Mouhamed SOW',
                                                'Oumar NDIAYE', 'Mme TOURE', 'Dorine DIOP', 'Pape DIALLO', 'Alioune NIANG', 'Boly DIOP'
                                            ]"
                                        />
                                        <BaseSelect
                                            class="sm:col-span-2"
                                            label="Caractères"
                                            name="txt_caractere"
                                            v-model="form.txt_caractere"
                                            placeholder="Choisis Caractères"
                                            :options="[
                                                { value: 'Confidentiel', label: 'Confidentiel' },
                                                { value: 'Urgent', label: 'Urgent' },
                                                { value: 'Secret', label: 'Secret' },
                                            ]"
                                        />
                                        <BaseInput
                                            class="sm:col-span-4"
                                            label="Observation"
                                            name="txt_observation"
                                            v-model="form.txt_observation"
                                            autocomplete="off"
                                        />
                                        <div class="sm:col-span-2">
                                            <BaseInput
                                                label="Importer le Fichier PDF"
                                                name="fichierPDF"
                                                type="file"
                                                accept="application/pdf"
                                                autocomplete="off"
                                                @change="handleFileChange"
                                            />
                                            <div v-if="arrivees?.fichierPDF" class="mt-2 flex items-center space-x-2">
                                                <a :href="`/fichiers/${arrivees.fichierPDF}`" target="_blank" class="text-sm text-blue-600 underline">
                                                    Voir le PDF
                                                </a>
                                                <button type="button" @click="deleteFile(arrivees)" class="text-sm text-red-600 hover:text-red-800">
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

