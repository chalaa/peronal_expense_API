<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;  

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try{
            $all_category = Auth::user()->categories;

            // Group categories by 'type' (income/expense)
            $grouped = $all_category->groupBy('type')->map(function ($items) {
                return $items->values(); // Reset array keys for frontend
            });
            return response()->json($grouped);
        }
        catch(ModelNotFoundException $e){
            // if category not found retun 404 resource not found
            return response()->json([
                'error' => 'Category not found',
                'message' => 'No Category available'
            ], 404);
        }
        catch(\Exception $e){

            return response()->json([
                'error' => 'An error occurred while fetching category',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validate the request
        try{
            $validated = $request->validate([
                'name' => ['required', 'string', 'unique:categories,name'],
                'type' => ['required', 'string', 'in:income,expense'],
                'color' => ['required', 'string','regex:/^#([A-Fa-f0-9]{6})$/'],
            ]);
    
            $category = Auth::user()->categories()->create($validated);
    
            return response()->json([
                'message' => 'Category Added successfully',
                'category' => $category
            ],201);
        }
        catch(ValidationException $e){
             return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        }catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while creating the user',
                'message' => $e->getMessage()
            ], 500);
        }
        }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $category = Auth::user()->categories()->findOrFail($id);

            return response()->json($category);
        } 
        catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching categories',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $category = Auth::user()->categories()->findOrFail($id);

            $validated = $request->validate([
                'name' => ['string', 'unique:categories,name,' . $category->id],
                'type' => ['nullable', 'string', 'in:income,expense'],
                'color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            ]);

            $category->update($validated);

            return response()->json([
                'message' => 'Category updated successfully',
                'category' => $category
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while updating the category',
                'message' => $e->getMessage()
            ], 500);
        }


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $category = Auth::user()->categories()->findOrFail($id);
            $category->delete();

            return response()->json(['message' => 'Category deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Category not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting the category',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
