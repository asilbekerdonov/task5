import React, { useState } from 'react';
import { Movie } from '../types/movie';
import { useTrailer } from '../context/TrailerContext';

interface MovieCardProps {
    movie: Movie;
}

export default function MovieCard({ movie }: MovieCardProps) {
    const [expanded, setExpanded] = useState(false);
    const { playTrailer } = useTrailer();
    return (
        <div
            onClick={() => setExpanded(!expanded)}
            className="border border-black/10 rounded-lg p-4 cursor-pointer hover:shadow-lg transition-shadow"
        >
            {/* Freeze frame placeholder */}
            <div className="w-full aspect-video bg-gray-300 flex items-center justify-center rounded-lg mb-3">
                <span className="text-white text-lg font-bold text-center px-4">
                    {movie.title}
                </span>
            </div>

            {/* Title */}
            <h3 className="font-bold text-sm mb-1">{movie.title}</h3>

            {/* Genre + Year */}
            <p className="text-xs text-black/50 mb-1">
                {movie.genre} • {movie.year}
            </p>

            {/* Actors */}
            <p className="text-xs text-black/70 mb-3">{movie.actors.join(', ')}</p>

            {/* Expanded content */}
            {expanded && (
                <div onClick={(e) => e.stopPropagation()}>
                    <button
                        onClick={() => playTrailer(movie)}
                        className="mb-3 rounded-full px-4 py-1.5 bg-black text-white text-xs hover:bg-black/80 transition-colors"
                    >
                        ▶ Play
                    </button>

                    <div className="mb-3">
                        <span className="text-xs text-black/60">Likes: </span>
                        <span className="text-xs font-semibold">{movie.likes}</span>
                    </div>

                    <div>
                        <h4 className="text-xs font-semibold mb-1">Reviews:</h4>
                        {movie.reviews.length === 0 ? (
                            <p className="text-xs text-black/50">No reviews</p>
                        ) : (
                            <div className="space-y-1">
                                {movie.reviews.map((review, idx) => (
                                    <div key={idx} className="text-xs">
                                        <span className="font-semibold">{review.author}: </span>
                                        <span className="text-black/70">{review.text}</span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}