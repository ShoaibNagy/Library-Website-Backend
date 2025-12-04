<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 *
 * APIs for managing user authentication
 */
class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email of the user. Example: john@example.com
     * @bodyParam password string required The password of the user. Example: password
     * @bodyParam password_confirmation string required The password confirmation. Example: password
     * 
     * @response 201 {
     *  "access_token": "1|laravel_sanctum_token",
     *  "token_type": "Bearer",
     *  "user": {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john@example.com",
     *      "role": "member",
     *      "status": "active",
     *      "created_at": "2023-01-01T00:00:00.000000Z",
     *      "updated_at": "2023-01-01T00:00:00.000000Z"
     *  }
     * }
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'member', // Default role
            'status' => 'active',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }

    /**
     * Login a user
     *
     * @bodyParam email string required The email of the user. Example: john@example.com
     * @bodyParam password string required The password of the user. Example: password
     * 
     * @response {
     *  "access_token": "1|laravel_sanctum_token",
     *  "token_type": "Bearer",
     *  "user": {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john@example.com",
     *      "role": "member",
     *      "status": "active",
     *      "created_at": "2023-01-01T00:00:00.000000Z",
     *      "updated_at": "2023-01-01T00:00:00.000000Z"
     *  }
     * }
     * @response 422 {
     *  "message": "The given data was invalid.",
     *  "errors": {
     *      "email": [
     *          "The provided credentials are incorrect."
     *      ]
     *  }
     * }
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
             throw ValidationException::withMessages([
                'email' => ['Your account is suspended.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Logout the authenticated user
     *
     * @response {
     *  "message": "Logged out successfully"
     * }
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Send password reset link
     *
     * @bodyParam email string required The email of the user. Example: john@example.com
     * 
     * @response {
     *  "status": "We have emailed your password reset link!"
     * }
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? response()->json(['status' => __($status)])
            : response()->json(['email' => __($status)], 400);
    }

    /**
     * Reset password
     *
     * @bodyParam token string required The password reset token.
     * @bodyParam email string required The email of the user. Example: john@example.com
     * @bodyParam password string required The new password. Example: newpassword
     * @bodyParam password_confirmation string required The new password confirmation. Example: newpassword
     * 
     * @response {
     *  "status": "Your password has been reset!"
     * }
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(\Illuminate\Support\Str::random(60));

                $user->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? response()->json(['status' => __($status)])
            : response()->json(['email' => __($status)], 400);
    }
}
