<?php

namespace App\Traits;


use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait Paginator
{
    /**
     * @param LengthAwarePaginator $paginator
     * @return array
     */
    protected function paginate(LengthAwarePaginator $paginator){
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'data' => $paginator->items(),
        ];
    }
}