<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use DateTime;
use PHPOpenSourceSaver\JWTAuth\Contracts\Providers\JWT;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWTAuth as JWTAuthJWTAuth;

class AuthController extends Controller
{
    // register the User
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', 'min:8']
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Authenticate user
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' =>  [
                'token' => $token,
                'type' => 'bearer'
            ]
        ], 200);
    }

    // login
    public function login(Request $request)
    {
        // validate the request
        $request->validate([
            "email" => ["required", "email"],
            "password" => ["required", "string", 'min:8'],
        ]);

        $credential = $request->only('email', 'password');

        if (!Auth::attempt($credential)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Credential'
            ], 401);
        }

        // Authenticate user
        $user = Auth::user();
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' =>  [
                'token' => $token,
                'type' => 'bearer'
            ]
        ], 200);
    }

    // logout

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    /*
    
    Refresh method
    This method invalidates the user Auth 
    token and generates a new token

    */

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    // dashboard data

    public function dashboard()
    {

        $all_expense = Auth::user()->expenses;
        $all_income = Auth::user()->incomes;


        function calculateMonthlyAmounts($expenses)
        {
            $currentDate = new DateTime();
            $monthlyAmounts = array_fill(0, 6, 0);
            $monthNames = [];
        
            // Prepare month names and initialize monthly totals
            for ($i = 0; $i < 6; $i++) {
                $monthNames[$i] = $currentDate->format('F');
                $monthlyAmounts[$i] = 0;
                $currentDate->modify('-1 month');
            }
        
            $currentDate = new DateTime();
            $endDate = clone $currentDate;
            $startDate = (clone $currentDate)->modify('-5 months')->modify('first day of this month');
        
            // Loop through expenses to sum amounts by month
            foreach ($expenses as $expense) {
                $expenseDate = new DateTime($expense['date']);
                if ($expenseDate >= $startDate && $expenseDate <= $endDate) {
                    $index = 5 - $expenseDate->diff($startDate)->m;
                    $monthlyAmounts[$index] += $expense['amount'];
                }
            }
        
            // Combine month names and total amounts into an indexed array
            $monthlyAmountsWithNames = [];
            foreach (array_reverse($monthlyAmounts) as $index => $amount) {
                $monthlyAmountsWithNames[$index] = [
                    'month' => $monthNames[5 - $index],
                    'amount' => $amount
                ];
            }
        
            return $monthlyAmountsWithNames;
        }

        function calculateCategoryTotals($expenses)
{
    // Get the current date
    $currentDate = new DateTime();

    // Calculate the start date for the last 6 months
    $startDate = (clone $currentDate)->modify('first day of this month');
    $startDate->modify('-5 months');

    // Initialize an array to store category totals
    $categoryTotals = [];

    // Loop through expenses to sum amounts by category for the last 6 months
    foreach ($expenses as $expense) {
        $expenseDate = new DateTime($expense['date']);
        if ($expenseDate >= $startDate) {
            $categoryName = $expense['category']['name'];
            if (!isset($categoryTotals[$categoryName])) {
                $categoryTotals[$categoryName] = 0;
            }
            $categoryTotals[$categoryName] += $expense['amount'];
        }
    }

    // Transform the totals into the desired format
    $indexedCategoryTotals = [];
    $index = 0;
    foreach ($categoryTotals as $categoryName => $totalAmount) {
        $indexedCategoryTotals[$index] = [
            'category' => $categoryName,
            'amount' => $totalAmount
        ];
        $index++;
    }

    return $indexedCategoryTotals;
}

        return response()->json([
            "expense" => calculateMonthlyAmounts($all_expense),
            "income" => calculateMonthlyAmounts($all_income),
            "category" => calculateCategoryTotals($all_expense)
        ]);
    }
}
