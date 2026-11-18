<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Notify;
use App\Support\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function showRegister() { return view('auth.register'); }

    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required']);
        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->role !== 'customer') {
            return back()->withErrors(['email' => 'Sign in through /admin/login — this is a staff account.'])->onlyInput('email');
        }
        if ($user && $user->is_blocked) {
            return back()->withErrors(['email' => 'This account has been suspended. Contact support.'])->onlyInput('email');
        }
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            ActivityLogger::log('login', 'auth', $request->user()->email);
            return redirect()->intended(route('home'));
        }
        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => 'customer',
            'is_admin' => false,
            'api_token' => Str::random(60),
        ]);
        Auth::login($user);
        \App\Models\NotificationPreference::for($user);
        Notify::admin('new_customer', 'New customer registered', $user->name . ' (' . $user->email . ')', route('admin.customers.show', $user));
        ActivityLogger::log('register', 'auth', $user->email);
        $verifyUrl = \Illuminate\Support\Facades\URL::signedRoute('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)], now()->addDays(7));
        \App\Support\Notifier::email($user, 'welcome', 'Welcome to ' . \App\Support\Settings::get('store_name'), "Hi {$user->name},\n\nWelcome to " . \App\Support\Settings::get('store_name') . "! Your account has been created.\n\nPlease verify your email by clicking:\n{$verifyUrl}\n\nStart shopping: " . route('catalog') . "\n\nNeed help? Reply to this email or visit our Help Center.", 'welcome:' . $user->id . ':email');
        if ($user->phone) {
            \App\Support\PhoneVerificationService::sendCode($user);
        }
        return redirect()->route('home')->with('success', 'Account created! Check your inbox for a welcome email.' . ($user->phone ? ' We sent a verification code to your phone.' : ''));
    }

    public function logout(Request $request)
    {
        ActivityLogger::log('logout', 'auth', $request->user()?->email);
        Auth::logout();
        Auth::guard('admin')->logout();
        Cart::clear();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
