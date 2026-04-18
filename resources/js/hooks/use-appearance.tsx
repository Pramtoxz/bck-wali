import { useEffect, useState } from 'react';

export type Appearance = 'light';

const applyTheme = () => {
    document.documentElement.classList.remove('dark');
};

export function initializeTheme() {
    applyTheme();
}

export function useAppearance() {
    const [appearance] = useState<Appearance>('light');

    useEffect(() => {
        applyTheme();
    }, []);

    return { appearance };
}
