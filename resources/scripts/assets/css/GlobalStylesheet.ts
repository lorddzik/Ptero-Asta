import tw from 'twin.macro';
import { createGlobalStyle } from 'styled-components/macro';
// @ts-expect-error untyped font file
import font from '@fontsource-variable/ibm-plex-sans/files/ibm-plex-sans-latin-wght-normal.woff2';

export default createGlobalStyle`
    @font-face {
        font-family: 'IBM Plex Sans';
        font-style: normal;
        font-display: swap;
        font-weight: 100 700;
        src: url(${font}) format('woff2-variations');
        unicode-range: U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;
    }

    body {
        background-color: #0D1117 !important;
        color: #FFFFFF !important;
        font-family: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        letter-spacing: 0.015em;
    }

    ::selection {
        background: #00D2FF !important;
        color: #000000 !important;
    }

    h1, h2, h3, h4, h5, h6 {
        ${tw`font-bold tracking-tight font-header text-white`};
    }

    p {
        ${tw`text-neutral-200 leading-snug font-sans`};
    }

    form {
        ${tw`m-0`};
    }

    textarea, select, input, button, button:focus, button:focus-visible {
        ${tw`outline-none`};
    }

    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield !important;
    }

    /* Neo-Brutalist Sharp Scrollbars */
    ::-webkit-scrollbar {
        background: #0D1117;
        width: 12px;
        height: 12px;
    }

    ::-webkit-scrollbar-thumb {
        background: #21262D;
        border: 2px solid #000000;
        border-radius: 0px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #00D2FF;
    }

    ::-webkit-scrollbar-corner {
        background: #0D1117;
    }
`;
