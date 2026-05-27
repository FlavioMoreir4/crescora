<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    private const array ROLES = [
        'owner' => 'Proprietário — acesso total à plataforma',
        'admin' => 'Administrador — gestão de usuários, unidades e configurações',
        'gestor' => 'Gestor — acompanhamento de leads, relatórios e métricas',
        'vendedor' => 'Vendedor — cadastro e acompanhamento de leads próprios',
    ];

    private const array PERMISSIONS = [
        'users.view',
        'users.create',
        'users.edit',
        'users.delete',
        'users.manage-roles',
        'units.view',
        'units.create',
        'units.update',
        'units.edit',
        'units.delete',
        'leads.view',
        'leads.view-own',
        'leads.create',
        'leads.update',
        'leads.update-own',
        'leads.edit',
        'leads.edit-own',
        'leads.delete',
        'leads.transfer',
        'leads.distribute',
        'forms.view',
        'forms.create',
        'forms.update',
        'forms.edit',
        'forms.delete',
        'forms.submissions.view',
        'landing-pages.view',
        'landing-pages.create',
        'landing-pages.edit',
        'landing-pages.delete',
        'landing-pages.publish',
        'reports.view',
        'reports.export',
        'billing.view',
        'billing.invoices',
        'billing.subscriptions',
        'billing.configure',
        'billing.manage',
        'settings.view',
        'settings.edit',
        'settings.integrations',
        'notifications.manage',
        'notifications.templates',
    ];

    private const array ROLE_PERMISSIONS_MAP = [
        'owner' => '*',
        'admin' => [
            'users.view', 'users.create', 'users.edit',
            'units.*',
            'leads.*',
            'forms.*',
            'landing-pages.*',
            'reports.*',
            'billing.*',
            'settings.*',
            'notifications.*',
        ],
        'gestor' => [
            'users.view',
            'units.view',
            'leads.view', 'leads.create', 'leads.update', 'leads.transfer', 'leads.distribute',
            'forms.view', 'forms.submissions.view',
            'landing-pages.view', 'landing-pages.publish',
            'reports.view', 'reports.export',
            'billing.view', 'billing.invoices',
            'settings.view',
        ],
        'vendedor' => [
            'leads.view-own', 'leads.create', 'leads.update-own',
            'forms.view', 'forms.submissions.view',
            'landing-pages.view',
        ],
    ];

    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        foreach (self::PERMISSIONS as $name) {
            Permission::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        foreach (self::ROLES as $name => $label) {
            $role = Role::query()->firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);

            if ($name === 'owner') {
                $role->givePermissionTo(Permission::all());
            } elseif (isset(self::ROLE_PERMISSIONS_MAP[$name])) {
                foreach (self::ROLE_PERMISSIONS_MAP[$name] as $perm) {
                    if (str_ends_with($perm, '*')) {
                        $prefix = str_replace('*', '', $perm);
                        $matching = Permission::query()
                            ->where('name', 'like', "{$prefix}%")
                            ->get();
                        $role->givePermissionTo($matching);
                    } else {
                        $permission = Permission::query()->where('name', $perm)->first();
                        if ($permission) {
                            $role->givePermissionTo($permission);
                        }
                    }
                }
            }
        }
    }
}
