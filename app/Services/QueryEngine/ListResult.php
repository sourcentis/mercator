<?php

namespace App\Services\QueryEngine;

use Illuminate\Support\Collection;

class ListResult
{
    public function __construct(
        public readonly Collection $rows,
        public readonly array $columns,
    ) {}

    public function toArray(): array
    {
        return [
            'columns' => $this->columns,
            'rows' => $this->rows->values()->toArray(),
        ];
    }

    public function rowCount(): int
    {
        return $this->rows->count();
    }
}
