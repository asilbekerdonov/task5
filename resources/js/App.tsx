import React from 'react';
import { GenerationParamsProvider, useGenerationParams } from './context/GenerationParamsContext';
import { TrailerProvider } from './context/TrailerContext';
import Toolbar from './components/Toolbar';
import MovieTable from './components/MovieTable';
import GalleryView from './components/GalleryView';
import TrailerPlayer from './components/TrailerPlayer';

function AppContent() {
    const { params } = useGenerationParams();

    return (
        <div className="min-h-screen bg-white text-black">
            <header className="px-8 py-5 border-b border-black/10">
                <h1 className="text-2xl font-bold tracking-tight">Movie Store Showcase</h1>
            </header>
            <Toolbar />
            <main className="px-8 py-6">
                {params.view === 'table' ? <MovieTable /> : <GalleryView />}
            </main>
            <TrailerPlayer />
        </div>
    );
}

export default function App() {
    return (
        <GenerationParamsProvider>
            <TrailerProvider>
                <AppContent />
            </TrailerProvider>
        </GenerationParamsProvider>
    );
}