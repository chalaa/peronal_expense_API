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
    public function register(Request $request){
        $request->validate([
            'name' => ['required','string'],
            'email'=> ['required','string','email'],
            'password'=> ['required','string','confirmed','min:8']
        ]);

        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=> Hash::make($request->password),
        ]);

        // Authenticate user
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status'=> 'success',
            'user'=> $user,
            'authorisation' =>  [
                'token'=> $token,
                'type' => 'bearer'
            ]
        ],200);

    }

    // login
    public function login(Request $request){
        // validate the request
        $request->validate([
            "email"=> ["required","email"],
            "password"=> ["required","string",'min:8'],
        ]);
        
        $credential = $request->only('email','password');

        if (!Auth::attempt($credential)){
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Credential'
            ],401);
        }

        // Authenticate user
        $user = Auth::user();
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'status'=> 'success',
            'user'=> $user,
            'authorisation' =>  [
                'token'=> $token,
                'type' => 'bearer'
            ]
        ],200);
    }

    // logout

    public function logout(){
        Auth::logout();
        return response()->json([
            'status'=> 'success',
            'message'=> 'Successfully logged out'
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

    public function dashboard( ) {

        $all_expense = Auth::user()->expenses;
        $all_income = Auth::user()->incomes;
        
       
        function calculateMonthlyAmounts($expenses) {
            $currentDate = new DateTime();

            // Initialize an array with 0 amounts for the last 6 months
            $monthlyAmounts = array_fill(0, 6, 0);
            $monthNames = [];
            
            // Loop through the last 6 months
            for ($i = 0; $i <= 6; $i++) {
                $monthNames[$i] = $currentDate->format('F');
                $monthlyAmounts[$i] = 0;
                $currentDate->modify('-1 month');
            }
            
            // Reset current date to the end of the last month to start filtering
            $currentDate = new DateTime();
            $endDate = clone $currentDate;
            $startDate = (clone $currentDate)->modify('-5 months')->modify('first day of this month');
            
            // Loop through expenses to sum amounts by month for the last 6 months
            foreach ($expenses as $expense) {
                $expenseDate = new DateTime($expense['date']);
                if ($expenseDate >= $startDate && $expenseDate <= $endDate) {
                    $index = 5 - $expenseDate->diff($startDate)->m;
                    $monthlyAmounts[$index] += $expense['amount'];
                }
            }
            
            // Map the amounts to their respective month names
            $monthlyAmountsWithNames = array_combine(array_reverse($monthNames), array_reverse($monthlyAmounts));
            
            // Print the results
            return $monthlyAmountsWithNames ;
        }
        
        return [calculateMonthlyAmounts($all_expense),calculateMonthlyAmounts($all_income)];
    }
}
