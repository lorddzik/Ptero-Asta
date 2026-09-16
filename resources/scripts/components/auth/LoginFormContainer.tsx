import React, { forwardRef } from 'react';
import { Form } from 'formik';
import styled from 'styled-components/macro';
import { breakpoint } from '@/theme';
import FlashMessageRender from '@/components/FlashMessageRender';
import tw from 'twin.macro';

type Props = React.DetailedHTMLProps<React.FormHTMLAttributes<HTMLFormElement>, HTMLFormElement> & {
    title?: string;
};

const Container = styled.div`
    ${breakpoint('sm')`
        ${tw`w-4/5 mx-auto`}
    `};

    ${breakpoint('md')`
        ${tw`p-10`}
    `};

    ${breakpoint('lg')`
        ${tw`w-3/5`}
    `};

    ${breakpoint('xl')`
        ${tw`w-full`}
        max-width: 700px;
    `};
`;

export default forwardRef<HTMLFormElement, Props>(({ title, ...props }, ref) => (
    <Container>
        {title && (
            <h2 css={tw`text-3xl text-center text-white font-extrabold uppercase tracking-wide py-4`}>
                {title}
            </h2>
        )}
        <FlashMessageRender className={'mb-2 px-1'} />
        <Form {...props} ref={ref}>
            <div
                css={tw`md:flex w-full rounded-md p-6 md:pl-2 mx-1`}
                style={{
                    backgroundColor: '#161B22',
                    border: '3px solid #000000',
                    boxShadow: '8px 8px 0px #00D2FF',
                }}
            >
                <div css={tw`flex-none select-none mb-6 md:mb-0 self-center flex items-center justify-center p-4`}>
                    <img
                        src={'/assets/astabrand.png'}
                        alt={'Asta'}
                        css={tw`block w-44 md:w-56 mx-auto object-contain`}
                        style={{ filter: 'drop-shadow(4px 4px 0px #000000)' }}
                    />
                </div>
                <div css={tw`flex-1`}>{props.children}</div>
            </div>
        </Form>
        <p css={tw`text-center text-neutral-500 text-xs mt-4`}>
            &copy; 2015 - {new Date().getFullYear()}&nbsp;
            <a
                rel={'noopener nofollow noreferrer'}
                href={'#'}
                target={'_blank'}
                css={tw`no-underline text-neutral-500 hover:text-neutral-300`}
            >
                Asta Panel
            </a>
        </p>
    </Container>
));
