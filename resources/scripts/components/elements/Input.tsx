import styled, { css } from 'styled-components/macro';
import tw from 'twin.macro';

export interface Props {
    isLight?: boolean;
    hasError?: boolean;
}

const light = css<Props>`
    ${tw`bg-white border-neutral-200 text-neutral-800`};
    &:focus {
        ${tw`border-primary-400`}
    }

    &:disabled {
        ${tw`bg-neutral-100 border-neutral-200`};
    }
`;

const checkboxStyle = css<Props>`
    appearance-none inline-block align-middle select-none flex-shrink-0 w-4 h-4 cursor-pointer;
    background-color: var(--neo-surface-light);
    border: 2px solid #000000;
    border-radius: 4px;
    box-shadow: 1px 1px 0px #000000;
    transition: all 75ms linear;

    &:checked {
        border-color: #000000;
        background-color: #00D2FF;
        background-repeat: no-repeat;
        background-position: center;
        background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='black' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M5.707 7.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4-4a1 1 0 0 0-1.414-1.414L7 8.586 5.707 7.293z'/%3e%3c/svg%3e");
        background-size: 100% 100%;
    }

    &:focus {
        outline: none;
        box-shadow: 2px 2px 0px #000000;
    }
`;

const inputStyle = css<Props>`
    // Reset to normal styling.
    resize: none;
    ${tw`appearance-none outline-none w-full min-w-0 font-medium`};
    ${tw`p-3 rounded text-sm transition-all duration-100`};
    background-color: var(--neo-surface-light);
    border: 2px solid #000000;
    box-shadow: 2px 2px 0px #000000;
    color: var(--neo-text);

    & + .input-help {
        ${tw`mt-1.5 text-xs font-bold`};
        color: ${(props) => (props.hasError ? '#EF4444' : 'var(--neo-text-muted)')} !important;
    }

    &:required,
    &:invalid {
        box-shadow: 2px 2px 0px #000000;
    }

    &:not(:disabled):not(:read-only):focus {
        border-color: #00D2FF;
        box-shadow: 4px 4px 0px #000000;
        background-color: var(--neo-surface);
    }

    &:disabled {
        ${tw`opacity-60 cursor-not-allowed`};
        box-shadow: none;
    }

    ${(props) => props.isLight && light};
    ${(props) => props.hasError && tw`text-red-100 border-red-400 hover:border-red-300`};
`;

const Input = styled.input<Props>`
    &:not([type='checkbox']):not([type='radio']) {
        ${inputStyle};
    }

    &[type='checkbox'],
    &[type='radio'] {
        ${checkboxStyle};

        &[type='radio'] {
            ${tw`rounded-full`};
        }
    }
`;
const Textarea = styled.textarea<Props>`
    ${inputStyle}
`;

export { Textarea };
export default Input;
