<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $all_category = Auth::user()->categories->all();
        return response()->json($all_category);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validate the request
        $request->validate([
            'name' => ['required','string']
        ]);

        $category = Auth::user()->categories()->create([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Category Added successfully',
            'category' => $category
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $all_category = Auth::user()->categories;
        $category = $all_category->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //validate request
        $request->validate([
            'name' => ['required','string']
        ]);

        $all_category = Auth::user()->categories;

        $category = $all_category->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        //update the category (name
        $category->update([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Category Updated successfully',
            'category' => $category
        ]);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $all_category = Auth::user()->categories;
        $category = $all_category->find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
