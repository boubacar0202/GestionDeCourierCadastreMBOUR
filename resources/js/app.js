import "../css/app.css";
import "./bootstrap";

import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import * as MazUI from "maz-ui";
import { installToaster } from 'maz-ui'; 
// import Toast from 'vue-toastification'
 
// const appName = import.meta.env.VITE_APP_NAME || "Bou";

import "maz-ui/styles";

const toasterOptions = {
    position: "bottom-right",
    timeout: 5000,
    persistent: false,
};

// // app.js
// const storedTheme = localStorage.getItem('theme');
// if (storedTheme === 'dark') {
//   document.documentElement.classList.add('dark');
// } else {
//   document.documentElement.classList.remove('dark');
// }


createInertiaApp({
    // title: (title) => `${title} - ${appName}`,
    title: (title) => `${title}`,
    resolve: async (name) => {
        return resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        );
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
    
        // Gestion globale des erreurs
        app.config.errorHandler = (err, vm, info) => {
            console.error('Erreur Vue attrapée:', err, vm, info)
        }

        // Configuration de MazUI avec la couleur personnalisée
        app.use(MazUI, {
            colors: {
                primary: '#FE5835',
                second: '#613104',
            },
        });
    
        // Ajout des plugins
        app.use(plugin)
            .use(ZiggyVue)
            .use(installToaster, toasterOptions)
            .mount(el);

        // app.use(Toast)
    },    
      
    progress: {
        color: "#FE5835",
    },
});
