import React from 'react';
import tw from 'twin.macro';
import Icon from '@/components/elements/Icon';
import { faExclamationTriangle } from '@fortawesome/free-solid-svg-icons';

interface State {
    hasError: boolean;
}

// eslint-disable-next-line @typescript-eslint/ban-types
class ErrorBoundary extends React.Component<{}, State> {
    state: State = {
        hasError: false,
    };

    static getDerivedStateFromError() {
        return { hasError: true };
    }

    componentDidCatch(error: Error) {
        console.error(error);
    }

    render() {
        return this.state.hasError ? (
            <div css={tw`flex items-center justify-center w-full my-4`}>
                <div
                    css={tw`flex items-center rounded p-3`}
                    style={{
                        backgroundColor: 'var(--neo-surface-light)',
                        border: '2px solid #000000',
                        boxShadow: '3px 3px 0px #000000',
                        color: 'var(--neo-text)',
                    }}
                >
                    <Icon icon={faExclamationTriangle} css={tw`h-4 w-auto mr-2 text-red-500`} />
                    <p css={tw`text-sm font-medium`} style={{ color: 'var(--neo-text)' }}>
                        An error was encountered by the application while rendering this view. Try refreshing the page.
                    </p>
                </div>
            </div>
        ) : (
            this.props.children
        );
    }
}

export default ErrorBoundary;
