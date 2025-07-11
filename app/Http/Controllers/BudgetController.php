<?php

namespace App\Http\Controllers;

use App\Http\Resources\BudgetResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try{
            $budgets = Auth::user()->budgets;

            return BudgetResource::collection($budgets);
            
        }catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Transaction not found',
                'message' => 'No transactions available'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching transactions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try{
            $validated = $request->validate([
                'amount' => ['required', 'numeric'],
                'color' => ['required', 'string','regex:/^#([A-Fa-f0-9]{6})$/'],
                'category_id' => [
                    'required',
                    'integer',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('user_id', Auth::id())->where('type', 'expense');
                    }),
                ],
                'year' => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
                'month' => ['required', 'integer', 'min:1', 'max:12'],
            ]);

            $budget = Auth::user()->budgets()->create($validated);

            return new BudgetResource($budget);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while creating the budget',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        try {
            $budget = Auth::user()->budgets()->findOrFail($id);
            return new BudgetResource($budget);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Budget not found',
                'message' => 'The requested budget does not exist'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching the budget',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        try {
            $budget = Auth::user()->budgets()->findOrFail($id);

            $validated = $request->validate([
                'amount' => ['numeric'],
                'color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
                'category_id' => [
                    'integer',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('user_id', Auth::id())->where('type', 'expense');
                    }),
                ],
                'year' => ['integer','min:2000', 'max:'.(date('Y') + 1)],
                'month' => ['integer','min:1', 'max:12'],
            ]);

            $budget->update($validated);

            return new BudgetResource($budget);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Budget not found',
                'message' => 'The requested budget does not exist'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation Error',
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while updating the budget',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            $budget = Auth::user()->budgets()->findOrFail($id);
            $budget->delete();

            return response()->json(['message' => 'Budget deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Budget not found',
                'message' => 'The requested budget does not exist'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting the budget',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
