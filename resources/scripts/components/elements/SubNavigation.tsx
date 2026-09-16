import styled from 'styled-components/macro';
import tw, { theme } from 'twin.macro';

const SubNavigation = styled.div`
    ${tw`w-full border-b-[2.5px] border-black overflow-x-auto`};
    background-color: var(--neo-surface);

    & > div {
        ${tw`flex items-center text-sm mx-auto px-4 py-2 gap-2`};
        max-width: 1200px;

        & > a,
        & > div {
            ${tw`inline-block py-1.5 px-3 no-underline whitespace-nowrap font-bold transition-all duration-100 rounded`};
            color: var(--neo-text-muted);
            border: 2px solid transparent;

            &:hover {
                color: var(--neo-text);
                background-color: var(--neo-surface-light);
                border-color: #000000;
            }

            &:active,
            &.active {
                color: #000000 !important;
                background-color: #00D2FF !important;
                border: 2px solid #000000 !important;
                box-shadow: 2px 2px 0px #000000 !important;
                font-weight: 800 !important;
            }
        }
    }
`;

export default SubNavigation;
