export interface Review {
    author: string;
    text: string;
}

export interface TrailerClip {
    clipId: string;
    source: string;
    zoom: number;
    speed: number;
    colorFilter: string;
}

export interface Trailer {
    duration: number;
    titleAnimation: { type: string };
    taglineAnimation: { text: string; type: string } | null;
    clips: TrailerClip[];
    transitions: string[];
    freezeFrame: { clipId: string; source: string };
}

export interface Movie {
    index: number;
    title: string;
    actors: string[];
    genre: string;
    year: number;
    likes: number;
    reviews: Review[];
    trailer: Trailer;
}

export interface MoviesResponse {
    data: Movie[];
    meta: {
        page: number;
        per_page: number;
        has_more: boolean;
    };
}
