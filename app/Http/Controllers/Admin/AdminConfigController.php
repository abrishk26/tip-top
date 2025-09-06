<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class AdminConfigController extends Controller
{
    // GET /api/admin/categories
    public function listCategories(Request $request)
    {
        $categories = Category::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    // POST /api/admin/categories
    public function createCategory(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        $category = Category::create($data);
        return response()->json($category, 201);
    }

    // PUT /api/admin/categories/{id}
    public function updateCategory(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id . ',id'],
        ]);

        $category->update($data);
        return response()->json($category);
    }

    // DELETE /api/admin/categories/{id}
    public function deleteCategory(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}
