import React, { useEffect, useRef, useState, useCallback } from 'react';
import { useGenerationParams } from '../context/GenerationParamsContext';
import { fetchMovies } from '../api/movies';
import { Movie } from '../types/movie';
import MovieCard from './MovieCard';

export default function GalleryView() {
    const { params } = useGenerationParams();
    const [movies, setMovies] = useState<Movie[]>([]);
    const [localPage, setLocalPage] = useState(1);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const sentinelRef = useRef<HTMLDivElement | null>(null);

    // ЭФФЕКТ 1: сброс при смене параметров генерации
    useEffect(() => {
        setMovies([]);
        setLocalPage(1);
        setError(null);
        window.scrollTo({ top: 0, behavior: 'auto' });
    }, [params.locale, params.seed, params.likes, params.reviews]);

    // ЭФФЕКТ 2: загрузка страницы localPage
    useEffect(() => {
        let cancelled = false;
        setLoading(true);
        setError(null);
        
        fetchMovies({
            locale: params.locale,
            seed: params.seed,
            likes: params.likes,
            reviews: params.reviews,
            page: localPage,
            per_page: params.perPage,
        })
            .then((res) => {
                if (!cancelled) setMovies((prev) => [...prev, ...res.data]);
            })
            .catch((err) => {
                if (!cancelled) setError(err.message);
            })
            .finally(() => {
                if (!cancelled) setLoading(false);
            });
            
        return () => {
            cancelled = true;
        };
    }, [localPage, params.locale, params.seed, params.likes, params.reviews, params.perPage]);

    // IntersectionObserver для бесконечного скролла
    const loadMore = useCallback(() => {
        if (!loading) {
            setLocalPage((p) => p + 1);
        }
    }, [loading]);

    useEffect(() => {
        const sentinel = sentinelRef.current;
        if (!sentinel) return;

        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting && !loading) {
                    loadMore();
                }
            },
            {
                root: null, // скроллится весь window
                threshold: 0.1,
            }
        );

        observer.observe(sentinel);

        return () => {
            observer.disconnect();
        };
    }, [loadMore]);

    return (
        <div className="mt-6">
            {error && (
                <div className="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    {error}
                </div>
            )}

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                {movies.map((movie) => (
                    <MovieCard key={`${movie.index}-${params.seed}-${params.locale}`} movie={movie} />
                ))}
            </div>

            {/* Сентинел для подгрузки */}
            <div ref={sentinelRef} className="h-4" />

            {loading && (
                <div className="text-center py-4 text-sm text-black/50">
                    Loading more...
                </div>
            )}
        </div>
    );
}