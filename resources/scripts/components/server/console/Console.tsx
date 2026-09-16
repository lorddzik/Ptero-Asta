import React, { useEffect, useMemo, useRef, useState } from 'react';
import { ITerminalOptions, Terminal } from 'xterm';
import { FitAddon } from 'xterm-addon-fit';
import { SearchAddon } from 'xterm-addon-search';
import { SearchBarAddon } from 'xterm-addon-search-bar';
import { WebLinksAddon } from 'xterm-addon-web-links';
import { Unicode11Addon } from 'xterm-addon-unicode11';
import { ScrollDownHelperAddon } from '@/plugins/XtermScrollDownHelperAddon';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import { ServerContext } from '@/state/server';
import { usePermissions } from '@/plugins/usePermissions';
import { theme as th } from 'twin.macro';
import useEventListener from '@/plugins/useEventListener';
import { debounce } from 'debounce';
import { usePersistedState } from '@/plugins/usePersistedState';
import { SocketEvent, SocketRequest } from '@/components/server/events';
import classNames from 'classnames';
import { ChevronDoubleRightIcon } from '@heroicons/react/solid';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faTrashAlt, faExpand, faCompress, faArrowDown, faTerminal } from '@fortawesome/free-solid-svg-icons';

import 'xterm/css/xterm.css';
import styles from './style.module.css';

const theme = {
    background: th`colors.black`.toString(),
    cursor: 'transparent',
    black: th`colors.black`.toString(),
    red: '#E54B4B',
    green: '#9ECE58',
    yellow: '#FAED70',
    blue: '#396FE2',
    magenta: '#BB80B3',
    cyan: '#2DDAFD',
    white: '#d0d0d0',
    brightBlack: 'rgba(255, 255, 255, 0.2)',
    brightRed: '#FF5370',
    brightGreen: '#C3E88D',
    brightYellow: '#FFCB6B',
    brightBlue: '#82AAFF',
    brightMagenta: '#C792EA',
    brightCyan: '#89DDFF',
    brightWhite: '#ffffff',
    selection: '#FAF089',
};

const terminalProps: ITerminalOptions = {
    disableStdin: true,
    cursorStyle: 'underline',
    allowTransparency: true,
    fontSize: 12,
    fontFamily: th('fontFamily.mono'),
    rows: 30,
    theme: theme,
};

const QUICK_COMMANDS = ['status', 'help', 'list', 'save-all'];

export default () => {
    const TERMINAL_PRELUDE = '\u001b[1m\u001b[33mcontainer@asta~ \u001b[0m';
    const ref = useRef<HTMLDivElement>(null);
    const terminal = useMemo(() => new Terminal({ ...terminalProps }), []);
    const fitAddon = useMemo(() => new FitAddon(), []);
    const searchAddon = useMemo(() => new SearchAddon(), []);
    const searchBar = useMemo(() => new SearchBarAddon({ searchAddon }), [searchAddon]);
    const webLinksAddon = useMemo(() => new WebLinksAddon(), []);
    const unicode11Addon = useMemo(() => new Unicode11Addon(), []);
    const scrollDownHelperAddon = useMemo(() => new ScrollDownHelperAddon(), []);
    const { connected, instance } = ServerContext.useStoreState((state) => state.socket);
    const [canSendCommands] = usePermissions(['control.console']);
    const serverId = ServerContext.useStoreState((state) => state.server.data!.id);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data!.isTransferring);
    const [history, setHistory] = usePersistedState<string[]>(`${serverId}:command_history`, []);
    const [historyIndex, setHistoryIndex] = useState(-1);
    const [isExpanded, setIsExpanded] = useState(false);

    const toggleExpanded = () => {
        setIsExpanded((prev) => {
            const next = !prev;
            setTimeout(() => {
                if (terminal.element) {
                    fitAddon.fit();
                }
            }, 100);
            return next;
        });
    };

    // SearchBarAddon has hardcoded z-index: 999 :(
    const zIndex = `
    .xterm-search-bar__addon {
        z-index: 10;
    }`;

    const handleConsoleOutput = (line: string, prelude = false) =>
        terminal.writeln((prelude ? TERMINAL_PRELUDE : '') + line.replace(/(?:\r\n|\r|\n)$/im, '') + '\u001b[0m');

    const handleTransferStatus = (status: string) => {
        switch (status) {
            // Sent by either the source or target node if a failure occurs.
            case 'failure':
                terminal.writeln(TERMINAL_PRELUDE + 'Transfer has failed.\u001b[0m');
                return;
        }
    };

    const handleDaemonErrorOutput = (line: string) =>
        terminal.writeln(
            TERMINAL_PRELUDE + '\u001b[1m\u001b[41m' + line.replace(/(?:\r\n|\r|\n)$/im, '') + '\u001b[0m'
        );

    const handlePowerChangeEvent = (state: string) =>
        terminal.writeln(TERMINAL_PRELUDE + 'Server marked as ' + state + '...\u001b[0m');

    const handleCommandKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'ArrowUp') {
            const newIndex = Math.min(historyIndex + 1, history!.length - 1);

            setHistoryIndex(newIndex);
            e.currentTarget.value = history![newIndex] || '';

            // By default up arrow will also bring the cursor to the start of the line,
            // so we'll preventDefault to keep it at the end.
            e.preventDefault();
        }

        if (e.key === 'ArrowDown') {
            const newIndex = Math.max(historyIndex - 1, -1);

            setHistoryIndex(newIndex);
            e.currentTarget.value = history![newIndex] || '';
        }

        const command = e.currentTarget.value;
        if (e.key === 'Enter' && command.length > 0) {
            setHistory((prevHistory) => [command, ...prevHistory!].slice(0, 32));
            setHistoryIndex(-1);

            instance && instance.send('send command', command);
            e.currentTarget.value = '';
        }
    };

    useEffect(() => {
        if (connected && ref.current && !terminal.element) {
            terminal.loadAddon(fitAddon);
            terminal.loadAddon(searchAddon);
            terminal.loadAddon(searchBar);
            terminal.loadAddon(webLinksAddon);
            terminal.loadAddon(unicode11Addon);
            terminal.loadAddon(scrollDownHelperAddon);

            terminal.open(ref.current);

            // Activate Unicode 11 for proper emoji and special character width handling
            terminal.unicode.activeVersion = '11';

            fitAddon.fit();
            searchBar.addNewStyle(zIndex);

            // Add support for capturing keys
            terminal.attachCustomKeyEventHandler((e: KeyboardEvent) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
                    document.execCommand('copy');
                    return false;
                } else if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    searchBar.show();
                    return false;
                } else if (e.key === 'Escape') {
                    searchBar.hidden();
                }
                return true;
            });
        }
    }, [terminal, connected]);

    useEventListener(
        'resize',
        debounce(() => {
            if (terminal.element) {
                fitAddon.fit();
            }
        }, 100)
    );

    useEffect(() => {
        const listeners: Record<string, (s: string) => void> = {
            [SocketEvent.STATUS]: handlePowerChangeEvent,
            [SocketEvent.CONSOLE_OUTPUT]: handleConsoleOutput,
            [SocketEvent.INSTALL_OUTPUT]: handleConsoleOutput,
            [SocketEvent.TRANSFER_LOGS]: handleConsoleOutput,
            [SocketEvent.TRANSFER_STATUS]: handleTransferStatus,
            [SocketEvent.DAEMON_MESSAGE]: (line) => handleConsoleOutput(line, true),
            [SocketEvent.DAEMON_ERROR]: handleDaemonErrorOutput,
        };

        if (connected && instance) {
            // Do not clear the console if the server is being transferred.
            if (!isTransferring) {
                terminal.clear();
            }

            Object.keys(listeners).forEach((key: string) => {
                instance.addListener(key, listeners[key]);
            });
            instance.send(SocketRequest.SEND_LOGS);
        }

        return () => {
            if (instance) {
                Object.keys(listeners).forEach((key: string) => {
                    instance.removeListener(key, listeners[key]);
                });
            }
        };
    }, [connected, instance]);

    return (
        <>
            {isExpanded && (
                <div
                    className={'fixed inset-0 bg-black/70 backdrop-blur-sm z-40'}
                    onClick={toggleExpanded}
                />
            )}
            <div
                className={classNames(styles.terminal, 'relative', {
                    'fixed inset-2 md:inset-6 z-50 h-[calc(100vh-1rem)] md:h-[calc(100vh-3rem)] shadow-2xl': isExpanded,
                })}
            >
                <SpinnerOverlay visible={!connected} size={'large'} />

                {/* Neo-Brutalist Mac-Style Terminal Bar */}
                <div
                    className={'flex items-center justify-between px-3 py-2 border-b-2 border-black select-none'}
                    style={{ backgroundColor: 'var(--neo-surface)' }}
                >
                    <div className={'flex items-center space-x-2'}>
                        <div className={'flex space-x-1.5'}>
                            <span className={'w-3 h-3 rounded-full bg-[#EF4444] inline-block border border-black'} />
                            <span className={'w-3 h-3 rounded-full bg-[#FACC15] inline-block border border-black'} />
                            <span className={'w-3 h-3 rounded-full bg-[#10B981] inline-block border border-black'} />
                        </div>
                        <span className={'font-mono text-xs font-bold pl-2 flex items-center gap-1.5'} style={{ color: 'var(--neo-text)' }}>
                            <FontAwesomeIcon icon={faTerminal} className={'text-xs text-[#00D2FF]'} />
                            asta@server: ~
                        </span>
                    </div>
                    <div className={'flex items-center space-x-1.5 sm:space-x-2'}>
                        <button
                            type={'button'}
                            onClick={() => terminal.clear()}
                            className={'px-2 py-1 text-xs font-mono font-bold rounded flex items-center gap-1 transition-all active:translate-y-0.5'}
                            style={{
                                backgroundColor: 'var(--neo-surface-light)',
                                color: 'var(--neo-text)',
                                border: '1.5px solid #000000',
                                boxShadow: '1.5px 1.5px 0px #000000',
                            }}
                            title={'Clear console'}
                        >
                            <FontAwesomeIcon icon={faTrashAlt} className={'text-xs'} />
                            <span className={'hidden sm:inline'}>Clear</span>
                        </button>
                        <button
                            type={'button'}
                            onClick={() => terminal.scrollToBottom()}
                            className={'px-2 py-1 text-xs font-mono font-bold rounded flex items-center gap-1 transition-all active:translate-y-0.5'}
                            style={{
                                backgroundColor: 'var(--neo-surface-light)',
                                color: 'var(--neo-text)',
                                border: '1.5px solid #000000',
                                boxShadow: '1.5px 1.5px 0px #000000',
                            }}
                            title={'Scroll to bottom'}
                        >
                            <FontAwesomeIcon icon={faArrowDown} className={'text-xs'} />
                            <span className={'hidden sm:inline'}>Bottom</span>
                        </button>
                        <button
                            type={'button'}
                            onClick={toggleExpanded}
                            className={'px-2 py-1 text-xs font-mono font-bold rounded flex items-center gap-1 transition-all active:translate-y-0.5'}
                            style={{
                                backgroundColor: isExpanded ? '#FACC15' : 'var(--neo-surface-light)',
                                color: isExpanded ? '#000000' : 'var(--neo-text)',
                                border: '1.5px solid #000000',
                                boxShadow: '1.5px 1.5px 0px #000000',
                            }}
                            title={isExpanded ? 'Collapse' : 'Expand Fullscreen'}
                        >
                            <FontAwesomeIcon icon={isExpanded ? faCompress : faExpand} className={'text-xs'} />
                            <span className={'hidden sm:inline'}>{isExpanded ? 'Exit' : 'Expand'}</span>
                        </button>
                    </div>
                </div>

                <div
                    className={classNames(styles.container, styles.overflows_container, { 'rounded-b': !canSendCommands })}
                >
                    <div className={'h-full'}>
                        <div id={styles.terminal} ref={ref} />
                    </div>
                </div>

                {canSendCommands && (
                    <>
                        {/* Quick Command Chips */}
                        <div
                            className={'flex items-center gap-1.5 px-3 py-1.5 overflow-x-auto border-t-2 border-black select-none'}
                            style={{ backgroundColor: 'var(--neo-surface)' }}
                        >
                            <span className={'text-[11px] font-mono font-bold uppercase tracking-wider shrink-0'} style={{ color: 'var(--neo-text-muted)' }}>
                                Quick:
                            </span>
                            {QUICK_COMMANDS.map((cmd) => (
                                <button
                                    key={cmd}
                                    type={'button'}
                                    disabled={!instance || !connected}
                                    onClick={() => {
                                        if (instance && connected) {
                                            instance.send('send command', cmd);
                                        }
                                    }}
                                    className={'px-2 py-0.5 text-xs font-mono font-bold rounded transition-all active:translate-y-0.5 shrink-0'}
                                    style={{
                                        backgroundColor: 'var(--neo-surface-light)',
                                        color: 'var(--neo-text)',
                                        border: '1.5px solid #000000',
                                        boxShadow: '1.5px 1.5px 0px #000000',
                                    }}
                                >
                                    {cmd}
                                </button>
                            ))}
                        </div>

                        {/* Command Input Bar */}
                        <div className={classNames('relative', styles.overflows_container)}>
                            <input
                                className={classNames('peer', styles.command_input)}
                                type={'text'}
                                placeholder={'Type a command...'}
                                aria-label={'Console command input.'}
                                disabled={!instance || !connected}
                                onKeyDown={handleCommandKeyDown}
                                autoCorrect={'off'}
                                autoCapitalize={'none'}
                            />
                            <div
                                className={classNames(
                                    'text-[#00D2FF] peer-focus:text-white peer-focus:animate-pulse flex items-center font-mono font-black text-xs',
                                    styles.command_icon
                                )}
                            >
                                <span className={'mr-1 text-[11px]'}>asta</span>
                                <ChevronDoubleRightIcon className={'w-3.5 h-3.5'} />
                            </div>
                        </div>
                    </>
                )}
            </div>
        </>
    );
};
