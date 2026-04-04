<?php

namespace App\Http\Middleware;

use App\Support\SettingCache;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $settings = SettingCache::all();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'permissions' => [
                    'can_view_reports' => $user ? in_array($user->role, ['principal', 'librarian', 'teacher'], true) : false,
                    'can_manage_circulation' => $user ? in_array($user->role, ['librarian', 'teacher'], true) : false,
                    'can_manage_catalog' => $user ? $user->role === 'librarian' : false,
                    'can_manage_settings' => $user ? $user->role === 'librarian' : false,
                ],
            ],
            'settings' => $settings,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
            ],
        ];
    }
}
