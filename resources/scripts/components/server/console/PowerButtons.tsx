import React, { useEffect, useState } from 'react';
import { Button } from '@/components/elements/button/index';
import Can from '@/components/elements/Can';
import { ServerContext } from '@/state/server';
import { PowerAction } from '@/components/server/console/ServerConsoleContainer';
import { Dialog } from '@/components/elements/dialog';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faPlay, faRedo, faSkull, faStop } from '@fortawesome/free-solid-svg-icons';

interface PowerButtonProps {
    className?: string;
}

export default ({ className }: PowerButtonProps) => {
    const [open, setOpen] = useState(false);
    const status = ServerContext.useStoreState((state) => state.status.value);
    const instance = ServerContext.useStoreState((state) => state.socket.instance);

    const killable = status === 'stopping';
    const onButtonClick = (
        action: PowerAction | 'kill-confirmed',
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>
    ): void => {
        e.preventDefault();
        if (action === 'kill') {
            return setOpen(true);
        }

        if (instance) {
            setOpen(false);
            instance.send('set state', action === 'kill-confirmed' ? 'kill' : action);
        }
    };

    useEffect(() => {
        if (status === 'offline') {
            setOpen(false);
        }
    }, [status]);

    return (
        <div className={className}>
            <Dialog.Confirm
                open={open}
                hideCloseIcon
                onClose={() => setOpen(false)}
                title={'Forcibly Stop Process'}
                confirm={'Continue'}
                onConfirmed={onButtonClick.bind(this, 'kill-confirmed')}
            >
                Forcibly stopping a server can lead to data corruption.
            </Dialog.Confirm>
            <Can action={'control.start'}>
                <Button
                    className={'flex-1'}
                    disabled={status !== 'offline'}
                    onClick={onButtonClick.bind(this, 'start')}
                    style={status === 'offline' ? { backgroundColor: '#10B981', color: '#000000', borderColor: '#000000' } : undefined}
                >
                    <FontAwesomeIcon icon={faPlay} className={'mr-1.5 text-xs'} />
                    Start
                </Button>
            </Can>
            <Can action={'control.restart'}>
                <Button.Text
                    className={'flex-1'}
                    disabled={!status}
                    onClick={onButtonClick.bind(this, 'restart')}
                    style={status ? { backgroundColor: '#FACC15', color: '#000000', borderColor: '#000000' } : undefined}
                >
                    <FontAwesomeIcon icon={faRedo} className={'mr-1.5 text-xs'} />
                    Restart
                </Button.Text>
            </Can>
            <Can action={'control.stop'}>
                <Button.Danger
                    className={'flex-1'}
                    disabled={status === 'offline'}
                    onClick={onButtonClick.bind(this, killable ? 'kill' : 'stop')}
                    style={status !== 'offline' ? { backgroundColor: killable ? '#DC2626' : '#EF4444', color: '#FFFFFF', borderColor: '#000000' } : undefined}
                >
                    <FontAwesomeIcon icon={killable ? faSkull : faStop} className={'mr-1.5 text-xs'} />
                    {killable ? 'Kill' : 'Stop'}
                </Button.Danger>
            </Can>
        </div>
    );
};
