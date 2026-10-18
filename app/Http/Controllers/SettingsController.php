<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DeleteUserAccount;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        public DeleteUserAccount $deleteUserAccount
    ) {}

    public function edit(Request $request): View
    {
        return view('settings.edit', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update([
            'name' => $request->string('name')->value(),
            'email' => $request->string('email')->value(),
            'currency_code' => strtoupper($request->string('currency_code')->value()),
        ]);

        return redirect()->route('settings.edit')->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->update([
            'password' => $request->string('password')->value(),
        ]);

        return redirect()->route('settings.edit')->with('success', 'Password updated successfully.');
    }

    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $this->deleteUserAccount->execute($user);

        return redirect()->route('login')->with('success', 'Your account and data were deleted.');
    }
}
