<?php

namespace App\Core;

/**
 * ترقيم الصفحات (Pagination)
 */
class Paginator
{
    private int $currentPage;
    private int $perPage;
    private int $total;
    private array $items;
    private string $queryString;

    public function __construct(array $items, int $total, int $page, int $perPage, string $queryString = '')
    {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = max(1, $perPage);
        $this->currentPage = max(1, $page);
        $this->queryString = $queryString;
    }

    public function items(): array
    {
        return $this->items;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function totalPages(): int
    {
        return (int)ceil($this->total / $this->perPage);
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function hasPages(): bool
    {
        return $this->totalPages() > 1;
    }

    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages();
    }

    private function buildQuery(int $page): string
    {
        $params = $_GET;
        $params['page'] = $page;
        return '?' . http_build_query($params);
    }

    public function prevUrl(): string
    {
        return $this->buildQuery($this->currentPage - 1);
    }

    public function nextUrl(): string
    {
        return $this->buildQuery($this->currentPage + 1);
    }

    public function pageUrl(int $page): string
    {
        return $this->buildQuery($page);
    }

    /** عرض روابط الترقيم */
    public function links(): string
    {
        if (!$this->hasPages()) {
            return '';
        }

        $html = '<nav aria-label="ترقيم الصفحات"><ul class="pagination pagination-sm justify-content-center mb-0">';

        $html .= $this->hasPrev()
            ? '<li class="page-item"><a class="page-link" href="' . e($this->prevUrl()) . '"><i class="bi bi-chevron-right"></i></a></li>'
            : '<li class="page-item disabled"><span class="page-link"><i class="bi bi-chevron-right"></i></span></li>';

        $totalPages = $this->totalPages();
        $start = max(1, $this->currentPage - 2);
        $end = min($totalPages, $this->currentPage + 2);

        for ($i = $start; $i <= $end; $i++) {
            if ($i === $this->currentPage) {
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . e($this->pageUrl($i)) . '">' . $i . '</a></li>';
            }
        }

        $html .= $this->hasNext()
            ? '<li class="page-item"><a class="page-link" href="' . e($this->nextUrl()) . '"><i class="bi bi-chevron-left"></i></a></li>'
            : '<li class="page-item disabled"><span class="page-link"><i class="bi bi-chevron-left"></i></span></li>';

        $html .= '</ul></nav>';
        return $html;
    }
}
