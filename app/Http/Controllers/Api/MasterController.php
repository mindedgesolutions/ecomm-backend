<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MasterController extends Controller
{
    public function parentCategories()
    {
        $categories = Category::whereNull('parent_id')->orderBy('name')->get();

        return response()->json($categories, Response::HTTP_OK);
    }

    public function childCategories()
    {
        $categories = Category::whereNotNull('parent_id')->orderBy('name')->get();

        return response()->json($categories, Response::HTTP_OK);
    }
}
