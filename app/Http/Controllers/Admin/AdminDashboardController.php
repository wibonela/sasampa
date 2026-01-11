<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sanduku;
use App\Models\UserLimitRequest;
use App\Services\AdminDashboardService;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct(
        private AdminDashboardService $dashboardService,
        private AdminNotificationService $notificationService
    ) {}

    public function index()
    {
        $companyStats = $this->dashboardService->getCompanyStats();
        $totalUsers = $this->dashboardService->getTotalUsers();
        $usersByRole = $this->dashboardService->getUsersByRole();
        $transactionStats = $this->dashboardService->getTransactionStats();
        $topCompanies = $this->dashboardService->getTopCompanies(5);
        $recentActivities = $this->dashboardService->getRecentActivities(10);
        $systemHealth = $this->dashboardService->getSystemHealth();
        $pendingCompanies = $this->dashboardService->getPendingCompanies();
        $unreadNotifications = $this->notificationService->getUnreadCount();

        // Additional stats
        $userLimitRequests = UserLimitRequest::where('status', 'pending')->count();
        $feedbackCount = Sanduku::where('status', 'new')->count();

        // Chart data
        $registrationTrends = $this->dashboardService->getRegistrationTrends(14);
        $revenueTrends = $this->dashboardService->getRevenueTrends('daily', 14);

        return view('admin.dashboard.index', compact(
            'companyStats',
            'totalUsers',
            'usersByRole',
            'transactionStats',
            'topCompanies',
            'recentActivities',
            'systemHealth',
            'pendingCompanies',
            'unreadNotifications',
            'registrationTrends',
            'revenueTrends',
            'userLimitRequests',
            'feedbackCount'
        ));
    }

    public function chartData(Request $request)
    {
        $period = $request->get('period', 'daily');
        $limit = $request->get('limit', 14);
        $type = $request->get('type', 'revenue');

        if ($type === 'revenue') {
            $data = $this->dashboardService->getRevenueTrends($period, $limit);
        } else {
            $data = $this->dashboardService->getRegistrationTrends($limit);
        }

        return response()->json($data);
    }
}
