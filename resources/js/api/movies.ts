import { MoviesResponse } from '../types/movie';

export interface MovieQueryParams {
    locale: string;
    seed: number;
    likes: number;
    reviews: number;
    page: number;
    per_page: number;
}

export async function fetchMovies(params: MovieQueryParams): Promise<MoviesResponse> {
    const query = new URLSearchParams({
        locale: params.locale,
        seed: String(params.seed),
        likes: String(params.likes),
        reviews: String(params.reviews),
        page: String(params.page),
        per_page: String(params.per_page),
    });

    const response = await fetch(`/api/movies?${query.toString()}`);
    if (!response.ok) {
        throw new Error(`Failed to fetch movies: ${response.status}`);
    }
    return response.json();
}
