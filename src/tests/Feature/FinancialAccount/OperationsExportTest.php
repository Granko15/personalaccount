<?php

namespace Tests\Feature\FinancialAccount;

use App\Http\Controllers\FinancialAccounts\AccountDetailController;
use App\Models\Account;
use App\Models\FinancialOperation;
use App\Models\OperationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;


/**
 * These tests must be run on a seeded database, as they generate plenty of models with foreign keys.
 */
class OperationsExportTest extends TestCase
{
    use DatabaseTransactions;

    private int $perPage, $extraRows;
    private array $dates;
    private Model $user, $account, $icomeType, $expenseType, $lendingType;

    public function setUp(): void
    {
        parent::setUp();

        $this->perPage = AccountDetailController::$perPage;
        $this->extraRows = 2; // header + extra '\n' symbol in a csv file
        $this->dates = ['2000-01-01', '2001-01-01', '2002-01-01', '2003-01-01', '2004-01-01','2005-01-01'];
        $this->user = User::create([ 'email' => 'new@b.c' ]);
        $this->account = Account::factory()->create(['user_id' => $this->user]);
        $this->incomeType = OperationType::factory()->create(['name' => 'income', 'expense' => false]);
        $this->expenseType = OperationType::factory()->create(['name' => 'expense', 'expense' => true]);
        $this->lendingType = OperationType::factory()->create(['name' => 'Lending', 'expense' => false]);

    }

    public function test_export_single_operation()
    {
        $operation = FinancialOperation::factory()->create([
            'account_id' => $this->account,
            'title' => 'title',
            'date' => $this->dates[0],
            'operation_type_id' => $this->incomeType,
            'subject' => 'subject',
            'sum' => 100,
            'attachment' => 'attachments/test',
            'checked' => false,
            'sap_id' => null
        ]);

        $response = $this->actingAs($this->user)->get(sprintf('/export/%d', $this->account->id));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+1,$rows);

        $expected = sprintf('%d;%s;title;2000-01-01;income;subject;100.00;attachments/test;FALSE;',
            $operation->id,
            $this->account->sap_id);

        $this->assertEquals($expected,$rows[1]);
    }

    public function test_export_single_operation_expense()
    {
        $operation = FinancialOperation::factory()->create([
            'account_id' => $this->account,
            'title' => 'title',
            'date' => $this->dates[0],
            'operation_type_id' => $this->expenseType,
            'subject' => 'subject',
            'sum' => 100,
            'attachment' => 'attachments/test',
            'checked' => false,
            'sap_id' => null
        ]);

        $response = $this->actingAs($this->user)->get(sprintf('/export/%d', $this->account->id));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+1,$rows);

        $expected = sprintf('%d;%s;title;2000-01-01;expense;subject;100.00-;attachments/test;FALSE;',
            $operation->id,
            $this->account->sap_id);

        $this->assertEquals($expected,$rows[1]);
    }

    public function test_export_single_operation_checked()
    {
        $operation = FinancialOperation::factory()->create([
            'account_id' => $this->account,
            'title' => 'title',
            'date' => $this->dates[0],
            'operation_type_id' => $this->incomeType,
            'subject' => 'subject',
            'sum' => 100,
            'attachment' => 'attachments/test',
            'checked' => true,
            'sap_id' => '99'
        ]);

        $response = $this->actingAs($this->user)->get(sprintf('/export/%d', $this->account->id));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+1,$rows);

        $expected = sprintf('%d;%s;title;2000-01-01;income;subject;100.00;attachments/test;TRUE;99',
            $operation->id,
            $this->account->sap_id);

        $this->assertEquals($expected,$rows[1]);
    }

    public function test_export_single_operation_lending()
    {
        $operation = FinancialOperation::factory()->create([
            'account_id' => $this->account,
            'title' => 'title',
            'date' => $this->dates[0],
            'operation_type_id' => $this->lendingType,
            'subject' => 'subject',
            'sum' => 100,
            'attachment' => 'attachments/test',
            'checked' => false,
            'sap_id' => null
        ]);

        $response = $this->actingAs($this->user)->get(sprintf('/export/%d', $this->account->id));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+1,$rows);

        $expected = sprintf('%d;%s;title;2000-01-01;Lending;subject;100.00;attachments/test;;',
            $operation->id,
            $this->account->sap_id);

        $this->assertEquals($expected,$rows[1]);
    }

    public function test_export_with_all_data()
    {
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[1]]);

        $response = $this->actingAs($this->user)->get(sprintf('/export/%d', $this->account->id));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+2,$rows);
    }

    public function test_filtered_export_with_some_data()
    {
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[2]]);
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[3]]);

        $response = $this->actingAs($this->user)
            ->get(sprintf('/export/%d?from=%s&to=%s', $this->account->id,
                $this->dates[1],
                $this->dates[2]
            ));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+2,$rows);
    }

    public function test_filtered_export_with_no_data()
    {
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[1]]);

        $response = $this->actingAs($this->user)
            ->get(sprintf('/export/%d?from=%s&to=%s', $this->account->id,
                $this->dates[2],
                $this->dates[3]
            ));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+0,$rows);
    }

    public function test_export_data_unbound_on_both_sides()
    {
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[2]]);

        $response = $this->actingAs($this->user)
            ->get(sprintf('/export/%d?from=%s&to=%s', $this->account->id,
                '',
                ''
            ));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+3,$rows);
    }

    public function test_export_data_unbound_from_right()
    {
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[2]]);

        $response = $this->actingAs($this->user)
            ->get(sprintf('/export/%d?from=%s&to=%s', $this->account->id,
                $this->dates[1],
                ''
            ));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+2,$rows);
    }

    public function test_export_data_unbound_from_left()
    {
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[1]]);
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[2]]);

        $response = $this->actingAs($this->user)
            ->get(sprintf('/export/%d?from=%s&to=%s', $this->account->id,
                '',
                $this->dates[1]
            ));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+2,$rows);
    }

    public function test_filtering_export_invalid_interval()
    {
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[0]]);
        FinancialOperation::factory()->create(['account_id' => $this->account, 'date' => $this->dates[1]]);

        $response = $this->actingAs($this->user)
            ->get(sprintf('/export/%d?from=%s&to=%s', $this->account->id,
                $this->dates[1],
                $this->dates[0]
            ));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $rows = explode("\n", $content);

        $this->assertCount($this->extraRows+0,$rows);
    }

    public function test_filtering_export_invalid_input()
    {

        $response = $this->actingAs($this->user)
            ->get(sprintf('/export/%d?from=%s&to=%s', $this->account->id,
                $this->dates[1],
                'invalid'
            ));

        $response->assertStatus(500);
    }

}
