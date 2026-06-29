/** @type {import('tailwindcss').Config} */
module.exports = {
    // Arquivos escaneados para gerar apenas as classes realmente usadas.
    content: [
        './app/Views/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                // Identidade visual Madalozzo Seguros — azul corporativo + branco
                brand: {
                    50:  '#EAF2FB',
                    100: '#D4E4F7',
                    200: '#A9C9EF',
                    300: '#7EAEE7',
                    400: '#5393DF',
                    500: '#2878D7',
                    600: '#0000FF', // primária
                    700: '#0852A0',
                    800: '#073F7C',
                    900: '#0B2A4A', // navy profundo
                },
                ink: '#0B2A4A',
            },
            fontFamily: {
                sans: ['Inter'],
            },
        },
    },
    plugins: [],
};
