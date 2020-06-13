<?php

namespace iamx\ApiCrudGenerator\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponserTrait
{
    /**
     * @author Benito Huerta
     * @param $data
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    private function successResponse($data, $code)
    {
        return response()->json($data, $code);
    }

    /**
     * @author Benito Huerta
     * @param $message
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message, $code)
    {
        return response()->json(['error' => $message, 'code' => $code], $code);
    }

    /**
     * @author Benito Huerta
     * @param Collection $collection
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function showAll(Collection $collection, $code = 200)
    {
        if ($collection->isEmpty()) {
            return $this->successResponse(['data' => $collection], $code);
        }

        $transformer = $collection->first()->transformer;

        $collection = $this->filterData($collection, $transformer);
        $collection = $this->sortData($collection);
        $collection = $this->paginate($collection);
        $collection = $this->transformData($collection, $transformer);
        $collection = $this->cacheResponse($collection);

        return $this->successResponse($collection, $code);
    }

    /**
     * @author Benito Huerta
     * @param Model $instance
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function showOne(Model $instance, $code = 200)
    {
        $transformer = $instance->transformer;
        $instance = $this->transformData($instance, $transformer);

        return $this->successResponse($instance, $code);
    }

    /**
     * @author Benito Huerta
     * @param $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function showMessage($message, $code = 200)
    {
        return $this->successResponse(['data' => $message], $code);
    }

    /**
     * @author Benito Huerta
     * @param Collection $collection
     * @param $transformer
     * @return Collection|static
     */
    protected function filterData(Collection $collection, $transformer)
    {
        foreach (request()->query() as $query => $value) {
            if (isset($query, $value) and $transformer::hasAttribute($query)) {
                $collection = $collection->where($query, $value);
            }
        }
        return $collection;
    }

    /**
     * @author Benito Huerta
     * @param Collection $collection
     * @return Collection|mixed
     */
    protected function sortData(Collection $collection)
    {
        if (request()->has('sort_by')) {
            $collection = $collection->sortBy->{request('sort_by')};
        }
        return $collection;
    }

    /**
     * @author Benito Huerta
     * @param Collection $collection
     * @return LengthAwarePaginator
     */
    protected function paginate(Collection $collection)
    {
        $rules = [
            'per_page' => 'integer|min:2|max:50'
        ];
        Validator::validate(request()->all(), $rules);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        if (request()->has('per_page'))
            $perPage = (int) request()->per_page;

        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath()
        ]);

        $paginated->appends(request()->all());

        return $paginated;
    }

    /**
     * @author Benito Huerta
     * @param $data
     * @param $transformer
     * @return array
     */
    protected function transformData($data, $transformer)
    {
        $transformation = fractal($data, new $transformer);

        return $transformation->toArray();
    }

    /**
     * @author Benito Huerta
     * @param $data
     * @return mixed
     */
    protected function cacheResponse($data)
    {
        $url = request()->url();
        $queryParams = request()->query();

        ksort($queryParams);
        $queryString = http_build_query($queryParams);
        $fullUrl = "{$url}?{$queryString}";

        return Cache::remember($fullUrl, 30/60, function() use($data) {
            return $data;
        });
    }

}