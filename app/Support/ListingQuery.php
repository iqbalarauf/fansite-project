<?php

namespace App\Support;

use Illuminate\Http\Request;

final class ListingQuery
{
    /**
     * @param  array<int, string>  $allowedSorts
     * @param  array<string, string>  $extra
     * @return array<string, string|int>
     */
    public static function from(Request $request, array $allowedSorts, string $defaultSort, array $extra = []): array
    {
        $sortBy = $request->string('sort_by', $defaultSort)->toString();
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = $defaultSort;
        }

        $perPage = (int) $request->integer('per_page', 10);

        $filters = [
            'search' => $request->string('search')->toString(),
            'sort_by' => $sortBy,
            'sort_dir' => $request->string('sort_dir', 'desc')->toString() === 'asc' ? 'asc' : 'desc',
            'per_page' => in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10,
        ];

        foreach ($extra as $key => $default) {
            $filters[$key] = $request->string($key, $default)->toString();
        }

        return $filters;
    }
}
