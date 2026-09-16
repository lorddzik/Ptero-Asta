import styled from 'styled-components/macro';
import tw, { theme } from 'twin.macro';

const SubNavigation = styled.div`
    ${tw`w-full bg-[#161B22] border-b-[2.5px] border-black overflow-x-auto`};

    & > div {
        ${tw`flex items-center text-sm mx-auto px-4 py-2 gap-2`};
        max-width: 1200px;

        & > a,
        & > div {
            ${tw`inline-block py-1.5 px-3 text-neutral-300 no-underline whitespace-nowrap font-bold transition-all duration-100 rounded`};
            border: 2px solid transparent;

            &:hover {
                ${tw`text-white bg-[#21262D]`};
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
