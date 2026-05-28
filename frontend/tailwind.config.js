/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{js,jsx,ts,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          light: '#F0F4F8',
          DEFAULT: '#1E40AF', // Bleu Premium Professionnel
          dark: '#1E3A8A',
          accent: '#3B82F6',
          hover: '#1D4ED8',
        },
        surface: {
          canvas: '#F8FAFC',  // Gris très léger pour le background général
          card: '#FFFFFF',    // Blanc pur pour les cartes
          glass: 'rgba(255, 255, 255, 0.70)',
        }
      },
      boxShadow: {
        'premium': '0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 12px -1px rgba(0, 0, 0, 0.03)',
        'glass': '0 8px 32px 0 rgba(31, 38, 135, 0.04)',
      },
      backdropBlur: {
        'xs': '2px',
      }
    },
  },
  plugins: [],
}