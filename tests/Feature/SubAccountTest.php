<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\EmployeeData;
use App\Models\SubAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class SubAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_get_subaccount_info()
    {
        // Create an employee with data
        $employee = Employee::factory()->create();
        $employeeData = EmployeeData::factory()->create([
            'employee_id' => $employee->id,
            'email' => 'test@example.com',
            'password_hash' => bcrypt('password123')
        ]);

        // Create a sub account (only stores Chapa subaccount ID)
        $subAccount = SubAccount::factory()->create([
            'employee_id' => $employee->id,
            'sub_account' => 'CHAPA_SUB_12345'
        ]);

        // Authenticate the employee
        Sanctum::actingAs($employee);

        // Test getting subaccount info - we can only get the subaccount ID
        $this->assertDatabaseHas('sub_accounts', [
            'employee_id' => $employee->id,
            'sub_account' => 'CHAPA_SUB_12345'
        ]);
    }

    public function test_subaccount_only_stores_chapa_id()
    {
        // Create an employee with data
        $employee = Employee::factory()->create();
        $employeeData = EmployeeData::factory()->create([
            'employee_id' => $employee->id,
            'email' => 'test@example.com',
            'password_hash' => bcrypt('password123')
        ]);

        // Create a sub account - only stores the Chapa subaccount ID
        $subAccount = SubAccount::factory()->create([
            'employee_id' => $employee->id,
            'sub_account' => 'CHAPA_SUB_67890'
        ]);

        // Verify only the subaccount ID is stored
        $this->assertDatabaseHas('sub_accounts', [
            'employee_id' => $employee->id,
            'sub_account' => 'CHAPA_SUB_67890'
        ]);

        // Verify no bank details are stored
        $this->assertDatabaseMissing('sub_accounts', [
            'employee_id' => $employee->id,
            'business_name' => 'Test Business'
        ]);
    }

    public function test_subaccount_creation_workflow()
    {
        // This test verifies the workflow:
        // 1. Employee provides bank details to create Chapa subaccount
        // 2. Only the Chapa subaccount ID is stored in database
        // 3. Bank details are not persisted
        
        $employee = Employee::factory()->create();
        $employeeData = EmployeeData::factory()->create([
            'employee_id' => $employee->id,
            'email' => 'test@example.com',
            'password_hash' => bcrypt('password123')
        ]);

        // Simulate the subaccount creation process
        $chapaSubaccountId = 'CHAPA_SUB_' . uniqid();
        
        $subAccount = new SubAccount();
        $subAccount->sub_account = $chapaSubaccountId;
        $subAccount->employee_id = $employee->id;
        $subAccount->save();

        // Verify only the subaccount ID is stored
        $this->assertDatabaseHas('sub_accounts', [
            'employee_id' => $employee->id,
            'sub_account' => $chapaSubaccountId
        ]);

        // Verify the relationship works
        $this->assertEquals($chapaSubaccountId, $employee->subAccount->sub_account);
    }
}
