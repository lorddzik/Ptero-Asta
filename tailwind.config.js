const colors = require('tailwindcss/colors');

const gray = {
    50: 'hsl(216, 33%, 97%)',
    100: 'hsl(214, 15%, 91%)',
    200: 'hsl(210, 16%, 82%)',
    300: 'hsl(211, 13%, 65%)',
    400: 'hsl(211, 10%, 53%)',
    500: 'hsl(211, 12%, 43%)',
    600: 'hsl(209, 14%, 37%)',
    700: 'hsl(209, 18%, 30%)',
    800: 'hsl(209, 20%, 25%)',
    900: 'hsl(210, 24%, 16%)',
};

module.exports = {
    content: [
        './resources/scripts/**/*.{js,ts,tsx}',
    ],
    theme: {
        extend: {
            fontFamily: {
                header: ['"IBM Plex Sans"', '"Roboto"', 'system-ui', 'sans-serif'],
            },
            colors: {
                black: '#000000',
                'neo-bg': '#0D1117',
                'neo-surface': '#161B22',
                'neo-surface-light': '#21262D',
                'asta-cyan': '#00D2FF',
                'asta-green': '#10B981',
                'asta-red': '#EF4444',
                'asta-yellow': '#FACC15',
                'asta-purple': '#A855F7',
                // "primary" and "neutral" are deprecated, prefer the use of "blue" and "gray"
                // in new code.
                primary: colors.blue,
                gray: gray,
                neutral: gray,
                cyan: colors.cyan,
            },
            boxShadow: {
                neo: '4px 4px 0px #000000',
                'neo-sm': '2px 2px 0px #000000',
                'neo-lg': '6px 6px 0px #000000',
                'neo-cyan': '4px 4px 0px #00D2FF',
                'neo-red': '4px 4px 0px #EF4444',
                'neo-green': '4px 4px 0px #10B981',
            },
            borderWidth: {
                neo: '2.5px',
            },
            fontSize: {
                '2xs': '0.625rem',
            },
            transitionDuration: {
                250: '250ms',
            },
            borderColor: theme => ({
                default: '#000000',
            }),
        },
    },
    plugins: [
        require('@tailwindcss/line-clamp'),
        require('@tailwindcss/forms')({
            strategy: 'class',
        }),
    ]
};
