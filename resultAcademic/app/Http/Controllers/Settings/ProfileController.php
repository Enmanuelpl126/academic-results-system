<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\Department;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        // Contadores: publicaciones, premios, reconocimientos y eventos asociados
        $counters = [
            'publications_count' => $user->publications()->count(),
            'awards_count' => $user->awards()->count(),
            'recognitions_count' => $user->recognitions()->count(),
            'events_count' => $user->events()->count(),
        ];

        $departments = Department::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('settings/Profile', array_merge([
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'departments' => $departments,
        ], $counters));
    }

    /**
     * Show public profile page (no navbar) for authenticated user.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();

        $counters = [
            'publications_count' => $user->publications()->count(),
            'awards_count' => $user->awards()->count(),
            'recognitions_count' => $user->recognitions()->count(),
            'events_count' => $user->events()->count(),
        ];

        $departments = Department::query()->select('id', 'name')->orderBy('name')->get();

        return Inertia::render('ProfileView', array_merge([
            'user' => $user,
            'departments' => $departments,
        ], $counters));
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

        // Volver a la página anterior (por ejemplo, la vista pública del perfil) en lugar de forzar ir a settings/profile
        return back()->with('status', 'profile-updated');
    }

    /**
     * Delete the user's profile.
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

        return redirect('/');
    }
}
