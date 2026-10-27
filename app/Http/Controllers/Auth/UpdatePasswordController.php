<?php

namespace App\Http\Controllers\Auth;

use App\Interfaces\UserInterface;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\PasswordChangeRequest;

class UpdatePasswordController extends Controller
{
    protected $userRepository;

    public function __construct(UserInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function edit() {
        return view('auth.passwords.edit');
    }

    public function update(PasswordChangeRequest $request) {
        $user = auth()->user();
        $currentPassword = $request->input('current_password', $request->input('old_password'));
        $newPassword = $request->input('password', $request->input('new_password'));

        if (!Hash::check($currentPassword, $user->password)) {
            return back()->with('error', 'The provided current password does not match your account password.');
        }

        try {
            $this->userRepository->changePassword($newPassword);

            return back()->with('status', 'Password changed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
