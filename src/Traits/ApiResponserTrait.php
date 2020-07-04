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
     * @param Model $model
     * @param Collection $collection
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($response, $code = 200)
    {
        if($response instanceof Collection)
            return $this->collection($response, $code);

        if($response instanceof Model)
            return $this->model($response, $code);

        return $response;
    }

    /**
     * @param Collection $collection
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function collection(Collection $collection, int $code)
    {
        if ($collection->isEmpty())
            return $this->success(['data' => $collection], $code);

        $transformer = $collection->first()->transformer;

        $collection = $this->filter($collection, $transformer);
        $collection = $this->sort($collection);
        $collection = $this->paginate($collection);
        $collection = $this->transform($collection, $transformer);
        $collection = $this->cache($collection);

        return $this->success($collection, $code);
    }

    /**
     * @param Model $model
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function model(Model $model, int $code)
    {
        $transformer = $model->transformer;
        $model       = $this->transform($model, $transformer);

        return $this->success($model, $code);
    }

    /**
     * @param $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function message($message, $code = 200)
    {
        return $this->success(['data' => $message], $code);
    }

    /**
     * @param $data
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    private function success($data, $code)
    {
        return response()->json($data, $code);
    }

    /**
     * @param $message
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error($message, $code)
    {
        return response()->json(['error' => $message, 'code' => $code], $code);
    }

    /**
     * @param Collection $collection
     * @param $transformer
     * @return Collection|static
     */
    protected function filter(Collection $collection, $transformer)
    {
        foreach (request()->query() as $query => $value) 
        {
            if (isset($query, $value) and $transformer::hasAttribute($query))
                $collection = $collection->where($query, $value);
        }

        return $collection;
    }

    /**
     * @param Collection $collection
     * @return Collection|mixed
     */
    protected function sort(Collection $collection)
    {
        if (request()->has('sort_by'))
            $collection = $collection->sortBy->{request('sort_by')};

        return $collection;
    }

    /**
     * @param Collection $collection
     * @return LengthAwarePaginator
     */
    protected function paginate(Collection $collection)
    {
        Validator::validate(request()->all(), [
            'per_page' => 'integer|min:2|max:50'
        ]);

        $page    = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;

        if (request()->has('per_page'))
            $perPage = (int) request()->per_page;

        $results   = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath()
        ]);

        $paginated->appends(request()->all());

        return $paginated;
    }

    /**
     * @param $data
     * @param $transformer
     * @return array
     */
    protected function transform($data, $transformer)
    {
        $transformation = fractal($data, new $transformer);

        return $transformation->toArray();
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function cache($data)
    {
        $url         = request()->url();
        $queryParams = request()->query();

        ksort($queryParams);

        $queryString = http_build_query($queryParams);
        $fullUrl     = "{$url}?{$queryString}";

        return Cache::remember($fullUrl, 30/60, function() use($data) {
            return $data;
        });
    }

}