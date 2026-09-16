import styled from 'styled-components/macro';
import tw from 'twin.macro';

export default styled.div<{ $hoverable?: boolean }>`
    ${tw`flex rounded-md no-underline items-center p-4 overflow-hidden`};
    background-color: var(--neo-surface);
    color: var(--neo-text);
    border: 2.5px solid #000000;
    box-shadow: 4px 4px 0px #000000;
    transition: transform 0.12s ease, box-shadow 0.12s ease;

    ${(props: { $hoverable?: boolean }) =>
        props.$hoverable !== false &&
        `
        &:hover {
            border-color: #00D2FF !important;
            box-shadow: 6px 6px 0px #000000 !important;
            background-color: var(--neo-surface-hover) !important;
        }

        &:active {
            box-shadow: 2px 2px 0px #000000 !important;
        }
    `};

    & .icon {
        ${tw`w-14 h-14 flex items-center justify-center p-3 text-lg`};
        background-color: var(--neo-surface-light);
        color: var(--neo-text);
        border: 2px solid #000000;
        box-shadow: 2px 2px 0px #000000;
        border-radius: 6px;
    }
`;
