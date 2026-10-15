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
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
                serif: ['"Source Serif 4"', ...defaultTheme.fontFamily.serif],
            },
            maxWidth: {
                '8xl': '88rem',
            },
            colors: {
                primary: '#7A3E12',
                "primary-dark": "#A35B24",
                "primary-txt": "#2C1810",
                "primary-only": "#E4D0B8",
                "primary-menu": "#8C4E1A",
                "primary-form": "#F8EFE4",
                "primary-footer": "#F3E4D2",
                "primary-layout": "#F7F1EA",
            },
            scrollbar: ['rounded'],
        },
    },

    plugins: [
        scrollbar,
    ],
      
      
};




