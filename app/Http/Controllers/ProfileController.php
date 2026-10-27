<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordChangeRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ImageWebpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the user's profile and account settings page.
     */
    public function edit(): View
    {
        $user = Auth::user()->load(['roles', 'permissions', 'school']);

        // Calculate profile completion percentage
        $fields = [
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'photo'      => $user->photo,
            'gender'     => $user->gender,
            'birthday'   => $user->birthday,
            'address'    => $user->address,
            'city'       => $user->city,
            'blood_type' => $user->blood_type,
        ];
        $filledCount = count(array_filter($fields, fn($val) => !empty($val)));
        $completionPercentage = (int) round(($filledCount / count($fields)) * 100);

        return view('profile.edit', compact('user', 'completionPercentage'));
    }

    /**
     * Update user profile personal and contact information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();

            // Handle photo upload if present
            if ($request->hasFile('photo')) {
                // Remove existing photo if any
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }

                $validated['photo'] = ImageWebpService::storeAsWebp(
                    $request->file('photo'),
                    'profile/photos',
                    'public'
                );
            } else {
                unset($validated['photo']);
            }

            // If email is changed, keep verified timestamp updated
            if (isset($validated['email']) && $validated['email'] !== $user->email) {
                $validated['email_verified_at'] = now();
            }

            $user->fill($validated);
            $user->save();

            return redirect()->route('profile.edit')
                ->with('status', 'Your profile details have been updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Profile update failed: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return back()->withInput()->with('error', 'Unable to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Update account password.
     */
    public function updatePassword(PasswordChangeRequest $request): RedirectResponse
    {
        try {
            $user = Auth::user();
            $currentPassword = $request->input('current_password', $request->input('old_password'));
            $newPassword = $request->input('password', $request->input('new_password'));

            if (!Hash::check($currentPassword, $user->password)) {
                return back()->with('error', 'The current password you entered is incorrect.');
            }

            $user->password = Hash::make($newPassword);
            $user->must_change_password = false;
            $user->save();

            return redirect()->route('profile.edit', ['#security'])
                ->with('status', 'Your password has been changed successfully.');
        } catch (\Throwable $e) {
            Log::error('Password change failed: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return back()->with('error', 'Unable to change password: ' . $e->getMessage());
        }
    }

    /**
     * Remove the user's avatar/photo.
     */
    public function removePhoto(): RedirectResponse
    {
        try {
            $user = Auth::user();

            if ($user->photo) {
                if (Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }
                $user->photo = null;
                $user->save();
            }

            return redirect()->route('profile.edit')
                ->with('status', 'Profile photo removed successfully.');
        } catch (\Throwable $e) {
            Log::error('Photo removal failed: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return back()->with('error', 'Unable to remove photo: ' . $e->getMessage());
        }
    }
}
