import React, { useEffect, useState } from 'react';
import { ServerContext } from '@/state/server';
import Modal from '@/components/elements/Modal';
import tw from 'twin.macro';
import Button from '@/components/elements/Button';
import FlashMessageRender from '@/components/FlashMessageRender';
import useFlash from '@/plugins/useFlash';
import { SocketEvent } from '@/components/server/events';
import { useStoreState } from 'easy-peasy';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faExclamationTriangle } from '@fortawesome/free-solid-svg-icons';

const PIDLimitModalFeature = () => {
    const [visible, setVisible] = useState(false);
    const [loading] = useState(false);

    const status = ServerContext.useStoreState((state) => state.status.value);
    const { clearFlashes } = useFlash();
    const { connected, instance } = ServerContext.useStoreState((state) => state.socket);
    const isAdmin = useStoreState((state) => state.user.data!.rootAdmin);

    useEffect(() => {
        if (!connected || !instance || status === 'running') return;

        const errors = [
            'pthread_create failed',
            'failed to create thread',
            'unable to create thread',
            'unable to create native thread',
            'unable to create new native thread',
            'exception in thread "craft async scheduler management thread"',
        ];

        const listener = (line: string) => {
            if (errors.some((p) => line.toLowerCase().includes(p))) {
                setVisible(true);
            }
        };

        instance.addListener(SocketEvent.CONSOLE_OUTPUT, listener);

        return () => {
            instance.removeListener(SocketEvent.CONSOLE_OUTPUT, listener);
        };
    }, [connected, instance, status]);

    useEffect(() => {
        clearFlashes('feature:pidLimit');
    }, []);

    return (
        <Modal
            visible={visible}
            onDismissed={() => setVisible(false)}
            closeOnBackground={false}
            showSpinnerOverlay={loading}
        >
            <FlashMessageRender key={'feature:pidLimit'} css={tw`mb-4`} />
            {isAdmin ? (
                <>
                    <div css={tw`mt-4 sm:flex items-center`}>
                        <FontAwesomeIcon css={tw`pr-4`} icon={faExclamationTriangle} color={'orange'} size={'4x'} />
                        <h2 css={tw`text-2xl mb-4 font-bold`} style={{ color: 'var(--neo-text)' }}>Memory or process limit reached...</h2>
                    </div>
                    <p css={tw`mt-4`} style={{ color: 'var(--neo-text)' }}>This server has reached the maximum process or memory limit.</p>
                    <p css={tw`mt-4`} style={{ color: 'var(--neo-text)' }}>
                        Increasing{' '}
                        <code
                            css={tw`font-mono rounded py-0.5 px-1.5`}
                            style={{
                                backgroundColor: 'var(--neo-surface-light)',
                                color: 'var(--neo-text)',
                                border: '1.5px solid #000000',
                            }}
                        >
                            container_pid_limit
                        </code>{' '}
                        in the wings configuration,{' '}
                        <code
                            css={tw`font-mono rounded py-0.5 px-1.5`}
                            style={{
                                backgroundColor: 'var(--neo-surface-light)',
                                color: 'var(--neo-text)',
                                border: '1.5px solid #000000',
                            }}
                        >
                            config.yml
                        </code>
                        , might help resolve this issue.
                    </p>
                    <p css={tw`mt-4`} style={{ color: 'var(--neo-text)' }}>
                        <b>Note: Wings must be restarted for the configuration file changes to take effect</b>
                    </p>
                    <div css={tw`mt-8 sm:flex items-center justify-end`}>
                        <Button onClick={() => setVisible(false)} css={tw`w-full sm:w-auto border-transparent`}>
                            Close
                        </Button>
                    </div>
                </>
            ) : (
                <>
                    <div css={tw`mt-4 sm:flex items-center`}>
                        <FontAwesomeIcon css={tw`pr-4`} icon={faExclamationTriangle} color={'orange'} size={'4x'} />
                        <h2 css={tw`text-2xl mb-4 font-bold`} style={{ color: 'var(--neo-text)' }}>Possible resource limit reached...</h2>
                    </div>
                    <p css={tw`mt-4`} style={{ color: 'var(--neo-text)' }}>
                        This server is attempting to use more resources than allocated. Please contact the administrator
                        and give them the error below.
                    </p>
                    <p css={tw`mt-4`}>
                        <code
                            css={tw`font-mono rounded p-2 block`}
                            style={{
                                backgroundColor: 'var(--neo-surface-light)',
                                color: 'var(--neo-text)',
                                border: '1.5px solid #000000',
                            }}
                        >
                            pthread_create failed, Possibly out of memory or process/resource limits reached
                        </code>
                    </p>
                    <div css={tw`mt-8 sm:flex items-center justify-end`}>
                        <Button onClick={() => setVisible(false)} css={tw`w-full sm:w-auto border-transparent`}>
                            Close
                        </Button>
                    </div>
                </>
            )}
        </Modal>
    );
};

export default PIDLimitModalFeature;
