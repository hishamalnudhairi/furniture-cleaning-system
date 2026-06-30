<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInventoryTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'worker'): User
    {
        return User::create([
            'name' => 'U '.$role, 'email' => $role.'@example.com', 'password' => 'password',
            'role' => $role, 'is_active' => true,
        ]);
    }

    private function makeItem(float $qty = 10, float $min = 5, bool $active = true): InventoryItem
    {
        return InventoryItem::create([
            'name' => 'شامبو', 'unit' => 'liter',
            'quantity' => $qty, 'min_quantity' => $min, 'cost_price' => 3, 'is_active' => $active,
        ]);
    }

    public function test_index_lists_items(): void
    {
        $item = $this->makeItem();
        $this->actingAs($this->user())->get(route('admin.inventory.index'))->assertOk()->assertSee('شامبو');
    }

    public function test_create_item(): void
    {
        $this->actingAs($this->user())->post(route('admin.inventory.store'), [
            'name' => 'منظف', 'unit' => 'bottle', 'current_quantity' => 12,
            'alert_quantity' => 4, 'purchase_price' => 2, 'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('inventory_items', ['name' => 'منظف', 'unit' => 'bottle', 'quantity' => 12.00, 'min_quantity' => 4.00]);
    }

    public function test_update_item_does_not_change_quantity(): void
    {
        $item = $this->makeItem(qty: 10);

        $this->actingAs($this->user())->put(route('admin.inventory.update', $item), [
            'name' => 'شامبو محدث', 'unit' => 'liter', 'alert_quantity' => 8, 'status' => 'inactive',
            'current_quantity' => 999, // يجب تجاهلها
        ])->assertRedirect();

        $item->refresh();
        $this->assertSame('شامبو محدث', $item->name);
        $this->assertEquals(10.0, (float) $item->quantity); // لم تتغير
        $this->assertEquals(8.0, (float) $item->min_quantity);
        $this->assertFalse($item->is_active);
    }

    public function test_show_item(): void
    {
        $item = $this->makeItem();
        $this->actingAs($this->user())->get(route('admin.inventory.show', $item))->assertOk()->assertSee('شامبو');
    }

    public function test_add_quantity(): void
    {
        $item = $this->makeItem(qty: 10);

        $this->actingAs($this->user())->post(route('admin.inventory.add', $item), ['quantity' => 5])->assertRedirect();

        $this->assertEquals(15.0, (float) $item->fresh()->quantity);
        $this->assertDatabaseHas('inventory_movements', ['inventory_item_id' => $item->id, 'type' => 'in', 'quantity' => 5.00, 'balance_after' => 15.00]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'inventory.added']);
    }

    public function test_dispense_quantity(): void
    {
        $item = $this->makeItem(qty: 10);

        $this->actingAs($this->user())->post(route('admin.inventory.dispense', $item), ['quantity' => 4])->assertRedirect();

        $this->assertEquals(6.0, (float) $item->fresh()->quantity);
        $this->assertDatabaseHas('inventory_movements', ['inventory_item_id' => $item->id, 'type' => 'out', 'quantity' => 4.00, 'balance_after' => 6.00]);
    }

    public function test_cannot_dispense_more_than_available(): void
    {
        $item = $this->makeItem(qty: 3);

        $this->actingAs($this->user())->post(route('admin.inventory.dispense', $item), ['quantity' => 10])
            ->assertSessionHasErrors('quantity');

        $this->assertEquals(3.0, (float) $item->fresh()->quantity);
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_stock_state_calculation(): void
    {
        $this->assertSame('out', $this->makeItem(qty: 0, min: 5)->stockState());
        $this->assertSame('low', $this->makeItem(qty: 2, min: 5)->stockState());
        $this->assertSame('ok', $this->makeItem(qty: 10, min: 5)->stockState());
    }

    public function test_filter_low_items(): void
    {
        $low = InventoryItem::create(['name' => 'مادة ناقصة', 'unit' => 'liter', 'quantity' => 2, 'min_quantity' => 5, 'is_active' => true]);
        $ok = InventoryItem::create(['name' => 'مادة كافية', 'unit' => 'liter', 'quantity' => 50, 'min_quantity' => 5, 'is_active' => true]);

        $this->actingAs($this->user())->get(route('admin.inventory.index', ['filter' => 'low']))
            ->assertOk()
            ->assertSee('مادة ناقصة')
            ->assertDontSee('مادة كافية');
    }

    public function test_manual_adjustment_admin_only(): void
    {
        $item = $this->makeItem(qty: 10);

        // الموظف ممنوع
        $this->actingAs($this->user('worker'))
            ->post(route('admin.inventory.adjust', $item), ['quantity' => 50])
            ->assertForbidden();

        // المدير مسموح
        $this->actingAs($this->user('admin'))
            ->post(route('admin.inventory.adjust', $item), ['quantity' => 50])
            ->assertRedirect();

        $this->assertEquals(50.0, (float) $item->fresh()->quantity);
        $this->assertDatabaseHas('inventory_movements', ['inventory_item_id' => $item->id, 'type' => 'adjustment', 'balance_after' => 50.00]);
    }

    public function test_guest_cannot_access(): void
    {
        $item = $this->makeItem();
        $this->get(route('admin.inventory.index'))->assertRedirect('/login');
        $this->get(route('admin.inventory.show', $item))->assertRedirect('/login');
    }

    public function test_dashboard_links_to_inventory(): void
    {
        $this->actingAs($this->user())->get(route('dashboard'))->assertOk()
            ->assertSee(route('admin.inventory.index'));
    }
}
