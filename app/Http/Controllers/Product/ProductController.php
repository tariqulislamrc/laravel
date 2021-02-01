<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Product\ProductServices;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    protected $request, $service;
    protected $module = 'product';

    public function __construct(
        ProductServices $service,
        Request $request
        )
    {
        $this->service = $service;
        $this->request = $request;
    }


    public function index()
    {
        $products = $this->service->paginate($this->request->all());
        return $this->success(compact('products'));
    }

    public function store(ProductRequest $request)
    {
       $this->service->create($this->request->all());
        return $this->success(['message' => 'New Product Added Successfull']);
    }

    public function show($uuid)
    {
        $show = $this->service->show($uuid);
        return $this->success($show);
    }

    public function update($uuid, ProductRequest $request)
    {
        $this->service->update($uuid, $this->request->all());
        return $this->success(['message' => 'Product Updated Successfull']);
    }

    public function destroy($uuid)
    {
        $this->service->delete($uuid);
        return $this->success(['message' => 'Product Deleted Successfull']);
    }
}
