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

        // New analytics data
        $customerStats = $this->dashboardService->getCustomerStats();
        $customerBreakdown = $this->dashboardService->getCustomerTransactionBreakdown();
        $customerGrowth = $this->dashboardService->getCustomerGrowthTrend(30);
        $productCatalogStats = $this->dashboardService->getProductCatalogStats();
        $trendingProducts = $this->dashboardService->getTrendingProducts(10);
        $lowStockAlerts = $this->dashboardService->getLowStockAlerts(10);
        $activeCompanyStats = $this->dashboardService->getActiveCompanyStats();
        $featureAdoption = $this->dashboardService->getFeatureAdoptionRates();
        $activeCompanyTrend = $this->dashboardService->getActiveCompanyTrend(14);
        $revenuePerCompany = $this->dashboardService->getRevenuePerCompany();
        $decliningCompanies = $this->dashboardService->getDecliningCompanies(5);
        $mobileAppStats = $this->dashboardService->getMobileAppStats();
        $sandukuSummary = $this->dashboardService->getSandukuSummary();
        $companyAging = $this->dashboardService->getCompanyAgingDistribution();
        $onboardingFunnel = $this->dashboardService->getOnboardingFunnel();

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
            'feedbackCount',
            'customerStats',
            'customerBreakdown',
            'customerGrowth',
            'productCatalogStats',
            'trendingProducts',
            'lowStockAlerts',
            'activeCompanyStats',
            'featureAdoption',
            'activeCompanyTrend',
            'revenuePerCompany',
            'decliningCompanies',
            'mobileAppStats',
            'sandukuSummary',
            'companyAging',
            'onboardingFunnel'
        ));
    }

    public function chartData(Request $request)
    {
        $period = $request->get('period', 'daily');
        $limit = (int) $request->get('limit', 14);
        $type = $request->get('type', 'revenue');

        $data = match ($type) {
            'revenue' => $this->dashboardService->getRevenueTrends($period, $limit),
            'customer_growth' => $this->dashboardService->getCustomerGrowthTrend($limit),
            'active_companies' => $this->dashboardService->getActiveCompanyTrend($limit),
            default => $this->dashboardService->getRegistrationTrends($limit),
        };

        return response()->json($data);
    }
}
