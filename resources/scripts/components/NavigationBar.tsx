import * as React from 'react';
import { useState } from 'react';
import { Link, NavLink } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCogs, faLayerGroup, faSignOutAlt, faSun, faMoon } from '@fortawesome/free-solid-svg-icons';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import SearchContainer from '@/components/dashboard/search/SearchContainer';
import tw from 'twin.macro';
import styled from 'styled-components/macro';
import http from '@/api/http';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import Tooltip from '@/components/elements/tooltip/Tooltip';
import Avatar from '@/components/Avatar';

const RightNavigation = styled.div`
    & > a,
    & > button,
    & > .navigation-link {
        ${tw`flex items-center h-full no-underline px-6 cursor-pointer font-bold`};
        color: var(--neo-text-muted);
        transition: all 0.1s ease;

        &:hover {
            color: var(--neo-text);
            background-color: var(--neo-surface-light);
        }

        &:active,
        &.active {
            color: #000000 !important;
            background-color: #00D2FF !important;
            box-shadow: inset 0 -3px #000000;
        }
    }
`;

export default () => {
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);
    const rootAdmin = useStoreState((state: ApplicationStore) => state.user.data!.rootAdmin);
    const [isLoggingOut, setIsLoggingOut] = useState(false);
    const [currentTheme, setCurrentTheme] = useState<'dark' | 'light'>(() => {
        if (typeof window !== 'undefined') {
            return (localStorage.getItem('asta_theme') as 'dark' | 'light') || 'dark';
        }
        return 'dark';
    });

    React.useEffect(() => {
        if (currentTheme === 'light') {
            document.documentElement.classList.add('theme-light');
            document.documentElement.setAttribute('data-theme', 'light');
            document.body.classList.add('theme-light');
        } else {
            document.documentElement.classList.remove('theme-light');
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body.classList.remove('theme-light');
        }
    }, [currentTheme]);

    const toggleTheme = () => {
        const next = currentTheme === 'dark' ? 'light' : 'dark';
        setCurrentTheme(next);
        localStorage.setItem('asta_theme', next);
    };

    const onTriggerLogout = () => {
        setIsLoggingOut(true);
        http.post('/auth/logout').finally(() => {
            // @ts-expect-error this is valid
            window.location = '/';
        });
    };

    return (
        <div className={'w-full border-b-[2.5px] border-black shadow-[0_4px_0_#000000] z-30 sticky top-0 overflow-x-auto'} style={{ backgroundColor: 'var(--neo-surface)' }}>
            <SpinnerOverlay visible={isLoggingOut} />
            <div className={'mx-auto w-full flex items-center h-[3.75rem] max-w-[1200px] px-2'}>
                <div id={'logo'} className={'flex-1 flex items-center'}>
                    <Link
                        to={'/'}
                        className={
                            'flex items-center px-4 no-underline group'
                        }
                    >
                        <img
                            src={'/assets/svgs/asta.svg'}
                            alt={'Asta'}
                            className={'w-8 h-8 mr-3 object-cover rounded-full border-[1.5px] border-black'}
                            style={{ objectPosition: '50% 25%', filter: 'drop-shadow(2px 2px 0px #000000)' }}
                        />
                        <span className={'text-xl font-header font-black tracking-wider uppercase group-hover:text-[#00D2FF] transition-colors duration-100'} style={{ color: 'var(--neo-text)' }}>
                            {name || 'Asta Panel'}
                        </span>
                    </Link>
                </div>
                <RightNavigation className={'flex h-full items-center justify-center'}>
                    <SearchContainer />
                    <Tooltip placement={'bottom'} content={'Dashboard'}>
                        <NavLink to={'/'} exact>
                            <FontAwesomeIcon icon={faLayerGroup} />
                        </NavLink>
                    </Tooltip>
                    {rootAdmin && (
                        <Tooltip placement={'bottom'} content={'Admin'}>
                            <a href={'/admin'} rel={'noreferrer'}>
                                <FontAwesomeIcon icon={faCogs} />
                            </a>
                        </Tooltip>
                    )}
                    <Tooltip placement={'bottom'} content={currentTheme === 'dark' ? 'Light Mode' : 'Dark Mode'}>
                        <button onClick={toggleTheme} aria-label={'Toggle Theme'}>
                            <FontAwesomeIcon icon={currentTheme === 'dark' ? faSun : faMoon} />
                        </button>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={'Account Settings'}>
                        <NavLink to={'/account'}>
                            <span className={'flex items-center w-5 h-5'}>
                                <Avatar.User />
                            </span>
                        </NavLink>
                    </Tooltip>
                    <Tooltip placement={'bottom'} content={'Sign Out'}>
                        <button onClick={onTriggerLogout}>
                            <FontAwesomeIcon icon={faSignOutAlt} />
                        </button>
                    </Tooltip>
                </RightNavigation>
            </div>
        </div>
    );
};
