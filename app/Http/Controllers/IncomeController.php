<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //get all auth user income list
        $all_incomes = Auth::user()->incomes;
        return response()->json($all_incomes);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validate the request
        $request->validate([
            "amount" => ['required','numeric'],
            "category_id" => ['required','integer'],
            "date" => ['required','date'],
            "description" => ['required','string'],
        ]);

        $income = Auth::user()->incomes()->create($request->all());

        return response()->json([
            'message' => 'Income Added successfully',
            'income' => $income
        ],201);


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $all_income = Auth::user()->incomes;
        $income = $all_income->find($id);

        if (!$income) {
            return response()->json(['message' => 'Income not found'], 404);
        }

        return response()->json($income);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            "amount" => ['numeric'],
            "category_id" => ['integer'],
            "date" => ['date'],
            "description" => ['string'],
        ]);

        $all_income = Auth::user()->incomes;
        $income = $all_income->find($id);

        if (!$income) {
            return response()->json(['message' => 'Income not found'], 404);
        }

        $income->update($request->all());

        return response()->json([
            'message' => 'Income updated successfully',
            'income' => $income
        ]);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $all_income = Auth::user()->incomes;
        $income = $all_income->find($id);
        if (!$income) {
            return response()->json(['message' => 'Income not found'], 404);
        }

        $income->delete();

        return response()->json(['message' => 'Income deleted successfully']);
    }
}
