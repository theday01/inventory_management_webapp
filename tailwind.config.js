/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./*.{html,js}"],
  theme: {
    extend: {
      colors: {
        dark: {
          DEFAULT: '#0E1116',
          surface: '#1F2937', // Slightly lighter for cards
          glass: 'rgba(14, 17, 22, 0.7)',
        },
        primary: {
          DEFAULT: '#3B82F6', // Electric Blue
          hover: '#2563EB',
        },
        accent: {
          DEFAULT: '#84CC16', // Lime Green
        }
      },
      fontFamily: {
        sans: ['Tajawal', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
