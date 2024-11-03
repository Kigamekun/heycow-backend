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
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:225', //'string', 'max:255',
            'address' => 'nullable|string|max:255',// 'string', 'max:255',
            'email' => 'required|lowercase|email|unique|max:255',// 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class,
            'phone_number' => 'nullable|string|max:225',// 'string', 'max:255',
            "gender" => "nullable|string|in:male,female",
            "bio" => "nullable|string|max:500",// "string",
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $name = $request->input('name');
        $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=128&background=random';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            "phone_number" => $request->phone_number,
            'gender' => $request->gender,
            'bio' => $request->bio,
            'email_verified_at' => now(),
            'password' => Hash::make($request->password),
            'avatar' => $avatar,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
