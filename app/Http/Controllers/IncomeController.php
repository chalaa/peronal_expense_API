<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Http\Resources\IncomeResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $incomes = Auth::user()->incomes;
            return IncomeResource::collection($incomes);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch incomes',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => ['required', 'numeric'],
                'category_id' => [
                    'required',
                    'integer',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('type', 'income')
                              ->where('user_id', Auth::id());
                    }),
                ],
                'date' => ['required', 'date'],
                'description' => ['required', 'string'],
            ]);

            $income = Auth::user()->incomes()->create($validated);
            return new IncomeResource($income);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create income',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $income = Auth::user()->incomes()->findOrFail($id);
            return new IncomeResource($income);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Income not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch income',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $income = Auth::user()->incomes()->findOrFail($id);

            $validated = $request->validate([
                'amount' => ['numeric'],
                'category_id' => [
                    'integer',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('type', 'income')
                              ->where('user_id', Auth::id());
                    }),
                ],
                'date' => ['date'],
                'description' => ['string'],
            ]);

            $income->update($validated);
            return new IncomeResource($income);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Income not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update income',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $income = Auth::user()->incomes()->findOrFail($id);
            $income->delete();

            return response()->json(['message' => 'Income deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Income not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete income',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
