<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'group',
        'description',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    public static function getDefaults(): array
    {
        return [
            // Transactions
            [
                'slug' => 'void_transactions',
                'name' => 'Void Transactions',
                'group' => 'transactions',
                'description' => 'Can void completed sales transactions',
            ],
            [
                'slug' => 'apply_discounts',
                'name' => 'Apply Discounts',
                'group' => 'transactions',
                'description' => 'Can apply custom discounts to sales',
            ],

            // Reports
            [
                'slug' => 'view_reports',
                'name' => 'View Reports',
                'group' => 'reports',
                'description' => 'Access to sales and inventory reports',
            ],
            [
                'slug' => 'export_reports',
                'name' => 'Export Reports',
                'group' => 'reports',
                'description' => 'Can export reports to PDF/Excel',
            ],

            // Inventory
            [
                'slug' => 'manage_inventory',
                'name' => 'Manage Inventory',
                'group' => 'inventory',
                'description' => 'Can adjust stock levels and manage inventory',
            ],
            [
                'slug' => 'view_cost_prices',
                'name' => 'View Cost Prices',
                'group' => 'inventory',
                'description' => 'Can view product cost prices',
            ],

            // Products
            [
                'slug' => 'manage_products',
                'name' => 'Manage Products',
                'group' => 'products',
                'description' => 'Can create, edit, and delete products',
            ],
            [
                'slug' => 'manage_categories',
                'name' => 'Manage Categories',
                'group' => 'products',
                'description' => 'Can create, edit, and delete categories',
            ],

            // Users
            [
                'slug' => 'manage_users',
                'name' => 'Manage Users',
                'group' => 'users',
                'description' => 'Can create, edit, and deactivate staff accounts',
            ],

            // Branches
            [
                'slug' => 'manage_branches',
                'name' => 'Manage Branches',
                'group' => 'branches',
                'description' => 'Can create and configure branches',
            ],

            // Settings
            [
                'slug' => 'manage_settings',
                'name' => 'Manage Settings',
                'group' => 'settings',
                'description' => 'Can modify company settings',
            ],
        ];
    }

    public static function getGroupedDefaults(): array
    {
        return collect(self::getDefaults())->groupBy('group')->toArray();
    }
}
