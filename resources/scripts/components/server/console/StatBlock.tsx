import React from 'react';
import Icon from '@/components/elements/Icon';
import { IconDefinition } from '@fortawesome/free-solid-svg-icons';
import classNames from 'classnames';
import styles from './style.module.css';
import useFitText from 'use-fit-text';
import CopyOnClick from '@/components/elements/CopyOnClick';

interface StatBlockProps {
    title: string;
    copyOnClick?: string;
    color?: string | undefined;
    icon: IconDefinition;
    children: React.ReactNode;
    className?: string;
    progress?: number;
}

export default ({ title, copyOnClick, icon, color, className, progress, children }: StatBlockProps) => {
    const { fontSize, ref } = useFitText({ minFontSize: 8, maxFontSize: 500 });

    return (
        <CopyOnClick text={copyOnClick}>
            <div className={classNames(styles.stat_block, 'bg-gray-600', className)}>
                <div className={classNames(styles.status_bar, color || 'bg-gray-700')} />
                <div className={classNames(styles.icon, color || 'bg-gray-700')}>
                    <Icon
                        icon={icon}
                        className={classNames({
                            'text-gray-100': !color || color === 'bg-gray-700',
                            'text-gray-50': color && color !== 'bg-gray-700',
                        })}
                    />
                </div>
                <div className={'flex flex-col justify-center overflow-hidden w-full'}>
                    <p className={'font-header font-medium leading-tight text-xs md:text-sm'} style={{ color: 'var(--neo-text-muted)' }}>{title}</p>
                    <div
                        ref={ref}
                        className={'h-[1.75rem] w-full font-semibold truncate'}
                        style={{ fontSize, color: 'var(--neo-text)' }}
                    >
                        {children}
                    </div>
                    {typeof progress === 'number' && !isNaN(progress) && (
                        <div className={'w-full bg-black/20 rounded-full h-1.5 mt-1 overflow-hidden border border-black/40'}>
                            <div
                                className={'h-full rounded-full transition-all duration-300'}
                                style={{
                                    width: `${Math.min(Math.max(progress, 0), 100)}%`,
                                    backgroundColor:
                                        progress > 90 ? '#EF4444' : progress > 70 ? '#FACC15' : '#00D2FF',
                                }}
                            />
                        </div>
                    )}
                </div>
            </div>
        </CopyOnClick>
    );
};

