import React, { useContext } from 'react';
import { DialogContext } from './';
import { useDeepCompareEffect } from '@/plugins/useDeepCompareEffect';

export default ({ children }: { children: React.ReactNode }) => {
    const { setFooter } = useContext(DialogContext);

    useDeepCompareEffect(() => {
        setFooter(
            <div
                className={'px-6 py-3 flex items-center justify-end space-x-3 rounded-b'}
                style={{
                    backgroundColor: 'var(--neo-surface-light)',
                    borderTop: '2px solid #000000',
                }}
            >
                {children}
            </div>
        );
    }, [children]);

    return null;
};
