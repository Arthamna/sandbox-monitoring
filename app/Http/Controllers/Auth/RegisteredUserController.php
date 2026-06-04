<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        
        $request->validate([
            'username' => 'required|string|max:255',
            'role' => 'required|string|in:admin,player',
            'password' => 'required',
        ]);
        
        $user = User::create([
            'username' => $request->username,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
        
        // event(new Registered($user));
        // Auth::login($user);
        // return to_route('dashboard');
    }
}
