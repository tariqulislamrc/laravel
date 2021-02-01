<?php

namespace App\Repositories\Product;

use App\Models\Product\Product;
use Illuminate\Validation\ValidationException;

class ProductRepository {

    public $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

     /**
     * Get model query
     *
     * @return Product query
     */
    public function getQuery()
    {
        return $this->model;
    }

    /**
     * Count Product
     *
     * @return integer
     */
    public function count()
    {
        return $this->model->count();
    }

    /**
     * List all model by title & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->model->get()->pluck('title', 'id')->all();
    }

    /**
     * Get all models
     *
     * @return array
     */
    public function getAll()
    {
        return $this->model->all();
    }

    /**
     * Find model with given id.
     *
     * @param integer $id
     * @return Product
     */
    public function find($id)
    {
        return $this->model->filterById($id)->first();
    }

    /**
     * Find model with given id or throw an error.
     *
     * @param integer $id
     * @return Product
     */
    public function findOrFail($id, $field = 'message')
    {
        $model = $this->model->filterById($id)->first();

        if (! $model) {
            throw ValidationException::withMessages([$field => 'Product not found']);
        }

        return $model;
    }

    /**
     * Find model with given uuid.
     *
     * @param string $uuid
     * @return Product
     */
    public function findByUuid($uuid)
    {
        return $this->model->filterByUuid($uuid)->first();
    }

    /**
     * Find model with given uuid or throw an error.
     *
     * @param string $uuid
     * @return Product
     */
    public function findByUuidOrFail($uuid, $field = 'message')
    {
        $model = $this->model->filterByUuid($uuid)->first();

        if (! $model) {
            throw ValidationException::withMessages([$field => 'Product not found']);
        }

        return $model;
    }

    /**
     * Find model with given uuid for any session or throw an error.
     *
     * @param string $uuid
     * @return Product
     */
    public function findByUuidOrFailWithoutSession($uuid, $field = 'message')
    {
        $model = $this->model->filterByUuid($uuid)->first();

        if (! $model) {
            throw ValidationException::withMessages([$field => 'Product not found']);
        }

        return $model;
    }

    public function update(Product $model, $params) {
        return $model->forceFill($params)->save();
    }

    public function delete(Product $model)
    {
        return $model->delete();
    }

}
