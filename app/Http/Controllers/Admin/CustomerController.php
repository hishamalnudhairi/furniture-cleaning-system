<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $customers = Customer::query()
            ->with(['orders' => fn ($q) => $q->latest()])
            ->when($search !== '', fn ($q) => $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', ['customers' => $customers, 'search' => $search]);
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create($request->validated());

        $this->log('customer.created', $customer, __('Created customer :name', ['name' => $customer->name]));

        return redirect()->route('admin.customers.show', $customer)->with('success', __('Customer created successfully.'));
    }

    public function show(Customer $customer): View
    {
        $customer->load(['orders' => fn ($q) => $q->latest()]);

        return view('admin.customers.show', [
            'customer' => $customer,
            'totalDue' => $customer->totalDue(),
            'totalPaid' => $customer->totalPaid(),
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', ['customer' => $customer]);
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        $this->log('customer.updated', $customer, __('Updated customer :name', ['name' => $customer->name]));

        return redirect()->route('admin.customers.show', $customer)->with('success', __('Customer updated successfully.'));
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
