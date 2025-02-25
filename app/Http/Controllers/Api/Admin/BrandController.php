<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('name')->paginate(10);

        return response()->json($brands, Response::HTTP_OK);
    }

    // -----------------------------------------------

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:255', 'min:3', function ($attribute, $value, $fail) {
                $slug = Str::slug($value);
                $brand = Brand::where('slug', $slug)->first();
                if ($brand) {
                    $fail('Brand exists');
                }
            }],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:100'],
        ], [
            'name.required' => ':Attribute is required',
            'name.max' => ':Attribute may not be greater than 255 characters',
            'name.min' => ':Attribute must be at least 3 characters',
            'logo.image' => 'Logo must be an image',
            'logo.mimes' => 'Logo must be a file of type: jpeg, png, jpg',
            'logo.max' => 'Logo may not be greater than 100KB',
        ], [
            'name' => 'Brand name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $brand = Brand::create([
                'name' => trim($request->name),
                'slug' => Str::slug($request->name),
                'description' => trim($request->desc) ?? null,
            ]);

            if ($request->has('logo') && $request->file('logo') != null) {
                $file = $request->file('logo');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/brands/';

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');

                $brand->update([
                    'logo' => Storage::url($filePath),
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Brand created'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -----------------------------------------------

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:255', 'min:3', function ($attribute, $value, $fail) use ($id) {
                $slug = Str::slug($value);
                $brand = Brand::where('slug', $slug)->where('id', '!=', $id)->first();
                if ($brand) {
                    $fail('Brand exists');
                }
            }],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:100'],
        ], [
            'name.required' => ':Attribute is required',
            'name.max' => ':Attribute may not be greater than 255 characters',
            'name.min' => ':Attribute must be at least 3 characters',
            'logo.image' => 'Logo must be an image',
            'logo.mimes' => 'Logo must be a file of type: jpeg, png, jpg',
            'logo.max' => 'Logo may not be greater than 100KB',
        ], [
            'name' => 'Brand name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $brand = Brand::findOrFail($id);

            $brand->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => trim($request->desc) ?? null,
            ]);

            if ($request->has('logo') && $request->file('logo') != null) {
                $relativePath = str_replace('/storage/', '', parse_url($brand->logo, PHP_URL_PATH));

                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }

                $file = $request->file('logo');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(10) . time() . '-' . $file->getClientOriginalName();
                $directory = 'uploads/brands/';

                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                }
                $filePath = $file->storeAs($directory, $filename, 'public');

                $brand->update([
                    'logo' => Storage::url($filePath),
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Brand created'], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // -----------------------------------------------

    public function destroy(string $id)
    {
        Brand::whereId($id)->update(['is_active' => false]);

        return response()->json(['message' => 'Brand deleted'], Response::HTTP_OK);
    }

    // -----------------------------------------------

    public function restore(string $id)
    {
        Brand::whereId($id)->update(['is_active' => true]);

        return response()->json(['message' => 'Brand restored'], Response::HTTP_OK);
    }

    // -----------------------------------------------

    public function delete(string $id)
    {
        $brand = Brand::findOrFail($id);

        $relativePath = str_replace('/storage/', '', parse_url($brand->logo, PHP_URL_PATH));

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }

        $brand->delete();

        return response()->json(['message' => 'Brand deleted'], Response::HTTP_OK);
    }
}
