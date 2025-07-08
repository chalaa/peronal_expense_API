<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\TransactionResource;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $transactions = Auth::user()->transactions; 
            
            $all_transaction = TransactionResource::collection($transactions);
            return $all_transaction;

        } catch (ModelNotFoundException $e) {
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
        try {
            $validated = $request->validate([
                'amount' => ['required', 'numeric'],
                'description' => ['nullable', 'string'],
                'type' => ['required', Rule::in(['income', 'expense'])],
                'date' => ['required', 'date'],
                'category_id' => [
                    'required',
                    'integer',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('user_id', Auth::id());
                    }),
                ],
            ]);

            $transaction = Auth::user()->transactions()->create($validated);

            return new TransactionResource($transaction);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while creating the transaction',
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
            $transaction = Auth::user()->transactions()->findOrFail($id);
            return new TransactionResource($transaction);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Transaction not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching the transaction',
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
            $transaction = Auth::user()->transactions()->findOrFail($id);

            $validated = $request->validate([
                'amount' => ['numeric'],
                'description' => ['nullable', 'string'],
                'type' => [Rule::in(['income', 'expense'])],
                'date' => ['date'],
                'category_id' => [
                    'integer',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('user_id', Auth::id());
                    }),
                ],
            ]);

            $transaction->update($validated);

            return new TransactionResource($transaction);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Transaction not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while updating the transaction',
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
            $transaction = Auth::user()->transactions()->findOrFail($id);
            $transaction->delete();

            return response()->json([
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Transaction not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting the transaction',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
