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
        min-height: 100vh;
        position: relative;
    }

    html.theme-light body, body.theme-light {
        background-color: #F0F4F8 !important;
        color: #0B0F17 !important;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: -999;
        pointer-events: none;
        background-image: 
            radial-gradient(circle at 50% 45%, rgba(13, 17, 23, 0.78) 0%, rgba(13, 17, 23, 0.94) 85%),
            url('/assets/astabrand.png');
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
    }

    html.theme-light body::before, body.theme-light::before {
        background-image: 
            radial-gradient(circle at 50% 45%, rgba(240, 244, 248, 0.88) 0%, rgba(240, 244, 248, 0.97) 85%),
            url('/assets/astabrand.png') !important;
    }

    ::selection {
        background: #00D2FF !important;
        color: #000000 !important;
    }

    h1, h2, h3, h4, h5, h6 {
        ${tw`font-bold tracking-tight font-header`};
        color: var(--neo-text);
    }

    p {
        ${tw`leading-snug font-sans`};
        color: var(--neo-text-muted);
    }

    html.theme-light h1,
    html.theme-light h2,
    html.theme-light h3,
    html.theme-light h4,
    html.theme-light h5,
    html.theme-light h6,
    html.theme-light strong,
    html.theme-light b {
        color: #0B0F17 !important;
    }

    html.theme-light p,
    html.theme-light label,
    html.theme-light td,
    html.theme-light th,
    html.theme-light li,
    html.theme-light dd,
    html.theme-light dt {
        color: #1E293B !important;
    }

    html.theme-light small,
    html.theme-light .input-help {
        color: #475569 !important;
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

    html.theme-light ::-webkit-scrollbar {
        background: #F0F4F8;
    }

    html.theme-light ::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border: 2px solid #000000;
    }

    html.theme-light ::-webkit-scrollbar-thumb:hover {
        background: #00D2FF;
    }

    html.theme-light ::-webkit-scrollbar-corner {
        background: #F0F4F8;
    }
`;
