import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import scrollbar from 'tailwind-scrollbar';


/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{vue,js,ts,jsx,tsx}',
    ],

     theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            maxWidth: {
                '8xl': '88rem',
            },
            colors: {
                primary: '#613104',
                "primary-dark": "#A35200",
                "primary-txt": "#643407",
                "primary-only": "#E6C5A2",
                "primary-menu": "#C7812E",
                "primary-form": "#ffe5c5",
                "primary-footer": "#fff6ee",
                "primary-layout": "#faefe6",
            },
            scrollbar: ['rounded'],
        },
    },

    plugins: [
        scrollbar,
    ],
      
      
};




