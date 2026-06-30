<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConvertServiceRequestRequest;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
    /**
     * قائمة طلبات العملاء مع الفلاتر والبحث.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        $query = ServiceRequest::query()->latest();

        if (in_array($status, [
            ServiceRequest::STATUS_PENDING_REVIEW,
            ServiceRequest::STATUS_CONTACTED,
            ServiceRequest::STATUS_CONFIRMED,
            ServiceRequest::STATUS_CANCELLED,
        ], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('request_number', 'like', "%{$search}%");
            });
        }

        $requests = $query->paginate(15)->withQueryString();

        return view('admin.service-requests.index', [
            'requests' => $requests,
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * تفاصيل طلب عميل.
     */
    public function show(ServiceRequest $serviceRequest): View
    {
        $serviceRequest->load('images');

        return view('admin.service-requests.show', [
            'request' => $serviceRequest,
        ]);
    }

    /**
     * تعليم الطلب كـ "تم التواصل".
     */
    public function markContacted(ServiceRequest $serviceRequest): RedirectResponse
    {
        if ($serviceRequest->status !== ServiceRequest::STATUS_PENDING_REVIEW) {
            return back()->with('error', __('This action is not allowed for the current status.'));
        }

        $serviceRequest->update(['status' => ServiceRequest::STATUS_CONTACTED]);

        $this->log('service_request.contacted', $serviceRequest,
            __('Marked request :no as contacted', ['no' => $serviceRequest->request_number]));

        return back()->with('success', __('The request has been marked as contacted.'));
    }

    /**
     * إلغاء الطلب (دون حذفه).
     */
    public function cancel(ServiceRequest $serviceRequest): RedirectResponse
    {
        if ($serviceRequest->isConverted()) {
            return back()->with('error', __('A converted request cannot be cancelled.'));
        }

        if ($serviceRequest->isCancelled()) {
            return back()->with('error', __('The request is already cancelled.'));
        }

        $serviceRequest->update(['status' => ServiceRequest::STATUS_CANCELLED]);

        $this->log('service_request.cancelled', $serviceRequest,
            __('Cancelled request :no', ['no' => $serviceRequest->request_number]));

        return back()->with('success', __('The request has been cancelled.'));
    }

    /**
     * عرض نموذج المراجعة قبل التحويل إلى طلب رسمي.
     */
    public function convertForm(ServiceRequest $serviceRequest): View|RedirectResponse
    {
        if (! $serviceRequest->canBeConverted()) {
            return redirect()
                ->route('admin.service-requests.show', $serviceRequest)
                ->with('error', $this->convertBlockedReason($serviceRequest));
        }

        return view('admin.service-requests.convert', [
            'request' => $serviceRequest,
        ]);
    }

    /**
     * تنفيذ التحويل إلى طلب رسمي.
     */
    public function convert(ConvertServiceRequestRequest $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        if (! $serviceRequest->canBeConverted()) {
            return redirect()
                ->route('admin.service-requests.show', $serviceRequest)
                ->with('error', $this->convertBlockedReason($serviceRequest));
        }

        $data = $request->validated();

        // بناء البنود الصالحة فقط (وصف غير فارغ)
        $items = [];
        $subtotal = 0.0;
        foreach ($data['items'] as $row) {
            $description = trim((string) ($row['description'] ?? ''));
            if ($description === '') {
                continue;
            }

            $quantity = max(1, (int) ($row['quantity'] ?? 1));
            $unitPrice = max(0, (float) ($row['unit_price'] ?? 0));
            $lineTotal = round($quantity * $unitPrice, 2);
            $subtotal += $lineTotal;

            $items[] = [
                'service_id' => $row['service_id'] ?? null,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        // قاعدة: خدمة واحدة على الأقل
        if (count($items) === 0) {
            return back()->withInput()->withErrors([
                'items' => __('At least one service with a description is required.'),
            ]);
        }

        $subtotal = round($subtotal, 2);
        $discount = round((float) ($data['discount'] ?? 0), 2);

        // قاعدة: الخصم لا يتجاوز الإجمالي الفرعي
        if ($discount > $subtotal) {
            return back()->withInput()->withErrors([
                'discount' => __('Discount cannot exceed the subtotal.'),
            ]);
        }

        $total = round($subtotal - $discount, 2);

        // الدفع (طريقة "لاحقًا" تعني عدم وجود مبلغ مدفوع الآن)
        $method = $data['payment_method'];
        $paid = $method === 'later' ? 0.0 : round((float) ($data['paid_amount'] ?? 0), 2);

        // قاعدة: المدفوع لا يتجاوز الإجمالي
        if ($paid > $total) {
            return back()->withInput()->withErrors([
                'paid_amount' => __('Paid amount cannot exceed the total.'),
            ]);
        }

        $remaining = round($total - $paid, 2);
        $paymentStatus = $this->derivePaymentStatus($total, $paid);

        $order = DB::transaction(function () use ($serviceRequest, $data, $items, $subtotal, $discount, $total, $paid, $remaining, $paymentStatus, $method) {
            // العميل: بحث بالهاتف أو إنشاء جديد
            $customer = Customer::firstOrNew(['phone' => $data['phone']]);
            $customer->name = $data['customer_name'];
            $customer->address = $this->composeAddress($data);
            // الاحتفاظ بموقع العميل
            $customer->latitude = $serviceRequest->latitude;
            $customer->longitude = $serviceRequest->longitude;
            $customer->location_url = $serviceRequest->location_url;
            $customer->save();

            // إنشاء الطلب الرسمي
            $order = Order::create([
                'order_number' => 'TEMP',
                'customer_id' => $customer->id,
                'service_request_id' => $serviceRequest->id,
                'accountant_id' => $this->userId(),
                'status' => Order::STATUS_NEW,
                'latitude' => $serviceRequest->latitude,
                'longitude' => $serviceRequest->longitude,
                'location_url' => $serviceRequest->location_url,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax_percentage' => 0,
                'tax_amount' => 0,
                'total' => $total,
                'paid_amount' => $paid,
                'due_amount' => $remaining,
                'payment_status' => $paymentStatus,
                'notes' => $data['notes'] ?? null,
            ]);

            $order->update([
                'order_number' => 'ORD-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
            ]);

            // البنود
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'service_id' => $item['service_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                ]);
            }

            // دفعة (فقط إذا وُجد مبلغ مدفوع وطريقة ليست "لاحقًا")
            if ($paid > 0 && $method !== 'later') {
                Payment::create([
                    'order_id' => $order->id,
                    'received_by' => $this->userId(),
                    'amount' => $paid,
                    'method' => $method,
                    'paid_at' => now(),
                ]);
            }

            // تحديث الطلب المبدئي
            $serviceRequest->update([
                'status' => ServiceRequest::STATUS_CONFIRMED,
                'converted_order_id' => $order->id,
            ]);

            $this->log('service_request.converted', $order,
                __('Converted request :req into order :ord', [
                    'req' => $serviceRequest->request_number,
                    'ord' => $order->order_number,
                ]));

            return $order;
        });

        return redirect()
            ->route('admin.service-requests.show', $serviceRequest)
            ->with('success', __('The official order has been created successfully.'))
            ->with('order_number', $order->order_number);
    }

    // ---------------------------------------------------------------------
    // مساعدات داخلية
    // ---------------------------------------------------------------------

    private function derivePaymentStatus(float $total, float $paid): string
    {
        if ($paid <= 0) {
            return Order::PAYMENT_UNPAID;
        }

        if ($paid >= $total && $total > 0) {
            return Order::PAYMENT_PAID;
        }

        return Order::PAYMENT_PARTIAL;
    }

    private function composeAddress(array $data): string
    {
        return collect([
            $data['wilaya'] ?? null,
            $data['area'] ?? null,
            $data['address'] ?? null,
        ])->filter()->implode(' - ');
    }

    private function convertBlockedReason(ServiceRequest $serviceRequest): string
    {
        if ($serviceRequest->isCancelled()) {
            return __('A cancelled request cannot be converted.');
        }

        return __('This request has already been converted.');
    }

    private function userId(): ?int
    {
        return auth()->id();
    }

    /**
     * تسجيل العملية في activity_logs (إن كان الجدول جاهزًا).
     */
    private function log(string $action, object $subject, string $description): void
    {
        ActivityLog::create([
            'user_id' => $this->userId(),
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
}
