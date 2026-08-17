import React from 'react';

interface PaginationProps {
    currentPage: number;
    onPageChange: (page: number) => void;
}

export default function Pagination({ currentPage, onPageChange }: PaginationProps) {
    const getPageWindow = () => {
        const start = Math.max(1, currentPage - 2);
        const end = start + 4; // показываем 5 страниц
        return Array.from({ length: end - start + 1 }, (_, i) => start + i);
    };

    const pages = getPageWindow();

    return (
        <div className="flex items-center gap-2 mt-4">
            <button
                onClick={() => onPageChange(currentPage - 1)}
                disabled={currentPage === 1}
                className={`rounded-full px-3 py-1.5 text-sm border transition-colors ${
                    currentPage === 1
                        ? 'bg-black/5 text-black/30 border-black/10 cursor-not-allowed'
                        : 'bg-white text-black border-black/20 hover:bg-black hover:text-white'
                }`}
            >
                «
            </button>

            {pages.map((page) => (
                <button
                    key={page}
                    onClick={() => onPageChange(page)}
                    className={`rounded-full px-3 py-1.5 text-sm border transition-colors ${
                        page === currentPage
                            ? 'bg-black text-white border-black'
                            : 'bg-white text-black border-black/20 hover:bg-black hover:text-white'
                    }`}
                >
                    {page}
                </button>
            ))}

            <button
                onClick={() => onPageChange(currentPage + 1)}
                className="rounded-full px-3 py-1.5 text-sm border border-black/20 bg-white text-black hover:bg-black hover:text-white transition-colors"
            >
                »
            </button>
        </div>
    );
}