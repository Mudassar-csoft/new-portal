<?php

namespace Tests\Feature\Finance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayeeQuickStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_payee_can_be_created_via_json_endpoint(): void
    {
        $this->withoutMiddleware();

        $response = $this->postJson(route('finance.payees.quickStore'), [
            'full_name' => 'Quick Payee',
            'type' => 'payee',
        ]);

        $response->assertSuccessful();
        $response->assertJsonPath('payee.full_name', 'Quick Payee');
        $response->assertJsonPath('payee.type', 'payee');
        $this->assertDatabaseHas('finance_payees', [
            'full_name' => 'Quick Payee',
            'type' => 'payee',
            'status' => 'active',
        ]);
    }
}
