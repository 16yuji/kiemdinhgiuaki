<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PartnerRequest;
use Illuminate\Http\Request;

class PartnerRequestController extends Controller
{
    public function create()
    {
        if (auth()->user()->role !== 'customer') {
            return redirect()
                ->route('customer.home')
                ->with('error', 'Chỉ tài khoản Customer mới có thể gửi yêu cầu trở thành đối tác.');
        }

        $latestRequest = PartnerRequest::where('user_id', auth()->id())
            ->latest()
            ->first();

        return view('customer.partner-request.create', compact('latestRequest'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'customer') {
            return redirect()
                ->route('customer.home')
                ->with('error', 'Chỉ tài khoản Customer mới có thể gửi yêu cầu trở thành đối tác.');
        }
        $existingPending = PartnerRequest::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return back()->with('error', 'Bạn đã có yêu cầu đang chờ duyệt.');
        }

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'contact_email' => ['required', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'business_name.required' => 'Vui lòng nhập tên cơ sở kinh doanh.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'contact_phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'contact_email.required' => 'Vui lòng nhập email liên hệ.',
        ]);

        PartnerRequest::create([
            'user_id' => auth()->id(),
            'business_name' => $data['business_name'],
            'address' => $data['address'],
            'contact_phone' => $data['contact_phone'],
            'contact_email' => $data['contact_email'],
            'description' => $data['description'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('customer.partner-request.create')
            ->with('success', 'Gửi yêu cầu trở thành đối tác thành công. Vui lòng chờ Admin duyệt.');
    }
}