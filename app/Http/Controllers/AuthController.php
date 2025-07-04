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
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException;
use Illuminate\Validation\ValidationException;  



class AuthController extends Controller
{
    // register the User
    public function register(Request $request)
    {
       try {
            $request->validate([
                'name' => ['required', 'string'],
                'email' => ['required', 'string', 'email', 'unique:users,email'],
                'password' => ['required', 'string', 'confirmed', 'min:8']
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
            'status' => 'success',
            'user' => $user,
            'token' => $token,
            'type' => 'bearer'
        ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        }
        catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while creating the user',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // login
    public function login(Request $request)
    {
        try {
            // validate the request
            $request->validate([
                "email" => ["required", "email"],
                "password" => ["required", "string", 'min:8'],
            ]);

            $credentials = $request->only('email', 'password');
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = Auth::user();

            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);
            $type = 'bearer';
            return response()->json([
                'status' => 'success',
                'user' => $user,
                'token' => $token,
                'type' => 'bearer'
            ], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        }
        catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while logging in',
                'message' => $e->getMessage()
            ], 500);
        }

    }

    // logout

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not invalidate token'], 500);
        }
    }

    /*
    
    Refresh method
    This method invalidates the user Auth 
    token and generates a new token

    */

    public function refresh()
    {
        $token = JWTAuth::getToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 400);
        }

        try {
            $refreshedToken = JWTAuth::refresh($token);
        } 
        catch(TokenBlacklistedException $e){
            return response()->json(['error' => 'Token has been blacklisted'], 401);
        }
        catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token'], 500);
        }

        return response()->json(compact('refreshedToken'));
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
