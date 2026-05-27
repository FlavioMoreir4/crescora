<?php

declare(strict_types=1);

namespace App\Domains\Shared\Context;

use App\Models\Team;

final class TenantContext
{
    private static ?self $instance = null;

    private ?int $teamId = null;

    private bool $ignoreTenancy = false;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public static function setTeamId(?int $teamId): void
    {
        self::getInstance()->teamId = $teamId;
    }

    public static function getTeamId(): ?int
    {
        return self::getInstance()->teamId;
    }

    public static function setIgnoreTenancy(bool $ignore): void
    {
        self::getInstance()->ignoreTenancy = $ignore;
    }

    public static function isIgnoreTenancy(): bool
    {
        return self::getInstance()->ignoreTenancy;
    }

    public static function currentTeam(): ?Team
    {
        $teamId = self::getTeamId();

        if ($teamId === null) {
            return null;
        }

        return Team::find($teamId);
    }
}
