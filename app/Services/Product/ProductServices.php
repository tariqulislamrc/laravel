<?php

namespace App\Services\Product;

use App\Models\Product\Product;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Upload\UploadRepository;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class ProductServices{

    private $repo, $upload;
    protected $module = 'product';

    public function __construct(
        ProductRepository $repo,
        UploadRepository $upload
        )
    {
        $this->repo = $repo;
        $this->upload = $upload;
    }

     /**
     * Get all filtered data
     *
     * @param array $params
     * @return Product
     */
    public function getData($params)
    {
        $sort_by         = gv($params, 'sort_by', 'created_at');
        $order           = gv($params, 'order', 'desc');
        $keyword           = gv($params, 'keyword');

        $created_at_start_date = gv($params, 'date_of_product_start_date');
        $created_at_end_date   = gv($params, 'date_of_product_end_date');

        $query = $this->repo->model->dateOfProductBetween([
                'start_date' => $created_at_start_date,
                'end_date' => $created_at_end_date
            ])->filterByKeyword($keyword);


        return $query->orderBy($sort_by, $order);
    }

    /**
     * Paginate all product using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $page_length = gv($params, 'page_length', config('config.page_length'));

        return $this->getData($params)->paginate($page_length);
    }

    /**
     * Create a new product.
     *
     * @param array $params
     * @return Product
     */
    public function create($params)
    {
        $product = $this->repo->model->forceCreate($this->formatParams($params));

        $this->processUpload($product, $params);

        return $product;
    }


    /**
     * Prepare given params for inserting into database.
     *
     * @param array $params
     * @param string $type
     * @return array
     */
    private function formatParams($params, $product_id = null)
    {
        $title           = gv($params, 'title');
        $slug            = createSlug($title);
        $price           = gv($params, 'price', 0);
        $description     = gv($params, 'description');
        

       
        $formatted = [
            'title'           => $title,
            'slug'            => $slug,
            'price'           => $price,
            'description'     => stripInlineStyle($description)
        ];

        if (! $product_id) {
            $formatted['upload_token'] = gv($params, 'upload_token');
            $formatted['uuid'] = Str::uuid();
        }

        return $formatted;
    }

     /**
     * Upload attachment
     *
     * @param Product $product
     * @param array $params
     * @param string $action
     * @return void
     */
    public function processUpload(Product $product, $params = array(), $action = 'create')
    {
        $upload_token = gv($params, 'upload_token');

        if ($action === 'create') {
            $this->upload->store($this->module, $product->id, $upload_token);
        } else {
            $this->upload->update($this->module, $product->id, $upload_token);
        }
    }

    public function show($uuid)
    {
        $product = $this->repo->findByUuidOrFail($uuid);
        $attachments = $this->getAttachment($this->module, $product->id);
        return compact('product', 'attachments');
    }

    /**
     * Update given product.
     *
     * @param Product $product
     * @param array $params
     *
     * @return Product
     */
    public function update($uuid, $params)
    {
        $product = $this->repo->findByUuidOrFail($uuid);
        $this->repo->update($product, $this->formatParams($params, $product->id));
        $this->processUpload($product, $params, 'update');
        return $product;
    }

    /**
     * Delete product.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete($uuid)
    {
        $product = $this->repo->findByUuidOrFail($uuid);
        return $this->repo->delete($product);
    }


    public function getAttachment($module, $product_id){

        return $this->upload->getAttachment($module, $product_id);

    }

    
}
