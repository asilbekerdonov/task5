import React, { useEffect, useRef, useState } from 'react';
import { useTrailer } from '../context/TrailerContext';

const COLOR_FILTERS: Record<string, string> = {
    warm: 'sepia(0.35) saturate(1.4) hue-rotate(-10deg)',
    cold: 'saturate(1.2) hue-rotate(180deg) brightness(0.95)',
    'high-contrast': 'contrast(1.5) saturate(1.15)',
    desaturated: 'grayscale(0.6) contrast(1.1)',
    none: 'none',
};

const TRANSITION_CLASSES: Record<string, string> = {
    fade: 'trailer-transition-fade',
    cut: 'trailer-transition-cut',
    'zoom-blur': 'trailer-transition-zoom-blur',
    'wipe-left': 'trailer-transition-wipe-left',
    flash: 'trailer-transition-flash',
};

const TEXT_ANIMATION_CLASSES: Record<string, string> = {
    'slide-in': 'trailer-text-slide-in',
    'fade-scale': 'trailer-text-fade-scale',
    typewriter: 'trailer-text-typewriter',
    'glitch-reveal': 'trailer-text-glitch-reveal',
    'zoom-blast': 'trailer-text-zoom-blast',
};

type Phase = 'playing' | 'freeze';

export default function TrailerPlayer() {
    const { playingMovie, closeTrailer } = useTrailer();
    const [clipIndex, setClipIndex] = useState(0);
    const [phase, setPhase] = useState<Phase>('playing');
    const [transitionClass, setTransitionClass] = useState<string>('');
    const [showTitle, setShowTitle] = useState(false);
    const [showTagline, setShowTagline] = useState(false);
    const videoRef = useRef<HTMLVideoElement | null>(null);
    const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    const trailer = playingMovie?.trailer ?? null;

    // Сброс состояния при открытии нового фильма
    useEffect(() => {
        setClipIndex(0);
        setPhase('playing');
        setTransitionClass('');
        setShowTitle(true);
        setShowTagline(false);
    }, [playingMovie]);

    // Таймер показа title
    useEffect(() => {
        if (!playingMovie) return;
        setShowTitle(true);
        const t = setTimeout(() => setShowTitle(false), 2000);
        return () => clearTimeout(t);
    }, [playingMovie]);

    // Таймер показа tagline (после title)
    useEffect(() => {
        if (!playingMovie || !trailer?.taglineAnimation) {
            setShowTagline(false);
            return;
        }
        const t1 = setTimeout(() => setShowTagline(true), 2000);
        const t2 = setTimeout(() => setShowTagline(false), 4000);
        return () => {
            clearTimeout(t1);
            clearTimeout(t2);
        };
    }, [playingMovie, trailer?.taglineAnimation]);

    // Основной таймер проигрывания клипов
    useEffect(() => {
        if (!trailer || phase !== 'playing') return;
        const perClipMs = (trailer.duration / trailer.clips.length) * 1000;

        timerRef.current = setTimeout(() => {
            if (clipIndex < trailer.clips.length - 1) {
                const transitionType = trailer.transitions[clipIndex] ?? 'cut';
                setTransitionClass(TRANSITION_CLASSES[transitionType]);
                setClipIndex((i) => i + 1);
            } else {
                setPhase('freeze');
            }
        }, perClipMs);

        return () => {
            if (timerRef.current) clearTimeout(timerRef.current);
        };
    }, [trailer, clipIndex, phase]);

    // Применить zoom/speed/colorFilter к video при смене клипа
    useEffect(() => {
        if (!trailer || !videoRef.current) return;
        const clip = trailer.clips[clipIndex];
        const video = videoRef.current;
        video.playbackRate = clip.speed;
        video.style.filter = COLOR_FILTERS[clip.colorFilter] ?? 'none';
        video.style.transform = `scale(${clip.zoom})`;
        video.currentTime = 0;
        video.play().catch(() => {
            // autoplay может быть заблокирован
        });
    }, [trailer, clipIndex]);

    // Убрать transition-класс после анимации
    useEffect(() => {
        if (!transitionClass) return;
        const t = setTimeout(() => setTransitionClass(''), 600);
        return () => clearTimeout(t);
    }, [transitionClass]);

    if (!playingMovie || !trailer) return null;

    const currentClip = trailer.clips[clipIndex];
    const freezeClip = trailer.clips.find((c) => c.clipId === trailer.freezeFrame.clipId) ?? trailer.clips[0];
    const titleClass = TEXT_ANIMATION_CLASSES[trailer.titleAnimation.type];
    const taglineClass = trailer.taglineAnimation ? TEXT_ANIMATION_CLASSES[trailer.taglineAnimation.type] : '';

    return (
        <div className="fixed inset-0 bg-black/90 z-50 flex items-center justify-center" onClick={closeTrailer}>
            <style>{`
                .trailer-transition-fade { animation: fadeIn 0.5s ease; }
                @keyframes fadeIn { from{opacity:0} to{opacity:1} }
                .trailer-transition-cut { }
                .trailer-transition-zoom-blur { animation: zoomBlurIn 0.5s ease; }
                @keyframes zoomBlurIn { from{transform:scale(1.5); filter:blur(8px)} to{transform:scale(1); filter:blur(0)} }
                .trailer-transition-wipe-left { animation: wipeLeft 0.5s ease; }
                @keyframes wipeLeft { from{clip-path: inset(0 100% 0 0)} to{clip-path: inset(0 0 0 0)} }
                .trailer-transition-flash { animation: flash 0.3s ease; }
                @keyframes flash { 0%{filter:brightness(3)} 100%{filter:brightness(1)} }
                .trailer-text-slide-in { animation: slideIn 0.6s ease; }
                @keyframes slideIn { from{transform:translateX(-100px); opacity:0} to{transform:translateX(0); opacity:1} }
                .trailer-text-fade-scale { animation: fadeScale 0.6s ease; }
                @keyframes fadeScale { from{opacity:0; transform:scale(0.8)} to{opacity:1; transform:scale(1)} }
                .trailer-text-typewriter { animation: typewriterFade 0.8s steps(20); }
                @keyframes typewriterFade { from{opacity:0} to{opacity:1} }
                .trailer-text-glitch-reveal { animation: glitchReveal 0.4s steps(4); }
                @keyframes glitchReveal { 0%{opacity:0; transform:translateX(-5px)} 50%{opacity:0.5; transform:translateX(5px)} 100%{opacity:1; transform:translateX(0)} }
                .trailer-text-zoom-blast { animation: zoomBlast 0.6s ease; }
                @keyframes zoomBlast { from{transform:scale(2); opacity:0} to{transform:scale(1); opacity:1} }
            `}</style>

            <div className="relative w-full max-w-3xl aspect-video bg-black overflow-hidden rounded-lg" onClick={(e) => e.stopPropagation()}>
                {phase === 'playing' ? (
                    <video
                        ref={videoRef}
                        key={currentClip.clipId}
                        src={currentClip.source}
                        muted
                        playsInline
                        className={`w-full h-full object-cover ${transitionClass}`}
                    />
                ) : (
                    <video
                        src={freezeClip.source}
                        muted
                        playsInline
                        autoPlay={false}
                        className="w-full h-full object-cover"
                        ref={(el) => { if (el) { el.currentTime = 0; el.pause(); } }}
                    />
                )}

                {showTitle && (
                    <div className={`absolute inset-0 flex items-center justify-center pointer-events-none ${titleClass}`}>
                        <h2 className="text-white text-4xl font-bold drop-shadow-lg">{playingMovie.title}</h2>
                    </div>
                )}

                {showTagline && trailer.taglineAnimation && (
                    <div className={`absolute bottom-8 inset-x-0 flex items-center justify-center pointer-events-none ${taglineClass}`}>
                        <p className="text-white text-xl font-semibold tracking-widest drop-shadow-lg">
                            {trailer.taglineAnimation.text}
                        </p>
                    </div>
                )}

                <button
                    onClick={closeTrailer}
                    className="absolute top-4 right-4 text-white bg-black/50 rounded-full w-8 h-8 flex items-center justify-center hover:bg-black/80"
                >
                    ✕
                </button>
            </div>
        </div>
    );
}