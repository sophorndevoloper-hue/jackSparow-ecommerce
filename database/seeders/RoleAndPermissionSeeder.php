<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\FrontendUser;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Dynamically extract all backend permissions from backend_menus.json
        $menuDefs = BackendMenuSeeder::getSystemMenuDefinitions();
        $permissions = [];

        foreach ($menuDefs as $def) {
            if (! empty($def['view_permission'])) {
                foreach (explode('|', $def['view_permission']) as $p) {
                    $pName = trim($p);
                    if ($pName !== '') {
                        $permissions[] = $pName;
                    }
                }
            }
            if (! empty($def['actions'])) {
                foreach ($def['actions'] as $act) {
                    if (! empty($act['permission'])) {
                        foreach (explode('|', $act['permission']) as $p) {
                            $pName = trim($p);
                            if ($pName !== '') {
                                $permissions[] = $pName;
                            }
                        }
                    }
                }
            }
        }

        // Dedicated stock permissions
        $permissions[] = 'view stock';
        $permissions[] = 'create stock';
        $permissions[] = 'edit stock';
        $permissions[] = 'delete stock';

        $permissions = array_values(array_unique(array_filter($permissions)));

        // Clean up legacy 'web' guard roles & permissions
        Role::where('guard_name', 'web')->delete();
        Permission::where('guard_name', 'web')->delete();

        // Clean up any orphan backend permissions not in the defined list
        Permission::where('guard_name', 'backend')
            ->whereNotIn('name', $permissions)
            ->delete();

        // 1. Create all admin permissions for backend guard
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'backend']);
        }

        // Create storefront customer permissions for frontend guard
        $frontendPermissions = [
            'view profile',
            'edit profile',
            'view customer orders',
        ];

        // Clean up legacy permissions from frontend guard that belong exclusively to backend
        Permission::where('guard_name', 'frontend')
            ->whereNotIn('name', $frontendPermissions)
            ->delete();

        foreach ($frontendPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'frontend']);
        }

        // Clean up legacy customer role from system roles
        Role::where('name', 'customer')->delete();

        // 2. Create roles for backend guard
        $superadminBackendRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
        $adminBackendRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);

        // Give all backend permissions to superadmin and admin roles
        $allBackendPermissions = Permission::where('guard_name', 'backend')->get();
        $superadminBackendRole->syncPermissions($allBackendPermissions);
        $adminBackendRole->syncPermissions($allBackendPermissions);

        // 3. Ensure superadmin and admin accounts exist, are approved, and have password 'password'
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_approved' => true,
                'approved_at' => now(),
            ]
        );
        $superadmin->update([
            'password' => Hash::make('password'),
            'is_approved' => true,
            'approved_at' => now(),
        ]);
        if (! $superadmin->hasRole('superadmin', 'backend')) {
            $superadmin->assignRole($superadminBackendRole);
        }

        $adminEmails = [
            'admin@gmail.com' => 'Admin User',
            'admin@ecommerce.test' => 'Admin Hasan',
        ];

        foreach ($adminEmails as $email => $name) {
            $adminUser = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_approved' => true,
                    'approved_at' => now(),
                ]
            );
            $adminUser->update([
                'password' => Hash::make('password'),
                'is_approved' => true,
                'approved_at' => now(),
            ]);
            if (! $adminUser->hasRole('admin', 'backend')) {
                $adminUser->assignRole($adminBackendRole);
            }
        }

        // Seed Customers into separate frontend_users table
        $simpleCustomer = FrontendUser::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'John Gamer',
                'password' => Hash::make('password'),
                'phone' => '+1 (555) 234-5678',
                'city' => 'San Jose',
                'customer_type' => 'simple',
                'orders_count' => 1,
                'total_spent' => 299.99,
                'email_verified_at' => now(),
            ]
        );

        $specialCustomer = FrontendUser::firstOrCreate(
            ['email' => 'alex.vip@customer.test'],
            [
                'name' => 'Alex Overclocker (VIP)',
                'password' => Hash::make('password'),
                'phone' => '+1 (555) 987-6543',
                'city' => 'Austin',
                'customer_type' => 'special',
                'orders_count' => 5,
                'total_spent' => 4850.00,
                'email_verified_at' => now(),
            ]
        );

        // Seed Hardware Suppliers
        Supplier::firstOrCreate(
            ['company_name' => 'ASUS Wholesale Direct'],
            [
                'contact_name' => 'Sarah Jenkins',
                'email' => 'distribution@asus-wholesale.test',
                'phone' => '+1 (800) 283-7872',
                'address' => '48720 Kato Rd, Fremont, CA 94538',
                'supply_categories' => 'GPUs, Motherboards, Monitors',
                'status' => 'active',
                'notes' => 'Net-30 billing agreement, Tier-1 graphics card allocations.',
            ]
        );

        Supplier::firstOrCreate(
            ['company_name' => 'Corsair Memory & Components'],
            [
                'contact_name' => 'David Miller',
                'email' => 'procurement@corsair-supply.test',
                'phone' => '+1 (888) 222-4346',
                'address' => '115 North McCarthy Blvd, Milpitas, CA 95035',
                'supply_categories' => 'DDR5 RAM, Power Supplies, Liquid Coolers',
                'status' => 'active',
                'notes' => 'Bulk pallet delivery every Tuesday.',
            ]
        );

        Supplier::firstOrCreate(
            ['company_name' => 'AMD Global Channel Distribution'],
            [
                'contact_name' => 'Michael Chang',
                'email' => 'partnerships@amd-channel.test',
                'phone' => '+1 (408) 749-4000',
                'address' => '2485 Augustine Dr, Santa Clara, CA 95054',
                'supply_categories' => 'Ryzen CPUs, Radeon GPUs, EPYC Servers',
                'status' => 'active',
                'notes' => 'Official distributor representative.',
            ]
        );
    }
}
