import React, { memo } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { IconProp } from '@fortawesome/fontawesome-svg-core';
import tw from 'twin.macro';
import isEqual from 'react-fast-compare';

interface Props {
    icon?: IconProp;
    title: string | React.ReactNode;
    className?: string;
    children: React.ReactNode;
}

const TitledGreyBox = ({ icon, title, children, className }: Props) => (
    <div
        css={tw`rounded-md overflow-hidden transition-all duration-100`}
        style={{
            backgroundColor: 'var(--neo-surface)',
            border: '2.5px solid #000000',
            boxShadow: '4px 4px 0px #000000',
        }}
        className={className}
    >
        <div
            css={tw`p-3 border-b-2 border-black`}
            style={{ backgroundColor: 'var(--neo-surface-light)' }}
        >
            {typeof title === 'string' ? (
                <p css={tw`text-sm font-extrabold uppercase tracking-wide`} style={{ color: 'var(--neo-text)' }}>
                    {icon && <FontAwesomeIcon icon={icon} css={tw`mr-2`} style={{ color: '#00D2FF' }} />}
                    {title}
                </p>
            ) : (
                title
            )}
        </div>
        <div css={tw`p-3`} style={{ color: 'var(--neo-text)' }}>{children}</div>
    </div>
);

export default memo(TitledGreyBox, isEqual);
