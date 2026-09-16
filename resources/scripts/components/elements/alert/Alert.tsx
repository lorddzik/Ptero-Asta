import { ExclamationIcon, ShieldExclamationIcon } from '@heroicons/react/outline';
import React from 'react';
import classNames from 'classnames';

interface AlertProps {
    type: 'warning' | 'danger';
    className?: string;
    children: React.ReactNode;
}

export default ({ type, className, children }: AlertProps) => {
    return (
        <div
            style={{ color: 'var(--neo-text)' }}
            className={classNames(
                'flex items-center border-l-8 rounded-md shadow px-4 py-3 font-medium',
                {
                    ['border-red-500 bg-red-500/20']: type === 'danger',
                    ['border-yellow-500 bg-yellow-500/20']: type === 'warning',
                },
                className
            )}
        >
            {type === 'danger' ? (
                <ShieldExclamationIcon className={'w-6 h-6 text-red-400 mr-2'} />
            ) : (
                <ExclamationIcon className={'w-6 h-6 text-yellow-500 mr-2'} />
            )}
            {children}
        </div>
    );
};
