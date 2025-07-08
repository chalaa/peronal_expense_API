<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpenseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;  
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try{
            $all_expense = Auth::user()->expenses;
            return ExpenseResource::collection($all_expense);

        }catch(ModelNotFoundException $e){
            // if category not found retun 404 resource not found
            return response()->json([
                'error' => 'Expense not found',
                'message' => 'No Expense available'
            ], 404);
        }
        catch(\Exception $e){

            return response()->json([
                'error' => 'An error occurred while fetching expense',
                'message' => $e->getMessage()
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
            'description' => ['required', 'string'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    $query->where('type', 'expense')
                          ->where('user_id', Auth::id());
                }),
            ],
            'date' => ['required', 'date'],
        ]);

            $expense = Auth::user()->expenses()->create($validated);

            return new ExpenseResource($expense);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while creating the expense',
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
            $expense = Auth::user()->expenses()->findOrFail($id);
            return new ExpenseResource($expense);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Expense not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching the expense',
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
            $expense = Auth::user()->expenses()->findOrFail($id);

            $validated = $request->validate([
                'amount' => ['numeric'],
                'description' => ['string'],
                'category_id' => [
                    'integer',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('type', 'expense')
                            ->where('user_id', Auth::id());
                    }),
                ],
                'date' => ['date'],
            ]);

            $expense->update($validated);

            return new ExpenseResource($expense);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Expense not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while updating the expense',
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
            $expense = Auth::user()->expenses()->findOrFail($id);
            $expense->delete();

            return response()->json([
                'message' => 'Expense deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Expense not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting the expense',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
