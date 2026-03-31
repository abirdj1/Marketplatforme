<?php

namespace App\Services;

use App\Models\Entitlement;
use App\Models\Report;
use App\Models\Subscription;
use App\Models\User;

class AccessService
{
    public function canViewReport(?User $user, Report $report): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        $hasEntitlement = Entitlement::query()
            ->where('user_id', $user->id)
            ->where('report_id', $report->id)
            ->exists();

        if ($hasEntitlement) {
            return true;
        }

        return Subscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
            })
            ->exists();
    }

    public function canPreviewReport(?User $user, Report $report): bool
    {
        return true;
    }

    public function canDownloadReport(?User $user, Report $report): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        $subscription = Subscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->with('plan')
            ->latest()
            ->first();

        return (bool) optional($subscription?->plan)->allows_download;
    }

    public function canViewDashboard(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        $subscription = Subscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->with('plan')
            ->latest()
            ->first();

        return (bool) optional($subscription?->plan)->allows_dashboard;
    }
}
