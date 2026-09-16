import styled, { css } from 'styled-components/macro';
import tw from 'twin.macro';

interface Props {
    hideDropdownArrow?: boolean;
}

const Select = styled.select<Props>`
    ${tw`block p-3 pr-8 rounded text-sm transition-all duration-100 font-medium w-full`};
    background-color: var(--neo-surface-light);
    border: 2px solid #000000;
    box-shadow: 2px 2px 0px #000000;
    color: var(--neo-text);

    &,
    &:hover:not(:disabled),
    &:focus {
        outline: none;
    }

    &:focus {
        border-color: #00D2FF;
        box-shadow: 4px 4px 0px #000000;
        background-color: var(--neo-surface);
    }

    -webkit-appearance: none;
    -moz-appearance: none;
    background-size: 1rem;
    background-repeat: no-repeat;
    background-position-x: calc(100% - 0.75rem);
    background-position-y: center;

    &::-ms-expand {
        display: none;
    }

    ${(props: Props) =>
        !props.hideDropdownArrow &&
        css`
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='%2300D2FF' d='M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z'/%3e%3c/svg%3e ");
        `};
`;

export default Select;
