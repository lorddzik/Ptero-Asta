import styled from 'styled-components/macro';
import tw from 'twin.macro';

export default styled.div<{ $hoverable?: boolean }>`
    ${tw`flex rounded-md no-underline text-neutral-200 items-center p-4 overflow-hidden`};
    background-color: #161B22;
    border: 2.5px solid #000000;
    box-shadow: 4px 4px 0px #000000;
    transition: transform 0.12s ease, box-shadow 0.12s ease;

    ${(props: { $hoverable?: boolean }) =>
        props.$hoverable !== false &&
        `
        &:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0px #000000;
            border-color: #000000;
        }

        &:active {
            transform: translate(2px, 2px);
            box-shadow: 0px 0px 0px #000000;
        }
    `};

    & .icon {
        ${tw`w-14 h-14 flex items-center justify-center p-3 text-lg`};
        background-color: #21262D;
        border: 2px solid #000000;
        box-shadow: 2px 2px 0px #000000;
        border-radius: 6px;
    }
`;
