<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderPaymentRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\ActivityLog;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\Payment;
use App\Support\QrCodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * قائمة الطلبات الرسمية مع الفلاتر والبحث.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $payment = $request->query('payment');
        $search = trim((string) $request->query('q', ''));

        $query = Order::query()->with('customer')->latest();

        if (in_array($status, [
            Order::STATUS_NEW, Order::STATUS_CLEANING, Order::STATUS_READY,
            Order::STATUS_DELIVERED, Order::STATUS_CANCELLED,
        ], true)) {
            $query->where('status', $status);
        }

        if (in_array($payment, [Order::PAYMENT_UNPAID, Order::PAYMENT_PARTIAL, Order::PAYMENT_PAID], true)) {
            $query->where('payment_status', $payment);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(15)->withQueryString(),
            'status' => $status,
            'payment' => $payment,
            'search' => $search,
        ]);
    }

    /**
     * تفاصيل الطلب الرسمي.
     */
    public function show(Order $order): View
    {
        $order->load(['customer', 'items', 'payments.receivedBy', 'serviceRequest', 'deliveryTasks.driver']);

        return view('admin.orders.show', [
            'order' => $order,
            'activeDrivers' => \App\Models\Driver::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * فاتورة A4 (للطباعة على ورق عادي).
     */
    public function invoice(Order $order): View
    {
        return view('admin.orders.invoice', $this->invoiceData($order));
    }

    /**
     * إيصال حراري 80mm.
     */
    public function receipt(Order $order): View
    {
        return view('admin.orders.receipt', $this->invoiceData($order));
    }

    /**
     * يبني بيانات الفاتورة/الإيصال (الطلب، الإعدادات، QR، الضريبة).
     */
    private function invoiceData(Order $order): array
    {
        $order->load(['customer', 'items', 'payments', 'serviceRequest']);
        $settings = BusinessSetting::current();

        // تحديد بيانات QR لموقع العميل
        $showQr = (bool) ($settings->invoice_show_qr ?? true);
        $qrData = $this->resolveLocationUrl($order);
        $qrSvg = ($showQr && $qrData) ? QrCodeGenerator::svg($qrData, 180) : null;

        // الضريبة تظهر فقط إذا كانت مفعّلة ومسموح عرضها على الفاتورة
        $taxEnabled = (bool) ($settings->tax_enabled ?? false) && (bool) ($settings->show_tax_on_invoice ?? true);
        $taxRate = (float) ($settings->tax_percentage ?? 0);
        $taxAmount = 0.0;
        if ($taxEnabled && $taxRate > 0 && (float) $order->total > 0) {
            $taxAmount = round((float) $order->total - ((float) $order->total / (1 + $taxRate / 100)), 2);
        }

        return [
            'order' => $order,
            'settings' => $settings,
            'qrData' => $qrData,
            'qrSvg' => $qrSvg,
            'showQr' => $showQr,
            'taxEnabled' => $taxEnabled,
            'taxRate' => $taxRate,
            'taxAmount' => $taxAmount,
        ];
    }

    /**
     * يحدد رابط موقع العميل لـ QR حسب الأولوية المطلوبة.
     */
    private function resolveLocationUrl(Order $order): ?string
    {
        if ($order->latitude && $order->longitude) {
            return 'https://www.google.com/maps?q='.$order->latitude.','.$order->longitude;
        }

        if (! empty($order->location_url)) {
            return $order->location_url;
        }

        $customer = $order->customer;
        if ($customer && $customer->latitude && $customer->longitude) {
            return 'https://www.google.com/maps?q='.$customer->latitude.','.$customer->longitude;
        }

        if ($customer && ! empty($customer->location_url)) {
            return $customer->location_url;
        }

        return null;
    }

    /**
     * تغيير حالة الطلب.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:cleaning,ready,delivered,cancelled'],
        ]);

        if ($order->isCancelled()) {
            return back()->with('error', __('Cannot change a cancelled order.'));
        }

        $newStatus = $validated['status'];
        $order->status = $newStatus;

        if ($newStatus === Order::STATUS_DELIVERED) {
            $order->delivered_at = now();
        }

        $order->save();

        $this->log('order.status_updated', $order,
            __('Order :no status changed to :status', ['no' => $order->order_number, 'status' => $newStatus]));

        // تنبيه عند التسليم مع وجود متبقٍّ
        if ($newStatus === Order::STATUS_DELIVERED && $order->hasBalance()) {
            return back()
                ->with('success', __('The order status has been updated.'))
                ->with('warning', __('There is a remaining balance on this customer.'));
        }

        return back()->with('success', __('The order status has been updated.'));
    }

    /**
     * تسجيل دفعة إضافية.
     */
    public function storePayment(StoreOrderPaymentRequest $request, Order $order): RedirectResponse
    {
        if ($order->isCancelled()) {
            return back()->with('error', __('Cannot add a payment to a cancelled order.'));
        }

        $data = $request->validated();
        $amount = round((float) $data['amount'], 2);

        // منع تجاوز إجمالي الطلب
        if (round((float) $order->paid_amount + $amount, 2) > (float) $order->total) {
            return back()->withInput()->withErrors([
                'amount' => __('Total paid cannot exceed the order total.'),
            ]);
        }

        DB::transaction(function () use ($order, $data, $amount) {
            Payment::create([
                'order_id' => $order->id,
                'received_by' => auth()->id(),
                'amount' => $amount,
                'method' => $data['payment_method'],
                'paid_at' => $data['paid_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $order->recalcTotals();
            $order->save();

            $this->log('order.payment_recorded', $order,
                __('Recorded payment of :amount for order :no', ['amount' => $amount, 'no' => $order->order_number]));
        });

        return back()->with('success', __('Payment recorded successfully.'));
    }

    /**
     * تعديل محدود (الخصم والملاحظات).
     */
    public function edit(Order $order): View|RedirectResponse
    {
        if ($order->isCancelled()) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', __('Cannot change a cancelled order.'));
        }

        return view('admin.orders.edit', ['order' => $order]);
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        if ($order->isCancelled()) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', __('Cannot change a cancelled order.'));
        }

        $data = $request->validated();
        $discount = round((float) ($data['discount'] ?? 0), 2);

        // الخصم لا يتجاوز الإجمالي الفرعي
        if ($discount > (float) $order->subtotal) {
            return back()->withInput()->withErrors([
                'discount' => __('Discount cannot exceed the subtotal.'),
            ]);
        }

        $newTotal = round((float) $order->subtotal - $discount + (float) $order->tax_amount, 2);

        // الإجمالي لا يقل عن المدفوع
        if ($newTotal < (float) $order->paid_amount) {
            return back()->withInput()->withErrors([
                'discount' => __('Total cannot be less than the paid amount.'),
            ]);
        }

        $order->discount = $discount;
        $order->notes = $data['notes'] ?? null;
        $order->recalcTotals();
        $order->save();

        $this->log('order.updated', $order,
            __('Updated order :no (discount/notes)', ['no' => $order->order_number]));

        return redirect()->route('admin.orders.show', $order)
            ->with('success', __('The order has been updated.'));
    }

    /**
     * تسجيل العملية في activity_logs.
     */
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
