<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import MazBtn from "maz-ui/components/MazBtn";
import BaseInput from "@/Components/BaseInput.vue";
import BaseSelect from "@/Components/BaseSelect.vue";
import axios from "axios";
import { toast } from "@/Components/AppToast";


const props = defineProps({
    visite: {
        type: Object,
        required: true,
    },
});

const today = new Date().toISOString().split("T")[0];

const form = useForm({
    dt_visite: props.visite?.dt_visite ? String(props.visite.dt_visite).slice(0, 10) : "",
    txt_traitementVisite: props.visite?.txt_traitementVisite || "",
    txt_objetVisite: props.visite?.txt_objetVisite || "",
    txt_telVisite: props.visite?.txt_telVisite || "",
    txt_prenomVisite: props.visite?.txt_prenomVisite || "",
    txt_numdordreVisite: props.visite?.txt_numdordreVisite || "",
});

async function submitForm() {
    try {
        const formData = new FormData();
        formData.append("_method", "PUT");
        formData.append("dt_visite", form.dt_visite);
        formData.append("txt_traitementVisite", form.txt_traitementVisite);
        formData.append("txt_objetVisite", form.txt_objetVisite);
        formData.append("txt_telVisite", form.txt_telVisite);
        formData.append("txt_prenomVisite", form.txt_prenomVisite);
        formData.append("txt_numdordreVisite", form.txt_numdordreVisite);

        await axios.post(route("visite.update", props.visite.id), formData);

        toast.success("Modification réussie !");
        router.visit(route("instancevisite.create"));
    } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors) {
            Object.values(errors).flat().forEach((err) => toast.error(err));
        } else {
            toast.error("Erreur lors de la modification");
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
                <div class="w-full max-w-8xl px-2 py-2">
                    <div class="bg-white shadow-md rounded-lg">
                        <!-- En-tête du formulaire -->
                        <div   class="p-4 border-b bg-primary rounded-t-lg">
                            <h2 class="text-2xl leading-tight text-white font-bold">Modification du visiteur</h2>
                        </div>
                        <!-- Corps du formulaire -->
                        <form @submit.prevent="submitForm">
                            <div class="p-6">
                                <!-- Section Parcelle -->
                                <div class="mb-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Date visite"
                                            name="dt_visite"
                                            type="date"
                                            v-model="form.dt_visite"
                                            :max="today"
                                            autocomplete="off"
                                            required
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="N° Arrivée du Visiteur"
                                            name="txt_numdordreVisite"
                                            v-model="form.txt_numdordreVisite"
                                            required
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Prénom & Nom"
                                            name="txt_prenomVisite"
                                            v-model="form.txt_prenomVisite"
                                            autocomplete="off"
                                            required
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="N° Telephone"
                                            name="txt_telVisite"
                                            v-model="form.txt_telVisite"
                                            autocomplete="off"
                                            required
                                        />
                                        <BaseInput
                                            class="sm:col-span-2"
                                            label="Objet"
                                            name="txt_objetVisite"
                                            v-model="form.txt_objetVisite"
                                            autocomplete="address-level2"
                                            required
                                        />
                                        <BaseSelect
                                            class="sm:col-span-2"
                                            label="Traitement"
                                            name="txt_traitementVisite"
                                            v-model="form.txt_traitementVisite"
                                            placeholder="Choisis ici"
                                            required
                                            :options="[
                                                { value: 'Traiter', label: 'Traiter' },
                                                { value: 'En Attente', label: 'En Attente' },
                                                { value: 'Retourner', label: 'Retourner' },
                                            ]"
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


