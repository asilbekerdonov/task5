import React from 'react';
import { GenerationParamsProvider } from './context/GenerationParamsContext';
import Toolbar from './components/Toolbar';

export default function App() {
    return (
        <GenerationParamsProvider>
            <div className="min-h-screen bg-white text-black">
                <header className="px-8 py-5 border-b border-black/10">
                    <h1 className="text-2xl font-bold tracking-tight">Movie Store Showcase</h1>
                </header>
                <Toolbar />
                <main className="px-8 py-6">
                    <p>Movie table coming next.</p>
                </main>
            </div>
        </GenerationParamsProvider>
    );
}