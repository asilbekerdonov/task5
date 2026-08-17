import React, { createContext, useContext, useState, ReactNode } from 'react';

export type ViewMode = 'table' | 'gallery';

export interface GenerationParams {
    locale: string;
    seed: number;
    likes: number;
    reviews: number;
    page: number;
    perPage: number;
    view: ViewMode;
}

interface GenerationParamsContextValue {
    params: GenerationParams;
    setLocale: (locale: string) => void;
    setSeed: (seed: number) => void;
    randomizeSeed: () => void;
    setLikes: (likes: number) => void;
    setReviews: (reviews: number) => void;
    setPage: (page: number) => void;
    setView: (view: ViewMode) => void;
}

const DEFAULT_PARAMS: GenerationParams = {
    locale: 'ru_RU',
    seed: 42,
    likes: 3.7,
    reviews: 2.5,
    page: 1,
    perPage: 20,
    view: 'table',
};

const GenerationParamsContext = createContext<GenerationParamsContextValue | undefined>(undefined);

export function GenerationParamsProvider({ children }: { children: ReactNode }) {
    const [params, setParams] = useState<GenerationParams>(DEFAULT_PARAMS);

    const setLocale = (locale: string) =>
        setParams((p) => ({ ...p, locale, page: 1 }));

    const setSeed = (seed: number) =>
        setParams((p) => ({ ...p, seed, page: 1 }));

    const randomizeSeed = () => {
        const randomSeed = Math.floor(Math.random() * Number.MAX_SAFE_INTEGER) % 281474976710655;
        setParams((p) => ({ ...p, seed: randomSeed, page: 1 }));
    };

    const setLikes = (likes: number) =>
        setParams((p) => ({ ...p, likes, page: 1 }));

    const setReviews = (reviews: number) =>
        setParams((p) => ({ ...p, reviews, page: 1 }));

    const setPage = (page: number) =>
        setParams((p) => ({ ...p, page }));

    const setView = (view: ViewMode) =>
        setParams((p) => ({ ...p, view, page: 1 }));

    return (
        <GenerationParamsContext.Provider
            value={{ params, setLocale, setSeed, randomizeSeed, setLikes, setReviews, setPage, setView }}
        >
            {children}
        </GenerationParamsContext.Provider>
    );
}

export function useGenerationParams(): GenerationParamsContextValue {
    const ctx = useContext(GenerationParamsContext);
    if (!ctx) {
        throw new Error('useGenerationParams must be used within GenerationParamsProvider');
    }
    return ctx;
}