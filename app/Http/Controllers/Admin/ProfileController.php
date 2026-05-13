<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'user' => $request->user()->only(['id', 'name', 'username', 'email', 'phone', 'locale']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:64'],
            'locale' => ['nullable', 'string', 'max:8'],
        ]);
        $user->update($data);

        return back()->with('success', __('messages.success.updated', ['resource' => __('messages.my_profile')]));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        $user = $request->user();
        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages(['current_password' => __('messages.invalid_credentials')]);
        }
        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', __('messages.success.updated', ['resource' => __('messages.password')]));
    }
}
