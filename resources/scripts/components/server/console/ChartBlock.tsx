import React from 'react';
import classNames from 'classnames';
import styles from '@/components/server/console/style.module.css';

interface ChartBlockProps {
    title: string;
    legend?: React.ReactNode;
    children: React.ReactNode;
}

export default ({ title, legend, children }: ChartBlockProps) => (
    <div className={classNames(styles.chart_container, 'group')}>
        <div className={'flex items-center justify-between px-4 py-2 border-b-2 border-black/10'}>
            <h3 className={'font-header font-bold text-sm tracking-wide'} style={{ color: 'var(--neo-text)' }}>
                {title}
            </h3>
            {legend && <p className={'text-xs font-mono font-bold flex items-center'} style={{ color: 'var(--neo-text-muted)' }}>{legend}</p>}
        </div>
        <div className={'z-10 ml-2'}>{children}</div>
    </div>
);
