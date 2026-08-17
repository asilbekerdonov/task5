import React, { createContext, useContext, useState, ReactNode } from 'react';
import { Movie } from '../types/movie';

interface TrailerContextValue {
    playingMovie: Movie | null;
    playTrailer: (movie: Movie) => void;
    closeTrailer: () => void;
}

const TrailerContext = createContext<TrailerContextValue | undefined>(undefined);

export function TrailerProvider({ children }: { children: ReactNode }) {
    const [playingMovie, setPlayingMovie] = useState<Movie | null>(null);
    const playTrailer = (movie: Movie) => setPlayingMovie(movie);
    const closeTrailer = () => setPlayingMovie(null);
    return (
        <TrailerContext.Provider value={{ playingMovie, playTrailer, closeTrailer }}>
            {children}
        </TrailerContext.Provider>
    );
}

export function useTrailer(): TrailerContextValue {
    const ctx = useContext(TrailerContext);
    if (!ctx) throw new Error('useTrailer must be used within TrailerProvider');
    return ctx;
}