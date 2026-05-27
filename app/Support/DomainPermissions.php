<?php

declare(strict_types=1);

namespace App\Support;

readonly class DomainPermissions
{
    public static function unitsView(): array
    {
        return ['units.view'];
    }

    public static function unitsCreate(): array
    {
        return ['units.create'];
    }

    public static function unitsUpdate(): array
    {
        return ['units.update', 'units.edit'];
    }

    public static function unitsDelete(): array
    {
        return ['units.delete'];
    }

    public static function formsView(): array
    {
        return ['forms.view'];
    }

    public static function formsCreate(): array
    {
        return ['forms.create'];
    }

    public static function formsUpdate(): array
    {
        return ['forms.update', 'forms.edit'];
    }

    public static function formsDelete(): array
    {
        return ['forms.delete'];
    }

    public static function formsSubmissionsView(): array
    {
        return ['forms.submissions.view'];
    }

    public static function leadsView(): array
    {
        return ['leads.view'];
    }

    public static function leadsViewOwn(): array
    {
        return ['leads.view-own'];
    }

    public static function leadsCreate(): array
    {
        return ['leads.create'];
    }

    public static function leadsUpdate(): array
    {
        return ['leads.update', 'leads.edit'];
    }

    public static function leadsUpdateOwn(): array
    {
        return ['leads.update-own', 'leads.edit-own'];
    }

    public static function leadsDelete(): array
    {
        return ['leads.delete'];
    }

    public static function leadsTransfer(): array
    {
        return ['leads.transfer'];
    }

    public static function leadsDistribute(): array
    {
        return ['leads.distribute'];
    }

    public static function reportsExport(): array
    {
        return ['reports.export'];
    }

    public static function billingManage(): array
    {
        return ['billing.manage'];
    }
}
