import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Server } from '@/api/server/getServer';
import getServers, { ServersResponse } from '@/api/getServers';
import ServerRow from '@/components/dashboard/ServerRow';
import Spinner from '@/components/elements/Spinner';
import PageContentBlock from '@/components/elements/PageContentBlock';
import useFlash from '@/plugins/useFlash';
import { useStoreState } from 'easy-peasy';
import { usePersistedState } from '@/plugins/usePersistedState';
import Switch from '@/components/elements/Switch';
import tw from 'twin.macro';
import useSWR from 'swr';
import Pagination from '@/components/elements/Pagination';
import { useLocation } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faServer, faChevronDown, faChevronUp, faCheck, faFilter } from '@fortawesome/free-solid-svg-icons';

export default () => {
    const { search } = useLocation();
    const defaultPage = Number(new URLSearchParams(search).get('page') || '1');

    const [page, setPage] = useState(!isNaN(defaultPage) && defaultPage > 0 ? defaultPage : 1);
    const [selectedNode, setSelectedNode] = useState<string>('');
    const [isFilterOpen, setIsFilterOpen] = useState(false);
    const filterRef = useRef<HTMLDivElement>(null);

    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const uuid = useStoreState((state) => state.user.data!.uuid);
    const rootAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const [showOnlyAdmin, setShowOnlyAdmin] = usePersistedState(`${uuid}:show_all_servers`, false);

    const { data: servers, error } = useSWR<ServersResponse>(
        ['/api/client/servers', showOnlyAdmin && rootAdmin, page, selectedNode],
        () => getServers({ page, type: showOnlyAdmin && rootAdmin ? 'admin' : undefined, node: selectedNode || undefined })
    );

    const availableNodes = useMemo(() => {
        const nodesFromMeta = servers?.nodes || [];
        const nodesFromItems = (servers?.items || []).map((s) => s.node).filter(Boolean);
        return Array.from(new Set([...nodesFromMeta, ...nodesFromItems])).sort();
    }, [servers?.nodes, servers?.items]);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (filterRef.current && !filterRef.current.contains(event.target as Node)) {
                setIsFilterOpen(false);
            }
        };

        if (isFilterOpen) {
            document.addEventListener('mousedown', handleClickOutside);
        }
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, [isFilterOpen]);

    useEffect(() => {
        setPage(1);
    }, [showOnlyAdmin, selectedNode]);

    useEffect(() => {
        if (!servers) return;
        if (servers.pagination.currentPage > 1 && !servers.items.length) {
            setPage(1);
        }
    }, [servers?.pagination.currentPage]);

    useEffect(() => {
        // Don't use react-router to handle changing this part of the URL, otherwise it
        // triggers a needless re-render. We just want to track this in the URL incase the
        // user refreshes the page.
        window.history.replaceState(null, document.title, `/${page <= 1 ? '' : `?page=${page}`}`);
    }, [page]);

    useEffect(() => {
        if (error) clearAndAddHttpError({ key: 'dashboard', error });
        if (!error) clearFlashes('dashboard');
    }, [error]);

    return (
        <PageContentBlock title={'Dashboard'} showFlashKey={'dashboard'}>
            {rootAdmin && (
                <div css={tw`mb-4 flex flex-wrap justify-end items-center gap-3`}>
                    {/* Lemari / Dropdown Filter Node */}
                    <div ref={filterRef} css={tw`relative`}>
                        <button
                            type={'button'}
                            onClick={() => setIsFilterOpen((prev) => !prev)}
                            css={tw`flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold uppercase transition-all duration-150 select-none cursor-pointer`}
                            style={{
                                backgroundColor: selectedNode ? 'var(--neo-primary)' : 'var(--neo-surface)',
                                color: selectedNode ? '#000000' : 'var(--neo-text)',
                                border: '2px solid #000000',
                                boxShadow: '2px 2px 0px #000000',
                            }}
                        >
                            <FontAwesomeIcon icon={faServer} />
                            <span>{selectedNode ? `Node: ${selectedNode}` : 'All Nodes'}</span>
                            <FontAwesomeIcon icon={isFilterOpen ? faChevronUp : faChevronDown} css={tw`text-[10px]`} />
                        </button>

                        {isFilterOpen && (
                            <div
                                css={tw`absolute right-0 mt-2 w-64 rounded-xl p-2 z-50`}
                                style={{
                                    backgroundColor: 'var(--neo-surface)',
                                    border: '2.5px solid #000000',
                                    boxShadow: '4px 4px 0px #000000',
                                }}
                            >
                                <div css={tw`flex items-center justify-between px-2 py-1.5 mb-1.5 border-b border-black/20`}>
                                    <div css={tw`flex items-center gap-1.5 text-xs font-black uppercase tracking-wider`} style={{ color: 'var(--neo-text)' }}>
                                        <FontAwesomeIcon icon={faFilter} />
                                        <span>Filter Node</span>
                                    </div>
                                    {selectedNode && (
                                        <button
                                            type={'button'}
                                            onClick={() => {
                                                setSelectedNode('');
                                                setIsFilterOpen(false);
                                            }}
                                            css={tw`text-[11px] font-bold text-red-500 hover:text-red-700 cursor-pointer`}
                                        >
                                            Reset
                                        </button>
                                    )}
                                </div>

                                <div css={tw`max-h-56 overflow-y-auto flex flex-col gap-1 pr-1`}>
                                    <button
                                        type={'button'}
                                        onClick={() => {
                                            setSelectedNode('');
                                            setIsFilterOpen(false);
                                        }}
                                        css={tw`flex items-center justify-between w-full px-2.5 py-1.5 rounded-md text-xs font-bold text-left transition-all cursor-pointer`}
                                        style={{
                                            backgroundColor: !selectedNode ? 'var(--neo-surface-light)' : 'transparent',
                                            color: 'var(--neo-text)',
                                            border: !selectedNode ? '1.5px solid #000000' : '1.5px solid transparent',
                                        }}
                                    >
                                        <span>All Nodes (Semua Node)</span>
                                        {!selectedNode && <FontAwesomeIcon icon={faCheck} />}
                                    </button>

                                    {availableNodes.length > 0 ? (
                                        availableNodes.map((node) => (
                                            <button
                                                key={node}
                                                type={'button'}
                                                onClick={() => {
                                                    setSelectedNode(node);
                                                    setIsFilterOpen(false);
                                                }}
                                                css={tw`flex items-center justify-between w-full px-2.5 py-1.5 rounded-md text-xs font-mono font-bold text-left transition-all cursor-pointer`}
                                                style={{
                                                    backgroundColor: selectedNode === node ? 'var(--neo-surface-light)' : 'transparent',
                                                    color: 'var(--neo-text)',
                                                    border: selectedNode === node ? '1.5px solid #000000' : '1.5px solid transparent',
                                                }}
                                            >
                                                <span css={tw`truncate mr-2`}>{node}</span>
                                                {selectedNode === node && <FontAwesomeIcon icon={faCheck} />}
                                            </button>
                                        ))
                                    ) : (
                                        <p css={tw`text-center text-xs py-2`} style={{ color: 'var(--neo-text-muted)' }}>
                                            Tidak ada node lain.
                                        </p>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Switch Showing others' servers */}
                    <div css={tw`flex items-center`}>
                        <p css={tw`uppercase text-xs mr-2 font-bold`} style={{ color: 'var(--neo-text-muted)' }}>
                            {showOnlyAdmin ? "Showing others' servers" : 'Showing your servers'}
                        </p>
                        <Switch
                            name={'show_all_servers'}
                            defaultChecked={showOnlyAdmin}
                            onChange={() => setShowOnlyAdmin((s) => !s)}
                        />
                    </div>
                </div>
            )}
            {!servers ? (
                <Spinner centered size={'large'} />
            ) : (
                <Pagination<Server> data={servers} onPageSelect={setPage}>
                    {({ items }) =>
                        items.length > 0 ? (
                            items.map((server, index) => (
                                <ServerRow key={server.uuid} server={server} css={index > 0 ? tw`mt-4` : undefined} />
                            ))
                        ) : (
                            <p css={tw`text-center text-sm`} style={{ color: 'var(--neo-text-muted)' }}>
                                {selectedNode
                                    ? `No servers found on node "${selectedNode}".`
                                    : showOnlyAdmin
                                    ? 'There are no other servers to display.'
                                    : 'There are no servers associated with your account.'}
                            </p>
                        )
                    }
                </Pagination>
            )}
        </PageContentBlock>
    );
};
