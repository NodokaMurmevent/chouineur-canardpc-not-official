module.exports = {
    darkMode: 'class',
    content: [
        "./templates/**/*.html.twig",
        "./assets/**/*.js",
        "./src/Form/**/*.php"
    ],
    theme: {       
        extend: {
            colors: {  
                primary: "#ffa500",
                secondary: "#00afbc",
            },
        },
    },
    plugins: [
        // require('@tailwindcss/forms'),
        // require('@tailwindcss/forms'),
        require('@tailwindcss/aspect-ratio'),
        require('@tailwindcss/typography')
    ],
}