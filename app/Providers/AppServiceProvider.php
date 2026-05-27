<?php

namespace App\Providers;

use App\Domains\Billing\Contracts\BillingGatewayInterface;
use App\Domains\Billing\Gateways\AsaasGateway;
use App\Domains\Forms\Models\Form;
use App\Domains\Forms\Policies\FormPolicy;
use App\Domains\Leads\Events\LeadCreated;
use App\Domains\Leads\Events\LeadStatusChanged;
use App\Domains\Leads\Listeners\DispatchLeadDistribution;
use App\Domains\Leads\Listeners\SendLeadAssignedNotification;
use App\Domains\Leads\Listeners\SendLeadStatusChangedNotification;
use App\Domains\Leads\Models\Lead;
use App\Domains\Leads\Observers\LeadObserver;
use App\Domains\Leads\Policies\LeadPolicy;
use App\Domains\Units\Models\Unit;
use App\Domains\Units\Policies\UnitPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BillingGatewayInterface::class, function () {
            return new AsaasGateway(
                sandbox: config('services.asaas.sandbox', true),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerPolicies();
        $this->registerObservers();
        $this->registerEvents();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(Form::class, FormPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Export::class, ExportPolicy::class);
    }

    protected function registerObservers(): void
    {
        Lead::observe(LeadObserver::class);
    }

    protected function registerEvents(): void
    {
        Event::listen(
            LeadCreated::class,
            DispatchLeadDistribution::class,
        );

        Event::listen(
            LeadCreated::class,
            SendLeadAssignedNotification::class,
        );

        Event::listen(
            LeadStatusChanged::class,
            SendLeadStatusChangedNotification::class,
        );
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
