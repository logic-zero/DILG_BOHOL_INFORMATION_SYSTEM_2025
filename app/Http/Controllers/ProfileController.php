<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ProvincialDirector;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'pdMessage' => ProvincialDirector::first(),
            'status' => session('status'),
            'auth' => [
                'user' => $request->user()->load('roles')
            ]
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    public function updateImage(Request $request): RedirectResponse
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:20480',
        ]);

        $user = $request->user();
        $uploadPath = public_path('profile_images');
        File::ensureDirectoryExists($uploadPath);

        if ($user->profile_image) {
            $oldImagePath = public_path('profile_images/' . $user->profile_image);
            if (File::exists($oldImagePath)) {
                File::delete($oldImagePath);
            }
        }

        $fileName = Str::random(20) . '.' . $request->file('profile_image')->extension();
        $request->file('profile_image')->move($uploadPath, $fileName);
        $user->profile_image = $fileName;
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'image-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    //PD message only
    public function storePDMessage(Request $request)
    {
        if (strtolower(auth()->user()->position) !== strtolower('Provincial Director')) {
            return redirect()->route('dashboard')->with('error', 'You are not authorized to perform this action!');
        }

        $validated = $request->validate([
            'message' => 'required|string',
            ]);

        ProvincialDirector::truncate();
        ProvincialDirector::create($validated);

        return redirect()->route('profile.edit')->with('success', "Provincial Director's Message Added Successfully!");
    }
}
