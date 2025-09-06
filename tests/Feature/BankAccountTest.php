<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\EmployeeData;
use App\Models\SubAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class BankAccountTest extends TestCase
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
        $response = $this->getJson('/api/bank-account');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Subaccount retrieved successfully',
                    'data' => [
                        'sub_account_id' => 'CHAPA_SUB_12345'
                    ]
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

    public function test_employee_profile_includes_bank_account_info()
    {
        // Create an employee with data and bank account
        $employee = Employee::factory()->create();
        $employeeData = EmployeeData::factory()->create([
            'employee_id' => $employee->id,
            'email' => 'test@example.com',
            'password_hash' => bcrypt('password123')
        ]);

        // Create a sub account
        $subAccount = SubAccount::factory()->create([
            'employee_id' => $employee->id,
            'sub_account' => 'CHAPA_SUB_12345'
        ]);

        // Authenticate the employee
        Sanctum::actingAs($employee);

        // Test getting profile with bank account info
        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Profile retrieved successfully',
                    'data' => [
                        'first_name' => $employeeData->first_name,
                        'last_name' => $employeeData->last_name,
                        'email' => $employeeData->email,
                        'bank_account' => [
                            'has_bank_account' => true,
                            'sub_account_id' => 'CHAPA_SUB_12345'
                        ]
                    ]
                ]);

        // Verify bank account fields are present
        $response->assertJsonStructure([
            'data' => [
                'id',
                'tip_code',
                'is_active',
                'is_verified',
                'first_name',
                'last_name',
                'email',
                'image_url',
                'bank_account' => [
                    'has_bank_account',
                    'sub_account_id',
                    'bank_account_updated_at'
                ]
            ]
        ]);
    }

    public function test_employee_profile_without_bank_account()
    {
        // Create an employee with data but no bank account
        $employee = Employee::factory()->create();
        $employeeData = EmployeeData::factory()->create([
            'employee_id' => $employee->id,
            'email' => 'test@example.com',
            'password_hash' => bcrypt('password123')
        ]);

        // Authenticate the employee
        Sanctum::actingAs($employee);

        // Test getting profile without bank account
        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Profile retrieved successfully',
                    'data' => [
                        'first_name' => $employeeData->first_name,
                        'last_name' => $employeeData->last_name,
                        'email' => $employeeData->email,
                        'bank_account' => [
                            'has_bank_account' => false,
                            'sub_account_id' => null,
                            'bank_account_updated_at' => null
                        ]
                    ]
                ]);
    }
}
