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
import { toast } from "@/Components/AppToast";  

defineOptions({ layout: DefaultLayout });
const today = new Date().toISOString().split('T')[0];
  
// Formulaire 
const form = useForm({
    //  visite edit    
    dt_visite: visit?.dt_visite || '',
    txt_traitementVisite: visit?.txt_traitementVisite || '',
    txt_objetVisite: viste?.txt_objetVisite || '',
    txt_telVisite: visite?.txt_telVisite || '',
    txt_prenomVisite: visit?.txt_prenomVisite || '',
    txt_numdordreVisite: visit?.txt_numdordreVisite || '',
});
   
async function submit() {
    try {
        console.log("📤 Envoi du formulaire départ...");

        const formData = new FormData();
        formData.append('_method', 'PUT'); // Laravel update 
        formData.append('dt_visite', form.dt_visite);
        formData.append('txt_traitementVisite', form.txt_traitementVisite);
        formData.append('txt_objetVisite', form.txt_objetVisite);
        formData.append('txt_telVisite', form.txt_telVisite); 
        formData.append('txt_prenomVisite', form.txt_prenomVisite); 
        formData.append('txt_numdordreVisite', form.txt_numdordreVisite); 

        // Debug
        for (let [key, value] of formData.entries()) {
            console.log(key + ':', value);
        }

        await axios.post(route('visite.update', departs.id), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        toast.success('Modification réussie !');
        Inertia.visit(route('instancevisite.create'), { replace: true });

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
    <Head title="Courrier Arrivées" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-primary-txt">
                Enregistrement des visites
            </h2>

        </template>

        <div class="py-12">
            <div class="flex justify-center">
                <div class="w-full border border-primary-only max-w-8xl rounded-lg">
                    <div class="bg-white shadow-md rounded-lg">
                        <!-- En-tête du formulaire -->
                        <div   class="p-4 border-b bg-primary rounded-t-lg">
                            <h1 class="text-lg text-white font-semibold">Formulaire d'enregistrement des visiteurs</h1>
                        </div>
                        <!-- Corps du formulaire -->
                        <form @submit.prevent="submitForm">
                            <div class="p-6">
                                <!-- Section Parcelle -->
                                <div class="mb-6">
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4"
                                    > 
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="dt_visite"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Date visite</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="date"
                                                        name="dt_visite"
                                                        v-model="form.dt_visite"
                                                        :max="today"
                                                        required
                                                        id="dt_visite"
                                                        autocomplete="off"
                                                        class="h-8 block w-full rounded-md bg-white px-3 py-1.5 text-base text-primary-txt 
                                                            outline outline-1 -outline-offset-1 outline-primary-only placeholder:text-primary-dark 
                                                            focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    > 
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-2">
                                                <label 
                                                    for="txt_numdordreVisite"
                                                    class="block text-sm/6 font-medium text-primary-txt">
                                                    N° Arrivée du Visiteur
                                                </label>
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_numdordreVisite"   
                                                        required
                                                        v-model="form.txt_numdordreVisite"  
                                                        id="txt_numdordreVisite"
                                                        class="h-8 block w-full bg-gray-100 rounded-md bg-white px-3 py-1.5
                                                            text-base text-primary outline outline-1 -outline-offset-1 outline-primary-only 
                                                            placeholder:text-primary-dark focus:outline focus:outline-2 focus:-outline-2 
                                                            focus:outline-primary sm:text-sm/6" 
                                                    />
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_prenomVisite"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Prénom & Nom</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_prenomVisite"
                                                        v-model="form.txt_prenomVisite"
                                                        required
                                                        id="txt_prenomVisite"
                                                        autocomplete="off"
                                                        class="h-8  scrollbar-thin scrollbar-thumb-primary block w-full rounded-md bg-white 
                                                            px-3 py-1.5 text-base text-primary-txt outline outline-1 -outline-offset-1 outline-primary-only 
                                                            placeholder:text-primary-dark focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    > 
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_telVisite"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >N° Telephone</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_telVisite"
                                                        v-model="form.txt_telVisite"
                                                        required
                                                        id="txt_telVisite"
                                                        autocomplete="off"
                                                        class="h-8  scrollbar-thin scrollbar-thumb-primary block w-full rounded-md bg-white 
                                                            px-3 py-1.5 text-base text-primary-txt outline outline-1 -outline-offset-1 outline-primary-only 
                                                            placeholder:text-primary-dark focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    > 
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_objetVisite"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Objet</label
                                                >
                                                <div class="mt-2">
                                                    <input
                                                        type="text"
                                                        name="txt_objetVisite"
                                                        v-model="form.txt_objetVisite"
                                                        required
                                                        id="txt_objetVisite"
                                                        autocomplete="address-level2"
                                                        class="h-8  scrollbar-thin scrollbar-thumb-primary block w-full rounded-md bg-white 
                                                            px-3 py-1.5 text-base text-primary-txt outline outline-1 -outline-offset-1 outline-primary-only 
                                                            placeholder:text-primary-dark focus:outline focus:outline-2 focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    > 
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <div class="sm:col-span-1">
                                                <label
                                                    for="txt_traitementVisite"
                                                    class="block text-sm/6 font-medium text-primary-txt"
                                                    >Traitement</label
                                                >
                                                <div class="mt-2">
                                                    <select
                                                        name="txt_traitementVisite"
                                                        v-model="form.txt_traitementVisite"
                                                        required
                                                        id="txt_traitementVisite"
                                                        class="h-8 scrollbar-thin scrollbar-thumb-primary block w-full rounded-md bg-white 
                                                            px-3 py-1.5 text-base text-primary-txt outline outline-1 -outline-offset-1 
                                                            outline-primary-only placeholder:text-primary-dark focus:outline focus:outline-2 
                                                            focus:-outline-2 focus:outline-primary sm:text-sm/6"
                                                    >
                                                        <option selected desabled value="">Choisis ici</option>
                                                        <option value="Traiter">Traiter</option>
                                                        <option value="En Attente">En Attente</option>
                                                        <option value="Retourner">Retourner</option>
                                                    </select>
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


