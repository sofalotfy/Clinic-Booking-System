<?php

namespace Tests\Feature;

use App\Enums\AdminPermissionsEnum;
use App\Enums\AssistantPermissionsEnum;
use App\Enums\PermissionsTypeEnum;
use App\Enums\UserType;
use App\Models\Assistant;
use App\Models\Cost;
use App\Models\CostCategory;
use App\Models\Doctor;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\CostCategorySeeder;
use Database\Seeders\PermissionTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CostTest extends TestCase
{
    use RefreshDatabase;

    private Doctor $doctor;

    private CostCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionTableSeeder::class);
        $this->seed(CostCategorySeeder::class);

        $this->doctor = $this->makeDoctor();
        $this->category = CostCategory::where('name', 'Rent & Utilities')->firstOrFail();
    }

    public function test_seeder_creates_the_categories_once(): void
    {
        $this->seed(CostCategorySeeder::class);

        $this->assertSame(7, CostCategory::count());
        $this->assertStringContainsString('إيجار العيادة', $this->category->description);
    }

    public function test_doctor_runs_the_costs_crud(): void
    {
        $user = $this->doctor->user;

        $id = $this->actingAs($user, 'sanctum')
            ->postJson('/api/costs', [
                'name' => 'October rent',
                'cost_category_id' => $this->category->id,
                'amount' => 5000.5,
                'date' => '2026-10-01',
                'notes' => 'Paid in cash',
            ])
            ->assertCreated()
            ->assertJsonPath('cost.category', 'Rent & Utilities')
            ->assertJsonPath('cost.amount', '5000.50')
            ->json('cost.id');

        $this->assertSame($this->doctor->id, Cost::findOrFail($id)->doctor_id);

        $this->actingAs($user, 'sanctum')->getJson("/api/costs/{$id}")->assertOk()->assertJsonPath('cost.name', 'October rent');

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/costs/{$id}", ['amount' => 6000])
            ->assertOk()
            ->assertJsonPath('cost.amount', '6000.00')
            ->assertJsonPath('cost.name', 'October rent');

        $this->actingAs($user, 'sanctum')->deleteJson("/api/costs/{$id}")->assertOk();
        $this->assertSame(0, Cost::count());
    }

    public function test_costs_validate_their_input(): void
    {
        $this->actingAs($this->doctor->user, 'sanctum')
            ->postJson('/api/costs', ['amount' => -1, 'cost_category_id' => 9999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'cost_category_id', 'amount', 'date']);
    }

    public function test_listing_is_scoped_to_the_clinic_and_filterable(): void
    {
        $other = $this->makeDoctor();
        $this->makeCost($other, 'Other clinic');
        $this->makeCost($this->doctor, 'Rent', '2026-10-01');
        $this->makeCost($this->doctor, 'Gloves', '2026-09-01', CostCategory::where('name', 'Medical Supplies')->first());

        $this->actingAs($this->doctor->user, 'sanctum')->getJson('/api/costs')
            ->assertOk()->assertJsonCount(2, 'costs.data');

        $this->actingAs($this->doctor->user, 'sanctum')->getJson('/api/costs?from=2026-10-01')
            ->assertJsonCount(1, 'costs.data')->assertJsonPath('costs.data.0.name', 'Rent');

        $this->actingAs($this->doctor->user, 'sanctum')->getJson('/api/costs?cost_category_id='.$this->category->id)
            ->assertJsonCount(1, 'costs.data');
    }

    public function test_a_clinic_cannot_touch_another_clinics_cost(): void
    {
        $cost = $this->makeCost($this->makeDoctor(), 'Not yours');

        $this->actingAs($this->doctor->user, 'sanctum')->getJson("/api/costs/{$cost->id}")->assertForbidden();
        $this->actingAs($this->doctor->user, 'sanctum')->putJson("/api/costs/{$cost->id}", ['amount' => 1])->assertForbidden();
        $this->actingAs($this->doctor->user, 'sanctum')->deleteJson("/api/costs/{$cost->id}")->assertForbidden();
    }

    public function test_categories_listing(): void
    {
        $this->actingAs($this->doctor->user, 'sanctum')->getJson('/api/cost-categories')
            ->assertOk()->assertJsonCount(7, 'categories');
    }

    public function test_assistants_need_the_cost_permissions(): void
    {
        $assistant = $this->makeUser(UserType::ASSISTANT);
        Assistant::create(['doctor_id' => $this->doctor->id, 'user_id' => $assistant->id]);

        $this->actingAs($assistant, 'sanctum')->getJson('/api/costs')->assertForbidden();
        $this->actingAs($assistant, 'sanctum')->getJson('/api/cost-categories')->assertForbidden();

        $role = Role::create(['name' => 'Accountant', 'guard_name' => 'web', 'doctor_id' => $this->doctor->id]);
        foreach ([AssistantPermissionsEnum::VIEW_ALL_COSTS, AssistantPermissionsEnum::VIEW_ALL_COST_CATEGORIES] as $case) {
            $role->givePermissionTo(Permission::where('name', $case->value)->where('type', PermissionsTypeEnum::ASSISTANT)->firstOrFail());
        }
        $assistant->assignRole($role);

        $this->actingAs($assistant->fresh(), 'sanctum')->getJson('/api/costs')->assertOk();
        $this->actingAs($assistant->fresh(), 'sanctum')->getJson('/api/cost-categories')->assertOk();
        $this->actingAs($assistant->fresh(), 'sanctum')->postJson('/api/costs', [])->assertForbidden();
    }

    public function test_admin_manages_categories(): void
    {
        $admin = $this->makeAdmin(array_map(fn ($case) => $case->name, [
            AdminPermissionsEnum::VIEW_ALL_COST_CATEGORIES,
            AdminPermissionsEnum::VIEW_SINGLE_COST_CATEGORY,
            AdminPermissionsEnum::CREATE_COST_CATEGORY,
            AdminPermissionsEnum::UPDATE_COST_CATEGORY,
            AdminPermissionsEnum::DELETE_COST_CATEGORY,
        ]));

        $this->actingAs($admin)->get(route('web-admin.cost-categories.index'))->assertOk()->assertSee('Rent &amp; Utilities', false);

        $this->actingAs($admin)
            ->post(route('web-admin.cost-categories.store'), ['name' => 'Insurance', 'description' => 'تأمين'])
            ->assertRedirect(route('web-admin.cost-categories.index'));
        $category = CostCategory::where('name', 'Insurance')->firstOrFail();

        $this->actingAs($admin)->get(route('web-admin.cost-categories.show', $category))->assertOk()->assertSee('تأمين');
        $this->actingAs($admin)->get(route('web-admin.cost-categories.edit', $category))->assertOk();

        $this->actingAs($admin)
            ->put(route('web-admin.cost-categories.update', $category), ['name' => 'Insurance & Legal'])
            ->assertRedirect(route('web-admin.cost-categories.show', $category));
        $this->assertSame('Insurance & Legal', $category->fresh()->name);

        $this->actingAs($admin)
            ->post(route('web-admin.cost-categories.store'), ['name' => 'Insurance & Legal'])
            ->assertSessionHasErrors('name');

        $this->actingAs($admin)->delete(route('web-admin.cost-categories.destroy', $category))
            ->assertRedirect(route('web-admin.cost-categories.index'));
        $this->assertNull(CostCategory::find($category->id));
    }

    public function test_a_category_with_costs_cannot_be_deleted(): void
    {
        $admin = $this->makeAdmin(['DELETE_COST_CATEGORY']);
        $this->makeCost($this->doctor, 'Rent');

        $this->actingAs($admin)->delete(route('web-admin.cost-categories.destroy', $this->category))
            ->assertSessionHasErrors('category');
        $this->assertNotNull($this->category->fresh());
    }

    public function test_admin_without_permission_is_forbidden(): void
    {
        $admin = $this->makeAdmin([]);

        $this->actingAs($admin)->get(route('web-admin.cost-categories.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('web-admin.cost-categories.store'), ['name' => 'X'])->assertForbidden();
    }

    private function makeCost(Doctor $doctor, string $name, string $date = '2026-10-01', ?CostCategory $category = null): Cost
    {
        return Cost::create([
            'doctor_id' => $doctor->id,
            'name' => $name,
            'cost_category_id' => ($category ?? $this->category)->id,
            'amount' => 100,
            'date' => $date,
        ]);
    }

    private function makeUser(UserType $type): User
    {
        return User::create([
            'name' => 'User '.uniqid(),
            'email' => uniqid('user').'@example.com',
            'phone' => '1'.random_int(100000000, 999999999),
            'password' => bcrypt('password'),
            'type' => $type,
        ]);
    }

    private function makeDoctor(): Doctor
    {
        return Doctor::create(['user_id' => $this->makeUser(UserType::DOCTOR)->id])->load('user');
    }

    private function makeAdmin(array $permissions): User
    {
        $role = Role::create(['name' => 'Role '.uniqid(), 'guard_name' => 'web', 'doctor_id' => null]);

        foreach ($permissions as $case) {
            $role->givePermissionTo(
                Permission::where('name', constant(AdminPermissionsEnum::class.'::'.$case)->value)
                    ->where('type', PermissionsTypeEnum::ADMIN)
                    ->firstOrFail()
            );
        }

        $admin = $this->makeUser(UserType::ADMIN);
        $admin->assignRole($role);

        return $admin;
    }
}
