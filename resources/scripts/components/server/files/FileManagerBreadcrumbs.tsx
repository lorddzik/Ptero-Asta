import React, { useEffect, useState } from 'react';
import { ServerContext } from '@/state/server';
import { NavLink, useLocation } from 'react-router-dom';
import { encodePathSegments, hashToPath } from '@/helpers';
import tw from 'twin.macro';

interface Props {
    renderLeft?: JSX.Element;
    withinFileEditor?: boolean;
    isNewFile?: boolean;
}

export default ({ renderLeft, withinFileEditor, isNewFile }: Props) => {
    const [file, setFile] = useState<string | null>(null);
    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const { hash } = useLocation();

    useEffect(() => {
        const path = hashToPath(hash);

        if (withinFileEditor && !isNewFile) {
            const name = path.split('/').pop() || null;
            setFile(name);
        }
    }, [withinFileEditor, isNewFile, hash]);

    const breadcrumbs = (): { name: string; path?: string }[] =>
        directory
            .split('/')
            .filter((directory) => !!directory)
            .map((directory, index, dirs) => {
                if (!withinFileEditor && index === dirs.length - 1) {
                    return { name: directory };
                }

                return { name: directory, path: `/${dirs.slice(0, index + 1).join('/')}` };
            });

    return (
        <div css={tw`flex flex-grow-0 items-center text-sm overflow-x-hidden font-mono`} style={{ color: 'var(--neo-text-muted)' }}>
            {renderLeft || <div css={tw`w-12`} />}/<span css={tw`px-1 font-bold`} style={{ color: 'var(--neo-text)' }}>home</span>/
            <NavLink to={`/server/${id}/files`} css={tw`px-1 no-underline font-bold hover:underline`} style={{ color: 'var(--asta-cyan, #00D2FF)' }}>
                container
            </NavLink>
            /
            {breadcrumbs().map((crumb, index) =>
                crumb.path ? (
                    <React.Fragment key={index}>
                        <NavLink
                            to={`/server/${id}/files#${encodePathSegments(crumb.path)}`}
                            css={tw`px-1 no-underline font-bold hover:underline`}
                            style={{ color: 'var(--neo-text)' }}
                        >
                            {crumb.name}
                        </NavLink>
                        /
                    </React.Fragment>
                ) : (
                    <span key={index} css={tw`px-1 font-bold`} style={{ color: 'var(--neo-text)' }}>
                        {crumb.name}
                    </span>
                )
            )}
            {file && (
                <React.Fragment>
                    <span css={tw`px-1 font-bold`} style={{ color: 'var(--neo-text)' }}>{file}</span>
                </React.Fragment>
            )}
        </div>
    );
};
