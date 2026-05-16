/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        ink: '#172026',
        muted: '#66727f',
        line: '#d7dde3',
        panel: '#f7f9fb',
        brand: '#0f766e',
        accent: '#b45309',
        danger: '#b91c1c',
      },
      boxShadow: {
        soft: '0 10px 30px rgba(23, 32, 38, 0.08)',
      },
    },
  },
  plugins: [],
};
