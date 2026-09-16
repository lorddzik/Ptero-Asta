import * as React from 'react';
import tw, { TwStyle } from 'twin.macro';
import styled from 'styled-components/macro';

export type FlashMessageType = 'success' | 'info' | 'warning' | 'error';

interface Props {
    title?: string;
    children: string;
    type?: FlashMessageType;
}

const styling = (type?: FlashMessageType): string => {
    switch (type) {
        case 'error':
            return 'background-color: #EF4444; color: #FFFFFF;';
        case 'info':
            return 'background-color: #00D2FF; color: #000000;';
        case 'success':
            return 'background-color: #10B981; color: #000000;';
        case 'warning':
            return 'background-color: #FACC15; color: #000000;';
        default:
            return 'background-color: var(--neo-surface-light); color: var(--neo-text);';
    }
};

const getBackground = (type?: FlashMessageType): string => {
    switch (type) {
        case 'error':
            return 'background-color: #000000; color: #EF4444;';
        case 'info':
            return 'background-color: #000000; color: #00D2FF;';
        case 'success':
            return 'background-color: #000000; color: #10B981;';
        case 'warning':
            return 'background-color: #000000; color: #FACC15;';
        default:
            return 'background-color: #000000; color: #FFFFFF;';
    }
};

const Container = styled.div<{ $type?: FlashMessageType }>`
    ${tw`p-2.5 items-center leading-normal flex w-full text-sm font-semibold`};
    border: 2px solid #000000;
    box-shadow: 3px 3px 0px #000000;
    border-radius: 4px;
    ${(props) => styling(props.$type)};
`;
Container.displayName = 'MessageBox.Container';

const MessageBox = ({ title, children, type }: Props) => (
    <Container css={tw`lg:inline-flex`} $type={type} role={'alert'}>
        {title && (
            <span
                className={'title'}
                css={[
                    tw`flex rounded-full uppercase px-2 py-1 text-xs font-bold mr-3 leading-none`,
                    getBackground(type),
                ]}
            >
                {title}
            </span>
        )}
        <span css={tw`mr-2 text-left flex-auto`}>{children}</span>
    </Container>
);
MessageBox.displayName = 'MessageBox';

export default MessageBox;
