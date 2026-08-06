/** @type {import('tailwindcss').Config} */
module.exports = {
    // dark mode driven by data-theme attribute on <html>
    darkMode: ['attribute', '[data-theme="dark"]'],

    corePlugins: {
        preflight: false, // Bootstrap handles base resets for legacy pages
    },

    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/View/Components/**/*.php',
    ],

    safelist: [
        // Gradient direction
        'bg-gradient-to-r',
        'bg-gradient-to-br',
        'bg-gradient-to-l',
        'bg-gradient-to-b',

        // Gradient from/to colors — all shades used in buttons and UI components
        { pattern: /^from-(indigo|blue|purple|sky|emerald|teal|violet|slate|brand)-(50|100|400|500|600|700|800|900)$/, variants: ['hover', 'focus'] },
        { pattern: /^to-(indigo|blue|purple|sky|emerald|teal|violet|slate|brand)-(50|100|400|500|600|700|800|900)$/, variants: ['hover', 'focus'] },

        // Background colors used on buttons
        { pattern: /^bg-(indigo|blue|purple|sky|emerald|teal|violet|slate|red|amber|orange)-(50|100|400|500|600|700|800|900)$/, variants: ['hover', 'focus'] },

        // Text colors on buttons
        { pattern: /^text-(indigo|blue|purple|sky|emerald|teal|violet|slate|red|amber|orange|white)-(50|100|400|500|600|700|800|900)$/ },

        // Shadow colors with opacity modifier (e.g. shadow-indigo-500/25)
        'shadow-indigo-500/25',
        'shadow-indigo-500/20',
        'shadow-indigo-600/20',
        'shadow-blue-500/20',
        'shadow-blue-600/20',
        'shadow-emerald-500/20',
        'shadow-emerald-600/20',
        'shadow-purple-500/25',
        'shadow-sky-500/20',

        // Border radius
        'rounded-xl',
        'rounded-2xl',
        'rounded-3xl',

        // Common button spacing
        'px-4', 'px-5', 'px-6', 'px-8',
        'py-2', 'py-2.5', 'py-3',

        // Font weights
        'font-semibold', 'font-bold',

        // Transitions
        'transition', 'transition-all',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
            },
            colors: {
                brand: {
                    50:  '#eef2ff',
                    100: '#e0e7ff',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    900: '#1e1b4b',
                },
            },
            boxShadow: {
                'soft':     '0 4px 20px -2px rgba(0,0,0,.06)',
                'floating': '0 10px 40px -5px rgba(0,0,0,.12)',
                'card':     '0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04)',
            },
            borderRadius: {
                'xl':  '0.75rem',
                '2xl': '1rem',
                '3xl': '1.25rem',
            },
        },
    },

    plugins: [],
};
