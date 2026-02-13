/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./src/**/*.php",
        "./public/**/*.php",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
        },
    },
    plugins: [],
}
