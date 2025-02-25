<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $search = request()->query('p');

        $categories = Category::with('parent')
            ->leftJoin('categories as parent', 'categories.parent_id', '=', 'parent.id')
            ->when($search, function ($query) use ($search) {
                $query->where('categories.slug', 'like', "%$search%")
                    ->orWhere('parent.slug', 'like', "%$search%");
            })
            ->select('categories.*')
            ->orderBy('parent.name', 'desc')
            ->orderBy('categories.name')
            ->paginate(10);

        return response()->json($categories, Response::HTTP_OK);
    }

    // -----------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:255', 'min:3', function ($attribute, $value, $fail) {
                $slug = Str::slug($value);
                if (Category::where('slug', $slug)->exists()) {
                    $fail('Category exists');
                }
            }],
            'isParent' => 'required|boolean',
            'parentId' => 'required_if:isParent,true|exists:categories,id',
        ], [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute may not be greater than 255 characters',
            '*.min' => ':Attribute must be at least 3 characters',
            '*.required_if' => ':Attribute is required',
            '*.exists' => ':Attribute does not exist',
        ], [
            'name' => 'Category',
            'isParent' => 'If parent',
            'parentId' => 'Parent category',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Category::create([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parentId ?? null,
        ]);

        return response()->json(['message' => 'Category created'], Response::HTTP_CREATED);
    }

    // -----------------------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:255', 'min:3', function ($attribute, $value, $fail) use ($id) {
                $slug = Str::slug($value);
                if (Category::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $fail('Category exists');
                }
            }],
            'isParent' => 'required|boolean',
            'parentId' => 'required_if:isParent,true|exists:categories,id',
        ], [
            '*.required' => ':Attribute is required',
            '*.max' => ':Attribute may not be greater than 255 characters',
            '*.min' => ':Attribute must be at least 3 characters',
            '*.required_if' => ':Attribute is required',
            '*.exists' => ':Attribute does not exist',
        ], [
            'name' => 'Category',
            'isParent' => 'If parent',
            'parentId' => 'Parent category',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Category::findOrFail($id)->update([
            'name' => trim($request->name),
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parentId ?? null,
        ]);

        return response()->json(['message' => 'Category updated'], Response::HTTP_OK);
    }

    // -----------------------------------------------

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        if (!$category->parent_id) {
            $category->children()->update(['is_active' => false]);
            $category->update(['is_active' => false]);
        } else {
            $category->update(['is_active' => false]);
        }

        return response()->json(['message' => 'Category deleted'], Response::HTTP_OK);
    }

    // -----------------------------------------------

    public function restore($id)
    {
        $category = Category::findOrFail($id);

        if ($category->parent_id && !$category->parent->is_active) {
            return response()->json(['errors' => 'Parent category is inactive. Activate parent first'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $category->update(['is_active' => true]);

        return response()->json(['message' => 'Category restored'], Response::HTTP_OK);
    }

    // -----------------------------------------------

    public function delete($id) {}
}
