import React, { memo } from 'react';
import { ServerContext } from '@/state/server';
import Can from '@/components/elements/Can';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import isEqual from 'react-fast-compare';
import Spinner from '@/components/elements/Spinner';
import Features from '@feature/Features';
import Console from '@/components/server/console/Console';
import StatGraphs from '@/components/server/console/StatGraphs';
import PowerButtons from '@/components/server/console/PowerButtons';
import ServerDetailsBlock from '@/components/server/console/ServerDetailsBlock';
import { Alert } from '@/components/elements/alert';

export type PowerAction = 'start' | 'stop' | 'restart' | 'kill';

const ServerConsoleContainer = () => {
    const name = ServerContext.useStoreState((state) => state.server.data!.name);
    const description = ServerContext.useStoreState((state) => state.server.data!.description);
    const status = ServerContext.useStoreState((state) => state.status.value);
    const isInstalling = ServerContext.useStoreState((state) => state.server.isInstalling);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data!.isTransferring);
    const eggFeatures = ServerContext.useStoreState((state) => state.server.data!.eggFeatures, isEqual);
    const isNodeUnderMaintenance = ServerContext.useStoreState((state) => state.server.data!.isNodeUnderMaintenance);

    const isOnline = status === 'running';
    const isTransitioning = status === 'starting' || status === 'stopping';

    return (
        <ServerContentBlock title={'Console'}>
            {(isNodeUnderMaintenance || isInstalling || isTransferring) && (
                <Alert type={'warning'} className={'mb-4'}>
                    {isNodeUnderMaintenance
                        ? 'The node of this server is currently under maintenance and all actions are unavailable.'
                        : isInstalling
                        ? 'This server is currently running its installation process and most actions are unavailable.'
                        : 'This server is currently being transferred to another node and all actions are unavailable.'}
                </Alert>
            )}
            <div className={'grid grid-cols-4 gap-4 mb-4'}>
                <div className={'col-span-4 sm:col-span-2 lg:col-span-3 pr-4'}>
                    <div className={'flex flex-wrap items-center gap-3'}>
                        <h1 className={'font-header font-black text-2xl lg:text-3xl tracking-tight leading-relaxed line-clamp-1'} style={{ color: 'var(--neo-text)' }}>
                            {name}
                        </h1>
                        <div
                            className={'inline-flex items-center px-3 py-1 rounded text-xs font-black uppercase tracking-wider select-none'}
                            style={{
                                backgroundColor: isOnline ? '#10B981' : isTransitioning ? '#FACC15' : '#EF4444',
                                color: isTransitioning ? '#000000' : '#FFFFFF',
                                border: '2px solid #000000',
                                boxShadow: '2.5px 2.5px 0px #000000',
                            }}
                        >
                            <span className={'relative flex h-2 w-2 mr-2'}>
                                {isOnline && (
                                    <span className={'animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75'} />
                                )}
                                <span
                                    className={'relative inline-flex rounded-full h-2 w-2'}
                                    style={{ backgroundColor: isTransitioning ? '#000000' : '#FFFFFF' }}
                                />
                            </span>
                            {status ? status : 'Offline'}
                        </div>
                    </div>
                    {description && (
                        <p className={'text-sm line-clamp-2 mt-1'} style={{ color: 'var(--neo-text-muted)' }}>{description}</p>
                    )}
                </div>
                <div className={'col-span-4 sm:col-span-2 lg:col-span-1 self-end'}>
                    <Can action={['control.start', 'control.stop', 'control.restart']} matchAny>
                        <PowerButtons className={'flex sm:justify-end space-x-2'} />
                    </Can>
                </div>
            </div>
            <div className={'grid grid-cols-4 gap-2 sm:gap-4 mb-4'}>
                <div className={'flex col-span-4 lg:col-span-3'}>
                    <Spinner.Suspense>
                        <Console />
                    </Spinner.Suspense>
                </div>
                <ServerDetailsBlock className={'col-span-4 lg:col-span-1 order-last lg:order-none'} />
            </div>
            <div className={'grid grid-cols-1 md:grid-cols-3 gap-2 sm:gap-4'}>
                <Spinner.Suspense>
                    <StatGraphs />
                </Spinner.Suspense>
            </div>
            <Features enabled={eggFeatures} />
        </ServerContentBlock>
    );
};

export default memo(ServerConsoleContainer, isEqual);
