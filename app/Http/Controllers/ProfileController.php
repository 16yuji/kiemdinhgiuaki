<?php

namespace App\Http\Controllers;

use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', 'in:male,female,other'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'birthday.before' => 'Ngày sinh phải nhỏ hơn ngày hiện tại.',
            'avatar.image' => 'Ảnh đại diện không hợp lệ.',
            'avatar.max' => 'Ảnh đại diện không được vượt quá 2MB.',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        SystemLogService::write(
            'update_profile',
            'profile',
            get_class($user),
            $user->id,
            'Người dùng cập nhật hồ sơ cá nhân.',
            [
                'email' => $user->email,
                'role' => $user->role,
            ]
        );

        return Redirect::route('profile.edit')
            ->with('success', 'Cập nhật hồ sơ thành công.');
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        SystemLogService::write(
            'change_password',
            'profile',
            get_class($user),
            $user->id,
            'Người dùng đổi mật khẩu.',
            [
                'email' => $user->email,
                'role' => $user->role,
            ]
        );

        return Redirect::route('profile.edit')
            ->with('success', 'Đổi mật khẩu thành công.');
    }

    public function paymentInfo(Request $request): View
    {
        if ($request->user()->role !== 'owner') {
            abort(403, 'Chỉ Owner mới được cập nhật thông tin thanh toán.');
        }

        return view('profile.payment-info', [
            'user' => $request->user(),
        ]);
    }

    public function updatePaymentInfo(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'owner') {
            abort(403, 'Chỉ Owner mới được cập nhật thông tin thanh toán.');
        }

        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_account_number' => ['required', 'string', 'max:100'],
            'bank_account_name' => ['required', 'string', 'max:255'],
        ], [
            'bank_name.required' => 'Vui lòng nhập tên ngân hàng.',
            'bank_account_number.required' => 'Vui lòng nhập số tài khoản.',
            'bank_account_name.required' => 'Vui lòng nhập tên chủ tài khoản.',
        ]);

        $user->update($data);

        SystemLogService::write(
            'update_payment_info',
            'profile',
            get_class($user),
            $user->id,
            'Owner cập nhật thông tin thanh toán.',
            [
                'email' => $user->email,
                'bank_name' => $user->bank_name,
            ]
        );

        return Redirect::route('profile.payment-info')
            ->with('success', 'Cập nhật thông tin thanh toán thành công.');
    }

    public function destroy(Request $request)
    {
        abort(403, 'Chức năng xóa tài khoản đang tạm khóa trong phạm vi đồ án.');
    }
}