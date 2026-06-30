<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryItemRequest;
use App\Http\Requests\InventoryMovementRequest;
use App\Models\ActivityLog;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter');
        $search = trim((string) $request->query('q', ''));

        $query = InventoryItem::query()->latest();

        match ($filter) {
            'low' => $query->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'min_quantity'),
            'out' => $query->where('quantity', '<=', 0),
            'active' => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            default => null,
        };

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        return view('admin.inventory.index', [
            'items' => $query->paginate(15)->withQueryString(),
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.inventory.create');
    }

    public function store(InventoryItemRequest $request): RedirectResponse
    {
        $item = InventoryItem::create($this->mapData($request->validated()));

        $this->log('inventory.created', $item, __('Created item :name', ['name' => $item->name]));

        return redirect()->route('admin.inventory.show', $item)
            ->with('success', __('Item created successfully.'));
    }

    public function show(InventoryItem $inventoryItem): View
    {
        $inventoryItem->load(['movements' => fn ($q) => $q->latest()->limit(20)]);

        return view('admin.inventory.show', [
            'item' => $inventoryItem,
            'settings' => \App\Models\BusinessSetting::current(),
        ]);
    }

    public function edit(InventoryItem $inventoryItem): View
    {
        return view('admin.inventory.edit', ['item' => $inventoryItem]);
    }

    public function update(InventoryItemRequest $request, InventoryItem $inventoryItem): RedirectResponse
    {
        // التعديل لا يغيّر الكمية مباشرة (تتغير عبر إضافة/صرف/تعديل يدوي مسجّل كحركة)
        $inventoryItem->update($this->mapData($request->validated(), withQuantity: false));

        $this->log('inventory.updated', $inventoryItem, __('Updated item :name', ['name' => $inventoryItem->name]));

        return redirect()->route('admin.inventory.show', $inventoryItem)
            ->with('success', __('Item updated successfully.'));
    }

    /**
     * إضافة كمية (in).
     */
    public function addQuantity(InventoryMovementRequest $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $data = $request->validated();
        $qty = round((float) $data['quantity'], 2);

        DB::transaction(function () use ($inventoryItem, $qty, $data) {
            $balance = round((float) $inventoryItem->quantity + $qty, 2);
            $this->recordMovement($inventoryItem, InventoryMovement::TYPE_IN, $qty, $balance, $data);
            $inventoryItem->update(['quantity' => $balance]);
            $this->log('inventory.added', $inventoryItem,
                __('Added :qty to :name', ['qty' => $qty, 'name' => $inventoryItem->name]));
        });

        return back()->with('success', __('Quantity added successfully.'));
    }

    /**
     * صرف كمية (out).
     */
    public function dispenseQuantity(InventoryMovementRequest $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $data = $request->validated();
        $qty = round((float) $data['quantity'], 2);

        // لا يمكن صرف أكثر من المتوفر
        if ($qty > (float) $inventoryItem->quantity) {
            return back()->withInput()->withErrors([
                'quantity' => __('Cannot dispense more than the available quantity.'),
            ]);
        }

        DB::transaction(function () use ($inventoryItem, $qty, $data) {
            $balance = round((float) $inventoryItem->quantity - $qty, 2);
            $this->recordMovement($inventoryItem, InventoryMovement::TYPE_OUT, $qty, $balance, $data);
            $inventoryItem->update(['quantity' => $balance]);
            $this->log('inventory.dispensed', $inventoryItem,
                __('Dispensed :qty from :name', ['qty' => $qty, 'name' => $inventoryItem->name]));
        });

        return back()->with('success', __('Quantity dispensed successfully.'));
    }

    /**
     * تعديل يدوي للكمية (adjustment) — للمدير فقط (محمي بـ middleware).
     */
    public function adjust(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        if (! (\App\Models\BusinessSetting::current()->allow_manual_inventory_adjustment ?? true)) {
            return back()->with('error', __('Manual adjustment is disabled in settings.'));
        }

        $data = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0'],
            'movement_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $newQty = round((float) $data['quantity'], 2);

        DB::transaction(function () use ($inventoryItem, $newQty, $data) {
            $this->recordMovement($inventoryItem, InventoryMovement::TYPE_ADJUSTMENT, $newQty, $newQty, $data);
            $inventoryItem->update(['quantity' => $newQty]);
            $this->log('inventory.adjusted', $inventoryItem,
                __('Adjusted :name to :qty', ['name' => $inventoryItem->name, 'qty' => $newQty]));
        });

        return back()->with('success', __('Quantity adjusted successfully.'));
    }

    // ---------------------------------------------------------------------

    private function recordMovement(InventoryItem $item, string $type, float $qty, float $balance, array $data): void
    {
        InventoryMovement::create([
            'inventory_item_id' => $item->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'quantity' => $qty,
            'balance_after' => $balance,
            'movement_date' => $data['movement_date'] ?? now()->toDateString(),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function mapData(array $data, bool $withQuantity = true): array
    {
        $mapped = [
            'name' => $data['name'],
            'unit' => $data['unit'],
            'min_quantity' => $data['alert_quantity'] ?? 0,
            'cost_price' => $data['purchase_price'] ?? null,
            'is_active' => ($data['status'] ?? 'active') === 'active',
            'notes' => $data['notes'] ?? null,
        ];

        if ($withQuantity) {
            $mapped['quantity'] = $data['current_quantity'] ?? 0;
        }

        return $mapped;
    }

    private function log(string $action, object $subject, string $description): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
