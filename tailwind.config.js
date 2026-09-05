import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './node_modules/flowbite/**/*.js'
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                playfair: ['Playfair Display', 'serif', 'system-ui'],
            },
            colors: {
                primary: colors.sky, // Xanh dương nhạt
                brand: {
                    DEFAULT: '#FD817A',
                    hover: '#e86e67',
                    light: '#f4f7f6',
                }
            }
        },
    },

    plugins: [
        forms,
        require('flowbite/plugin')
    ],
};
