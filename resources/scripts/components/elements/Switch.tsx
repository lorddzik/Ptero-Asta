import React, { useMemo } from 'react';
import styled from 'styled-components/macro';
import { v4 } from 'uuid';
import tw from 'twin.macro';
import Label from '@/components/elements/Label';
import Input from '@/components/elements/Input';

const ToggleContainer = styled.div`
    ${tw`relative select-none w-12 leading-normal`};

    & > input[type='checkbox'] {
        ${tw`hidden`};

        &:checked + label {
            background-color: #00D2FF;
            border-color: #000000;
            box-shadow: 2px 2px 0px #000000;
        }

        &:checked + label:before {
            right: 0.125rem;
            background-color: #000000;
        }
    }

    & > label {
        ${tw`mb-0 block overflow-hidden cursor-pointer h-6 rounded-full transition-all duration-100`};
        background-color: var(--neo-surface-light);
        border: 2px solid #000000;
        box-shadow: 2px 2px 0px #000000;

        &::before {
            ${tw`absolute block h-4 w-4 rounded-full`};
            background-color: #FFFFFF;
            border: 1.5px solid #000000;
            top: 0.125rem;
            right: calc(50% + 0.125rem);
            content: '';
            transition: all 100ms ease;
        }
    }
`;

export interface SwitchProps {
    name: string;
    label?: string;
    description?: string;
    defaultChecked?: boolean;
    readOnly?: boolean;
    onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
    children?: React.ReactNode;
}

const Switch = ({ name, label, description, defaultChecked, readOnly, onChange, children }: SwitchProps) => {
    const uuid = useMemo(() => v4(), []);

    return (
        <div css={tw`flex items-center`}>
            <ToggleContainer css={tw`flex-none`}>
                {children || (
                    <Input
                        id={uuid}
                        name={name}
                        type={'checkbox'}
                        onChange={(e) => onChange && onChange(e)}
                        defaultChecked={defaultChecked}
                        disabled={readOnly}
                    />
                )}
                <Label htmlFor={uuid} />
            </ToggleContainer>
            {(label || description) && (
                <div css={tw`ml-4 w-full`}>
                    {label && (
                        <Label css={[tw`cursor-pointer`, !!description && tw`mb-0`]} htmlFor={uuid}>
                            {label}
                        </Label>
                    )}
                    {description && <p css={tw`text-sm mt-2`} style={{ color: 'var(--neo-text-muted)' }}>{description}</p>}
                </div>
            )}
        </div>
    );
};

export default Switch;
