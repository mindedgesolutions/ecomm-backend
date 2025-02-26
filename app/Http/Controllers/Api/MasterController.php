<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MasterController extends Controller
{
    public function parentCategories()
    {
        $categories = Category::with('children')->whereNull('parent_id')->where('is_active', true)->orderBy('name')->get();

        return response()->json($categories, Response::HTTP_OK);
    }

    public function childCategories()
    {
        $categories = Category::with('parent')->whereNotNull('parent_id')->orderBy('name')->get();

        return response()->json($categories, Response::HTTP_OK);
    }

    public function brands()
    {
        $brands = Brand::orderBy('name')->get();

        return response()->json($brands, Response::HTTP_OK);
    }
}
