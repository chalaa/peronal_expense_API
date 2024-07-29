<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $all_expense = Auth::user()->expenses;
        return response()->json($all_expense);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'amount' => ['required','numeric'],
            'description' =>['required','string'] ,
            'category_id' => ['required','integer'] ,
            'date' => ['required','date'],
        ]);

        $expense = Auth::user()->expenses()->create($request->all());

        return response()->json([
            'message' => 'Expense created successfully',
            'expense' => $expense
        ], 201);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $all_expense = Auth::user()->expenses;
        $expense = $all_expense->find($id);

        if (!$expense) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        return response()->json($expense);  
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'amount'=> ['numeric'],
            'description'=> ['string'],
            'category_id'=> ['integer'],
            'date'=> ['date'],
        ]);

        $all_expense = Auth::user()->expenses;
        $expense = $all_expense->find($id);
        if (!$expense) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        $expense->update($request->all());

        return response()->json([
            'message' => 'Expense updated successfully',
            'expense' => $expense
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $all_expense = Auth::user()->expenses;
        $expense = $all_expense->find($id);

        if (!$expense) {
            return response()->json(['message'=> 'Expense not Found'],404);
        }
        $expense->delete();
        return response()->json([
            'message' => 'Expense deleted successfully'
        ]);
    }
}
