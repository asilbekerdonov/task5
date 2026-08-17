import React, { useEffect, useState } from 'react';
import { useGenerationParams } from '../context/GenerationParamsContext';
import { fetchMovies } from '../api/movies';
import { Movie } from '../types/movie';
import Pagination from './Pagination';
import { useTrailer } from '../context/TrailerContext';

export default function MovieTable() {
    const { params, setPage } = useGenerationParams();
    const [movies, setMovies] = useState<Movie[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [expandedIndex, setExpandedIndex] = useState<number | null>(null);

    useEffect(() => {
        let cancelled = false;
        setLoading(true);
        setError(null);
        
        fetchMovies({
            locale: params.locale,
            seed: params.seed,
            likes: params.likes,
            reviews: params.reviews,
            page: params.page,
            per_page: params.perPage,
        })
            .then((res) => {
                if (!cancelled) setMovies(res.data);
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
    }, [params.locale, params.seed, params.likes, params.reviews, params.page, params.perPage]);

    const toggleExpand = (index: number) => {
        setExpandedIndex(expandedIndex === index ? null : index);
    };

    const { playTrailer } = useTrailer();

    return (
        <div className="relative mt-6">
            {error && (
                <div className="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                    {error}
                </div>
            )}

            {loading && (
                <div className="absolute top-0 right-0 bg-white/80 px-4 py-2 text-sm text-black/50 rounded-bl-lg">
                    Loading...
                </div>
            )}

            <div className="overflow-x-auto">
                <table className="w-full border-collapse">
                    <thead>
                        <tr className="border-b-2 border-black/20">
                            <th className="text-left px-4 py-3 text-sm font-semibold text-black/60">#</th>
                            <th className="text-left px-4 py-3 text-sm font-semibold text-black/60">Genre</th>
                            <th className="text-left px-4 py-3 text-sm font-semibold text-black/60">Title</th>
                            <th className="text-left px-4 py-3 text-sm font-semibold text-black/60">Cast</th>
                            <th className="text-left px-4 py-3 text-sm font-semibold text-black/60">Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        {movies.map((movie) => (
                            <React.Fragment key={movie.index}>
                                <tr
                                    onClick={() => toggleExpand(movie.index)}
                                    className={`border-b border-black/10 cursor-pointer transition-colors ${
                                        expandedIndex === movie.index
                                            ? 'bg-black/5'
                                            : 'hover:bg-black/5'
                                    }`}
                                >
                                    <td className="px-4 py-3 text-sm">{movie.index}</td>
                                    <td className="px-4 py-3 text-sm">{movie.genre}</td>
                                    <td className="px-4 py-3 text-sm font-medium">{movie.title}</td>
                                    <td className="px-4 py-3 text-sm text-black/70">{movie.actors.join(', ')}</td>
                                    <td className="px-4 py-3 text-sm">{movie.year}</td>
                                </tr>
                                {expandedIndex === movie.index && (
                                    <tr className="border-b border-black/10 bg-black/5">
                                        <td colSpan={5} className="px-6 py-5">
                                            <div className="flex gap-6">
                                                {/* Freeze frame placeholder */}
                                                    <div className="w-64 aspect-video rounded-lg overflow-hidden relative bg-gray-300">
                                                        <img
                                                            src={movie.trailer.freezeFrame.frame}
                                                            alt={movie.title}
                                                            className="w-full h-full object-cover"
                                                        />
                                                        <div className="absolute inset-0 flex items-end justify-center pb-3 bg-gradient-to-t from-black/70 via-transparent to-transparent">
                                                            <span className="text-white text-lg font-bold text-center px-4">
                                                                {movie.title}
                                                            </span>
                                                        </div>
                                                    </div>
                                                
                                                <div className="flex-1">
                                                    <button
                                                        onClick={() => playTrailer(movie)}
                                                        className="mb-4 rounded-full px-6 py-2 bg-black text-white text-sm hover:bg-black/80 transition-colors"
                                                    >
                                                        ▶ Play
                                                    </button>
                                                    
                                                    <div className="mb-4">
                                                        <span className="text-sm text-black/60">Likes: </span>
                                                        <span className="text-sm font-semibold">{movie.likes}</span>
                                                    </div>
                                                    
                                                    <div>
                                                        <h4 className="text-sm font-semibold mb-2">Reviews:</h4>
                                                        {movie.reviews.length === 0 ? (
                                                            <p className="text-sm text-black/50">No reviews</p>
                                                        ) : (
                                                            <div className="space-y-2">
                                                                {movie.reviews.map((review, idx) => (
                                                                    <div key={idx} className="text-sm">
                                                                        <span className="font-semibold">{review.author}: </span>
                                                                        <span className="text-black/70">{review.text}</span>
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </React.Fragment>
                        ))}
                    </tbody>
                </table>
            </div>

            <Pagination currentPage={params.page} onPageChange={setPage} />
        </div>
    );
}