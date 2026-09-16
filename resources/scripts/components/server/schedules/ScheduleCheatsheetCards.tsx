import React from 'react';
import tw from 'twin.macro';

export default () => {
    return (
        <>
            <div
                css={tw`md:w-1/2 h-full rounded`}
                style={{
                    backgroundColor: 'var(--neo-surface)',
                    border: '2px solid #000000',
                    boxShadow: '3px 3px 0px #000000',
                    color: 'var(--neo-text)',
                    overflow: 'hidden',
                }}
            >
                <div css={tw`flex flex-col`}>
                    <h2
                        css={tw`py-4 px-6 font-extrabold uppercase`}
                        style={{ backgroundColor: 'var(--neo-surface-light)', borderBottom: '2px solid #000000' }}
                    >
                        Examples
                    </h2>
                    <div css={tw`flex py-3 px-6`} style={{ backgroundColor: 'var(--neo-surface-light)' }}>
                        <div css={tw`w-1/2 font-mono font-bold`}>*/5 * * * *</div>
                        <div css={tw`w-1/2`}>every 5 minutes</div>
                    </div>
                    <div css={tw`flex py-3 px-6`}>
                        <div css={tw`w-1/2 font-mono font-bold`}>0 */1 * * *</div>
                        <div css={tw`w-1/2`}>every hour</div>
                    </div>
                    <div css={tw`flex py-3 px-6`} style={{ backgroundColor: 'var(--neo-surface-light)' }}>
                        <div css={tw`w-1/2 font-mono font-bold`}>0 8-12 * * *</div>
                        <div css={tw`w-1/2`}>hour range</div>
                    </div>
                    <div css={tw`flex py-3 px-6`}>
                        <div css={tw`w-1/2 font-mono font-bold`}>0 0 * * *</div>
                        <div css={tw`w-1/2`}>once a day</div>
                    </div>
                    <div css={tw`flex py-3 px-6`} style={{ backgroundColor: 'var(--neo-surface-light)' }}>
                        <div css={tw`w-1/2 font-mono font-bold`}>0 0 * * MON</div>
                        <div css={tw`w-1/2`}>every Monday</div>
                    </div>
                </div>
            </div>
            <div
                css={tw`md:w-1/2 h-full rounded`}
                style={{
                    backgroundColor: 'var(--neo-surface)',
                    border: '2px solid #000000',
                    boxShadow: '3px 3px 0px #000000',
                    color: 'var(--neo-text)',
                    overflow: 'hidden',
                }}
            >
                <h2
                    css={tw`py-4 px-6 font-extrabold uppercase`}
                    style={{ backgroundColor: 'var(--neo-surface-light)', borderBottom: '2px solid #000000' }}
                >
                    Special Characters
                </h2>
                <div css={tw`flex flex-col`}>
                    <div css={tw`flex py-3 px-6`} style={{ backgroundColor: 'var(--neo-surface-light)' }}>
                        <div css={tw`w-1/2 font-mono font-bold`}>*</div>
                        <div css={tw`w-1/2`}>any value</div>
                    </div>
                    <div css={tw`flex py-3 px-6`}>
                        <div css={tw`w-1/2 font-mono font-bold`}>,</div>
                        <div css={tw`w-1/2`}>value list separator</div>
                    </div>
                    <div css={tw`flex py-3 px-6`} style={{ backgroundColor: 'var(--neo-surface-light)' }}>
                        <div css={tw`w-1/2 font-mono font-bold`}>-</div>
                        <div css={tw`w-1/2`}>range values</div>
                    </div>
                    <div css={tw`flex py-3 px-6`}>
                        <div css={tw`w-1/2 font-mono font-bold`}>/</div>
                        <div css={tw`w-1/2`}>step values</div>
                    </div>
                </div>
            </div>
        </>
    );
};
