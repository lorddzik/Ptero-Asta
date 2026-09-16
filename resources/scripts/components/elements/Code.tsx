import React from 'react';
import classNames from 'classnames';

interface CodeProps {
    dark?: boolean | undefined;
    className?: string;
    children: React.ReactChild | React.ReactFragment | React.ReactPortal;
}

export default ({ dark: _dark, className, children }: CodeProps) => (
    <code
        className={classNames('font-mono text-sm px-2 py-1 inline-block rounded', className)}
        style={{
            backgroundColor: 'var(--neo-surface-light)',
            color: 'var(--neo-text)',
            border: '1.5px solid #000000',
        }}
    >
        {children}
    </code>
);
