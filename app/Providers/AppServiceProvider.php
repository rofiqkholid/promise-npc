<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }

        if (str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer(['layouts.header', 'components.stock-alert-modal'], function ($view) {
            $view->with('stockAlerts', collect([]))
                ->with('stockAlertAutoOpen', false);
        });

        View::composer(['layouts.header', 'components.stock-alert-modal', 'components.ecn-alert-modal'], function ($view) {
            // Hitung part aktif yang memiliki ECN update
            $ecnQuery = \App\Models\NpcPart::with(['event.customerCategory', 'product'])
                ->whereNotIn('status', ['FINISHED', 'CLOSED'])
                ->whereNotNull('part_revision_id')
                ->whereHas('product.docPackage', function ($query) {
                    $query->whereColumn('doc_packages.current_revision_id', '!=', 'npc_parts.part_revision_id');
                });

            $ecnNotificationCount = $ecnQuery->count();
            $ecnUpdatedParts = $ecnQuery->latest()->take(10)->get();

            $view->with('stockAlerts', collect([]))
                ->with('stockAlertAutoOpen', false)
                ->with('ecnNotificationCount', $ecnNotificationCount)
                ->with('ecnUpdatedParts', $ecnUpdatedParts);
        });

        View::composer('layouts.sidebar', function ($view) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $sidebarMenus = collect();
            $userRoleCode = 'guest';

            if ($user) {
                // Tentukan userRoleCode untuk UI jika diperlukan (misal: ambil role pertama)
                $firstRole = $user->roles->first();
                $userRoleCode = $firstRole ? $firstRole->code : 'user';

                if ($userRoleCode === 'admin') {
                    // Admin melihat semua menu aktif
                    $sidebarMenus = \App\Models\NpcMenu::whereNull('parent_id')
                        ->with(['children' => function ($q) {
                            $q->where('is_active', true); // Removed orderBy('sort_order') to avoid duplicate order by in SQL Server
                        }])
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->get();
                } else {
                    // User lain hanya melihat menu yang diizinkan
                    $accessibleMenus = $user->getAllAccessibleMenus()->where('is_active', true);

                    $sidebarMenus = $accessibleMenus->whereNull('parent_id')->sortBy('sort_order')->values();

                    $sidebarMenus->map(function ($menu) use ($accessibleMenus) {
                        $children = $accessibleMenus->where('parent_id', $menu->id)->sortBy('sort_order')->values();
                        $menu->setRelation('children', $children);
                        return $menu;
                    });
                }
            }

            $view->with('sidebarMenus', $sidebarMenus)
                ->with('userRoleCode', $userRoleCode);
        });
    }
}
