<?php

namespace App\Http\Middleware;

use App\Models\AssetRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->withoutRelations(),
                'roles' => $request->user()?->getRoleNames() ?? [],
            ],
            'isAdmin' => $request->user()?->hasRole('admin'),
            'flash' => [
                'success' => session('success'),
                'error' => session('error') ?? ($request->cookie('_error') ? [
                    'message' => $request->cookie('_error'),
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                ] : null),
            ],
            'pending_requests_count' => Auth::check()
    ? AssetRequest::where('status', 'pending')->count()
    : 0,
            'unread_notifications_count' => Auth::user()?->unreadNotifications()->count() ?? 0,
            'recent_notifications' => Auth::user()?->notifications()->take(5)->get() ?? [],
        ];
    }
}
