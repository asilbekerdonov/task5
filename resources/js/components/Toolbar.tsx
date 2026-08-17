import React, { useState, useEffect, useRef } from 'react';
import { useGenerationParams } from '../context/GenerationParamsContext';

const LOCALES = [
    { code: 'ru_RU', label: 'Русский (RU)' },
    { code: 'en_US', label: 'English (US)' },
    { code: 'uz_UZ', label: "O'zbekcha (UZ)" },
];

export default function Toolbar() {
    const { params, setLocale, setSeed, randomizeSeed, setLikes, setReviews, setView } =
        useGenerationParams();

    const [seedInput, setSeedInput] = useState(String(params.seed));
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    useEffect(() => {
        setSeedInput(String(params.seed));
    }, [params.seed]);

    const handleSeedChange = (value: string) => {
        setSeedInput(value);
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(() => {
            const parsed = parseInt(value, 10);
            if (!isNaN(parsed) && parsed >= 0) {
                setSeed(parsed);
            }
        }, 300);
    };

    return (
        <div className="flex flex-wrap items-center gap-6 px-8 py-4 border-b border-black/10">
            {/* Language */}
            <div className="flex flex-col gap-1">
                <label className="text-xs text-black/50">Language</label>
                <select
                    value={params.locale}
                    onChange={(e) => setLocale(e.target.value)}
                    className="border border-black/20 rounded-full px-4 py-1.5 text-sm bg-white"
                >
                    {LOCALES.map((l) => (
                        <option key={l.code} value={l.code}>
                            {l.label}
                        </option>
                    ))}
                </select>
            </div>

            {/* Seed */}
            <div className="flex flex-col gap-1">
                <label className="text-xs text-black/50">Seed</label>
                <div className="flex items-center gap-2">
                    <input
                        type="text"
                        value={seedInput}
                        onChange={(e) => handleSeedChange(e.target.value)}
                        className="border border-black/20 rounded-full px-4 py-1.5 text-sm w-40"
                    />
                    <button
                        onClick={randomizeSeed}
                        className="border border-black rounded-full px-3 py-1.5 text-sm hover:bg-black hover:text-white transition-colors"
                        title="Randomize seed"
                    >
                        🎲
                    </button>
                </div>
            </div>

            {/* Likes */}
            <div className="flex flex-col gap-1">
                <label className="text-xs text-black/50">Likes / movie: {params.likes.toFixed(1)}</label>
                <input
                    type="range"
                    min={0}
                    max={10}
                    step={0.1}
                    value={params.likes}
                    onChange={(e) => setLikes(parseFloat(e.target.value))}
                    className="w-40"
                />
            </div>

            {/* Reviews */}
            <div className="flex flex-col gap-1">
                <label className="text-xs text-black/50">Reviews / movie: {params.reviews.toFixed(1)}</label>
                <input
                    type="range"
                    min={0}
                    max={10}
                    step={0.1}
                    value={params.reviews}
                    onChange={(e) => setReviews(parseFloat(e.target.value))}
                    className="w-40"
                />
            </div>

            {/* View switcher */}
            <div className="flex gap-1 ml-auto">
                <button
                    onClick={() => setView('table')}
                    className={`rounded-full px-4 py-1.5 text-sm border transition-colors ${
                        params.view === 'table'
                            ? 'bg-black text-white border-black'
                            : 'bg-white text-black border-black/20'
                    }`}
                >
                    Table
                </button>
                <button
                    onClick={() => setView('gallery')}
                    className={`rounded-full px-4 py-1.5 text-sm border transition-colors ${
                        params.view === 'gallery'
                            ? 'bg-black text-white border-black'
                            : 'bg-white text-black border-black/20'
                    }`}
                >
                    Gallery
                </button>
            </div>
        </div>
    );
}