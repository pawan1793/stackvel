<?php

namespace Stackvel;

/**
 * Stackvel Framework - Paginator Class
 * 
 * Handles pagination results with metadata
 */
class Paginator
{
    /**
     * The paginated items
     */
    private array $items;

    /**
     * Total number of items
     */
    private int $total;

    /**
     * Items per page
     */
    private int $perPage;

    /**
     * Current page
     */
    private int $currentPage;

    /**
     * Additional query parameters
     */
    private array $appends = [];

    /**
     * Constructor
     */
    public function __construct(array $items, int $total, int $perPage, int $currentPage)
    {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
    }

    /**
     * Get the items
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get total count
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Get items per page
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get current page
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Get last page
     */
    public function getLastPage(): int
    {
        return (int) ceil($this->total / $this->perPage);
    }

    /**
     * Check if there are more pages
     */
    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->getLastPage();
    }

    /**
     * Check if there are previous pages
     */
    public function hasPreviousPages(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Get next page URL
     */
    public function getNextPageUrl(): ?string
    {
        if ($this->hasMorePages()) {
            return $this->buildUrl($this->currentPage + 1);
        }
        return null;
    }

    /**
     * Get previous page URL
     */
    public function getPreviousPageUrl(): ?string
    {
        if ($this->hasPreviousPages()) {
            return $this->buildUrl($this->currentPage - 1);
        }
        return null;
    }

    /**
     * Add query parameters to pagination URLs
     */
    public function appends(array $parameters): self
    {
        $this->appends = array_merge($this->appends, $parameters);
        return $this;
    }

    /**
     * Get the data array
     */
    public function getData(): array
    {
        return $this->items;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(function ($item) {
                return $item instanceof Model ? $item->toArray() : $item;
            }, $this->items),
            'current_page' => $this->currentPage,
            'per_page' => $this->perPage,
            'total' => $this->total,
            'last_page' => $this->getLastPage(),
            'from' => $this->getFrom(),
            'to' => $this->getTo(),
            'next_page_url' => $this->getNextPageUrl(),
            'prev_page_url' => $this->getPreviousPageUrl(),
        ];
    }

    /**
     * Get the "from" index
     */
    private function getFrom(): int
    {
        return ($this->currentPage - 1) * $this->perPage + 1;
    }

    /**
     * Get the "to" index
     */
    private function getTo(): int
    {
        return min($this->currentPage * $this->perPage, $this->total);
    }

    /**
     * Build URL with parameters
     */
    private function buildUrl(int $page): string
    {
        $query = array_merge($_GET ?? [], $this->appends, ['page' => $page]);
        return '?' . http_build_query($query);
    }

    /**
     * Magic method to access items as array
     */
    public function __get(string $key)
    {
        if ($key === 'data') {
            return $this->items;
        }
        
        if (method_exists($this, 'get' . ucfirst($key))) {
            return $this->{'get' . ucfirst($key)}();
        }
        
        return null;
    }

    /**
     * Magic method to check if property exists
     */
    public function __isset(string $key): bool
    {
        return in_array($key, ['data', 'current_page', 'per_page', 'total', 'last_page', 'from', 'to', 'next_page_url', 'prev_page_url']);
    }
} 