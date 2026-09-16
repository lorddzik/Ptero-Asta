import React from 'react';
import styled, { css } from 'styled-components/macro';
import tw from 'twin.macro';
import Spinner from '@/components/elements/Spinner';

interface Props {
    isLoading?: boolean;
    size?: 'xsmall' | 'small' | 'large' | 'xlarge';
    color?: 'green' | 'red' | 'primary' | 'grey';
    isSecondary?: boolean;
}

const ButtonStyle = styled.button<Omit<Props, 'isLoading'>>`
    ${tw`relative inline-flex items-center justify-center rounded p-2 font-extrabold uppercase tracking-wider text-sm cursor-pointer`};
    border: 2.5px solid #000000;
    box-shadow: 4px 4px 0px #000000;
    transition: transform 0.1s ease, box-shadow 0.1s ease;

    &:hover:not(:disabled) {
        transform: translate(-2px, -2px);
        box-shadow: 6px 6px 0px #000000;
    }

    &:active:not(:disabled) {
        transform: translate(2px, 2px);
        box-shadow: 0px 0px 0px #000000;
    }

    ${(props: Omit<Props, 'isLoading'>) =>
        ((!props.isSecondary && !props.color) || props.color === 'primary') &&
        css`
            background-color: #00D2FF;
            color: #000000;

            &:hover:not(:disabled) {
                background-color: #33DCFF;
            }
        `};

    ${(props: Omit<Props, 'isLoading'>) =>
        props.color === 'grey' &&
        css`
            background-color: var(--neo-surface-light);
            color: var(--neo-text);

            &:hover:not(:disabled) {
                background-color: var(--neo-surface-hover);
            }
        `};

    ${(props: Omit<Props, 'isLoading'>) =>
        props.color === 'green' &&
        css`
            background-color: #10B981;
            color: #000000;

            &:hover:not(:disabled) {
                background-color: #34D399;
            }
        `};

    ${(props: Omit<Props, 'isLoading'>) =>
        props.color === 'red' &&
        css`
            background-color: #EF4444;
            color: #FFFFFF;

            &:hover:not(:disabled) {
                background-color: #F87171;
            }
        `};

    ${(props: Omit<Props, 'isLoading'>) => props.size === 'xsmall' && tw`px-2 py-1 text-xs`};
    ${(props: Omit<Props, 'isLoading'>) => (!props.size || props.size === 'small') && tw`px-4 py-2`};
    ${(props: Omit<Props, 'isLoading'>) => props.size === 'large' && tw`p-4 text-base`};
    ${(props: Omit<Props, 'isLoading'>) => props.size === 'xlarge' && tw`p-4 w-full`};

    ${(props: Omit<Props, 'isLoading'>) =>
        props.isSecondary &&
        css`
            background-color: var(--neo-surface-light);
            color: var(--neo-text);

            &:hover:not(:disabled) {
                background-color: var(--neo-surface-hover);
                ${props.color === 'red' && `background-color: #EF4444 !important; color: #FFFFFF !important;`};
                ${props.color === 'primary' && `background-color: #00D2FF !important; color: #000000 !important;`};
                ${props.color === 'green' && `background-color: #10B981 !important; color: #000000 !important;`};
            }
        `};

    &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: 2px 2px 0px #000000 !important;
    }
`;

type ComponentProps = Omit<JSX.IntrinsicElements['button'], 'ref' | keyof Props> & Props;

const Button: React.FC<ComponentProps> = ({ children, isLoading, ...props }) => (
    <ButtonStyle {...props}>
        {isLoading && (
            <div css={tw`flex absolute justify-center items-center w-full h-full left-0 top-0`}>
                <Spinner size={'small'} />
            </div>
        )}
        <span css={isLoading ? tw`text-transparent` : undefined}>{children}</span>
    </ButtonStyle>
);

type LinkProps = Omit<JSX.IntrinsicElements['a'], 'ref' | keyof Props> & Props;

const LinkButton: React.FC<LinkProps> = (props) => <ButtonStyle as={'a'} {...props} />;

export { LinkButton, ButtonStyle };
export default Button;
