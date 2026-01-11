<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Company;

class AdminNotificationService
{
    public function notifyNewCompanyRegistration(Company $company): AdminNotification
    {
        return AdminNotification::create([
            'type' => AdminNotification::TYPE_COMPANY_REGISTERED,
            'title' => 'New Company Registration',
            'message' => "{$company->name} has submitted a registration request and is awaiting approval.",
            'data' => [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'company_email' => $company->email,
            ],
            'icon' => 'bi-building-add',
            'color' => 'warning',
            'action_url' => route('admin.companies.show', $company),
        ]);
    }

    public function notifyCompanyApproved(Company $company): AdminNotification
    {
        return AdminNotification::create([
            'type' => AdminNotification::TYPE_COMPANY_APPROVED,
            'title' => 'Company Approved',
            'message' => "{$company->name} has been approved and can now access the POS system.",
            'data' => [
                'company_id' => $company->id,
                'company_name' => $company->name,
            ],
            'icon' => 'bi-check-circle',
            'color' => 'success',
            'action_url' => route('admin.companies.show', $company),
        ]);
    }

    public function notifyCompanyRejected(Company $company): AdminNotification
    {
        return AdminNotification::create([
            'type' => AdminNotification::TYPE_COMPANY_REJECTED,
            'title' => 'Company Rejected',
            'message' => "{$company->name} registration has been rejected.",
            'data' => [
                'company_id' => $company->id,
                'company_name' => $company->name,
            ],
            'icon' => 'bi-x-circle',
            'color' => 'danger',
            'action_url' => route('admin.companies.show', $company),
        ]);
    }

    public function notifySystemWarning(string $title, string $message, ?string $actionUrl = null): AdminNotification
    {
        return AdminNotification::create([
            'type' => AdminNotification::TYPE_SYSTEM_WARNING,
            'title' => $title,
            'message' => $message,
            'icon' => 'bi-exclamation-triangle',
            'color' => 'danger',
            'action_url' => $actionUrl,
        ]);
    }

    public function notifySystemInfo(string $title, string $message, ?string $actionUrl = null): AdminNotification
    {
        return AdminNotification::create([
            'type' => AdminNotification::TYPE_SYSTEM_INFO,
            'title' => $title,
            'message' => $message,
            'icon' => 'bi-info-circle',
            'color' => 'info',
            'action_url' => $actionUrl,
        ]);
    }

    public function getUnreadCount(): int
    {
        return AdminNotification::unread()->count();
    }

    public function getRecent(int $limit = 5)
    {
        return AdminNotification::latest()->limit($limit)->get();
    }

    public function getUnreadRecent(int $limit = 5)
    {
        return AdminNotification::unread()->latest()->limit($limit)->get();
    }

    public function markAllAsRead(): void
    {
        AdminNotification::unread()->update(['read_at' => now()]);
    }

    public function markAsRead(AdminNotification $notification): void
    {
        $notification->markAsRead();
    }
}
