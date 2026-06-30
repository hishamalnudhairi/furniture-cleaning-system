<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceFormRequest;
use App\Models\ActivityLog;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::orderBy('sort_order')->orderBy('name_ar')->paginate(20);

        return view('admin.services.index', ['services' => $services]);
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(ServiceFormRequest $request): RedirectResponse
    {
        $service = Service::create($this->mapData($request->validated()));

        $this->log('service.created', $service, __('Created service :name', ['name' => $service->name_ar]));

        return redirect()->route('admin.services.index')->with('success', __('Service created successfully.'));
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', ['service' => $service]);
    }

    public function update(ServiceFormRequest $request, Service $service): RedirectResponse
    {
        $service->update($this->mapData($request->validated()));

        $this->log('service.updated', $service, __('Updated service :name', ['name' => $service->name_ar]));

        return redirect()->route('admin.services.index')->with('success', __('Service updated successfully.'));
    }

    /**
     * يحوّل بيانات النموذج (base_price → default_price) ويعالج الحقول المنطقية.
     */
    private function mapData(array $data): array
    {
        return [
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? null,
            'unit' => $data['unit'] ?? null,
            'default_price' => $data['base_price'] ?? null,
            'is_price_editable' => request()->boolean('is_price_editable'),
            'is_active' => request()->boolean('is_active'),
            'notes' => $data['notes'] ?? null,
        ];
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
