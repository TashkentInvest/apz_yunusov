<?php
// app/Http/Controllers/AuthController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->intended('/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email manzilini kiriting',
            'email.email' => 'To\'g\'ri email manzilini kiriting',
            'password.required' => 'Parolni kiriting',
        ]);

        $credentials = $request->only('email', 'password');

        // Check if user exists and is active
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Bunday email manzili topilmadi.',
            ])->withInput($request->except('password'));
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'email' => 'Sizning hisobingiz bloklangan. Administrator bilan bog\'laning.',
            ])->withInput($request->except('password'));
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Update last login time
            $user->update(['last_login_at' => now()]);

            // Redirect based on role
            switch (auth()->user()->role) {
                case 'admin':
                    return redirect()->intended('/dashboard')->with('success', 'Xush kelibsiz, Administrator!');
                case 'manager':
                    return redirect()->intended('/contracts')->with('success', 'Xush kelibsiz, ' . auth()->user()->name . '!');
                case 'employee':
                    return redirect()->intended('/contracts')->with('success', 'Xush kelibsiz, ' . auth()->user()->name . '!');
                default:
                    return redirect()->intended('/')->with('success', 'Muvaffaqiyatli kirdingiz!');
            }
        }

        return back()->withErrors([
            'password' => 'Parol noto\'g\'ri.',
        ])->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Siz muvaffaqiyatli chiqdingiz!');
    }

    public function dashboard()
    {
        $user = auth()->user();

        // Basic dashboard stats
        $stats = [
            'total_contracts' => \App\Models\Contract::count(),
            'active_contracts' => \App\Models\Contract::where('status_id', 1)->count(),
            'total_users' => User::where('is_active', true)->count(),
            'user_role' => $user->role,
        ];

        return view('dashboard', compact('user', 'stats'));
    }
}
