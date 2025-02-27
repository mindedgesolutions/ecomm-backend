<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category', 'brand', 'images', 'discount')->orderBy('name')->paginate(10);

        return ProductResource::collection($products);
    }

    // ----------------------------------------------------

    public function store(ProductRequest $request)
    {
        try {
            DB::beginTransaction();

            $product = Product::create([
                'category_id' => $request->category,
                'brand_id' => $request->brand,
                'name' => trim($request->name),
                'slug' => Str::slug($request->name),
                'product_code' => $request->code ?? null,
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
            ]);

            if ($request->discountType) {
                ProductDiscount::create([
                    'product_id' => $product->id,
                    'price' => $request->price,
                    'discount_type' => $request->discountType,
                    'discount_amt' => $request->discountAmt,
                ]);
            }

            if ($request->hasFile('cover')) {
                $file = $request->file('cover');
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/products/' . $product->id;

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => Storage::url($filePath),
                    'is_cover' => true
                ]);
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    if ($image->getClientOriginalName() != $request->cover->getClientOriginalName()) {
                        $filename = Str::random(10) . time() . '-' . $image->getClientOriginalName();
                        $directory = 'uploads/products/' . $product->id;

                        if (!Storage::disk('public')->exists($directory)) {
                            Storage::disk('public')->makeDirectory($directory);
                        }
                        $filePath = $image->storeAs($directory, $filename, 'public');

                        ProductImage::create([
                            'product_id' => $product->id,
                            'path' => Storage::url($filePath),
                            'is_cover' => false
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json(['message' => 'Product created'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['errors' => $th->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // ----------------------------------------------------

    public function show(string $id)
    {
        //
    }

    // ----------------------------------------------------

    public function update(Request $request, string $id)
    {
        //
    }

    // ----------------------------------------------------

    public function destroy(string $id)
    {
        //
    }
}
