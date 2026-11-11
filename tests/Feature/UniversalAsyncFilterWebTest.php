<?php

use App\Models\PemrakarsaProfile;
use App\Models\PengelolaTps3rProfile;
use App\Models\User;

test('public portal pages expose universal async filtering and custom select controls', function () {
    $this->get(route('public.marketplace'))
        ->assertOk()
        ->assertSee('data-ajax-form', false)
        ->assertSee('data-ajax-zone="marketplace"', false)
        ->assertSee('data-select-filter', false)
        ->assertDontSee('onchange="this.form.submit()"', false)
        ->assertDontSee('marketplaceAjax', false);

    $this->get(route('public.services'))
        ->assertOk()
        ->assertSee('data-ajax-form', false)
        ->assertSee('data-ajax-zone="services-facilities"', false)
        ->assertSee('data-select-filter', false)
        ->assertSee('id="servicesMapPayload"', false)
        ->assertDontSee('onchange="this.form.submit()"', false);
});

test('admin dlh dashboard pages expose async filtering tabs pagination and custom dropdowns', function () {
    $admin = User::factory()->adminDlh()->create();
    $tps3rManager = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $tps3rManager->id]);

    $formAndZoneRoutes = [
        'dashboard.admin.users',
        'dashboard.admin.complaints',
        'dashboard.admin.reports',
        'dashboard.admin.points',
        'dashboard.admin.retributions',
        'dashboard.admin.articles',
        'dashboard.admin.documents',
        'dashboard.tps3r.payment-settings',
    ];

    foreach ($formAndZoneRoutes as $routeName) {
        $this->actingAs($admin)
            ->get(route($routeName))
            ->assertOk()
            ->assertSee('data-ajax-form', false)
            ->assertSee('data-ajax-zone=', false)
            ->assertDontSee('onchange="this.form.submit()"', false);
    }

    $selectFilterRoutes = [
        'dashboard.admin.complaints',
        'dashboard.admin.reports',
        'dashboard.admin.retributions',
        'dashboard.tps3r.payment-settings',
    ];

    foreach ($selectFilterRoutes as $routeName) {
        $this->actingAs($admin)
            ->get(route($routeName))
            ->assertOk()
            ->assertSee('data-select-filter', false);
    }

    $zoneOnlyRoutes = [
        'dashboard.admin.entities',
        'dashboard.admin.master-data',
        'dashboard.admin.activity-logs',
        'dashboard.admin.broadcasts',
        'dashboard.admin.routes',
    ];

    foreach ($zoneOnlyRoutes as $routeName) {
        $this->actingAs($admin)
            ->get(route($routeName))
            ->assertOk()
            ->assertSee('data-ajax-zone=', false)
            ->assertDontSee('onchange="this.form.submit()"', false);
    }

    $this->actingAs($admin)
        ->get(route('dashboard.admin.portal-settings'))
        ->assertOk()
        ->assertDontSee('onchange="this.form.submit()"', false);
});

test('tps3r and pemrakarsa dashboards expose async filtering pagination and custom dropdowns', function () {
    $manager = User::factory()->pengelolaTps3r()->create();
    PengelolaTps3rProfile::factory()->create(['user_id' => $manager->id]);

    $this->actingAs($manager)
        ->get(route('dashboard.tps3r.deposits'))
        ->assertOk()
        ->assertSee('data-ajax-form', false)
        ->assertSee('data-ajax-zone="tps3r-deposits"', false)
        ->assertDontSee('onchange="this.form.submit()"', false);

    $this->actingAs($manager)
        ->get(route('dashboard.tps3r.volume-recap'))
        ->assertOk()
        ->assertSee('data-ajax-form', false)
        ->assertSee('data-ajax-zone="tps3r-volume-recap"', false)
        ->assertSee('data-select-filter', false)
        ->assertDontSee('onchange="this.form.submit()"', false);

    foreach ([
        'dashboard.tps3r.orders' => 'tps3r-orders',
        'dashboard.tps3r.products' => 'tps3r-products',
        'dashboard.tps3r.processing.index' => 'tps3r-processing',
        'dashboard.tps3r.withdrawals.index' => 'tps3r-withdrawals',
    ] as $routeName => $zoneName) {
        $this->actingAs($manager)
            ->get(route($routeName))
            ->assertOk()
            ->assertSee("data-ajax-zone=\"{$zoneName}\"", false)
            ->assertDontSee('onchange="this.form.submit()"', false);
    }

    $pemrakarsa = User::factory()->pemrakarsa()->create();
    PemrakarsaProfile::factory()->create(['user_id' => $pemrakarsa->id]);

    $this->actingAs($pemrakarsa)
        ->get(route('dashboard.pemrakarsa.reports'))
        ->assertOk()
        ->assertSee('data-ajax-form', false)
        ->assertSee('data-ajax-zone="pemrakarsa-reports-table"', false)
        ->assertSee('data-select-filter', false)
        ->assertDontSee('onchange="this.form.submit()"', false);
});
