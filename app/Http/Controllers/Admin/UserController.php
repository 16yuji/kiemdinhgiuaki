<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemLogService;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load([
            'hotels',
            'bookings',
            'partnerRequests',
            'reviews',
        ]);

        return view('admin.users.show', compact('user'));
    }

    public function lock(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản của mình.');
        }

        if ($user->role === 'admin') {
            return back()->with('error', 'Không thể khóa tài khoản Admin khác trong phạm vi đồ án.');
        }

        if ($user->status === 'locked') {
            return redirect()
                ->route('admin.users.show', $user)
                ->with('error', 'Tài khoản này đã bị khóa.');
        }

        $data = $request->validate([
            'lock_reason' => ['required', 'string', 'max:1000'],
        ], [
            'lock_reason.required' => 'Vui lòng nhập lý do khóa tài khoản.',
        ]);

        $user->update([
            'status' => 'locked',
            'locked_reason' => $data['lock_reason'],
            'locked_at' => now(),
            'locked_by' => auth()->id(),
        ]);

        SystemLogService::write(
            'lock_user',
            'users',
            User::class,
            $user->id,
            'Admin khóa tài khoản người dùng.',
            [
                'email' => $user->email,
                'role' => $user->role,
                'reason' => $data['lock_reason'],
            ]
        );

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Khóa tài khoản thành công.');
    }

    public function unlock(User $user)
    {
        if ($user->status === 'active') {
            return back()->with('error', 'Tài khoản này đang hoạt động.');
        }

        $user->update([
            'status' => 'active',
            'locked_reason' => null,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        SystemLogService::write(
            'unlock_user',
            'users',
            User::class,
            $user->id,
            'Admin mở khóa tài khoản người dùng.',
            [
                'email' => $user->email,
                'role' => $user->role,
            ]
        );

        return back()->with('success', 'Mở khóa tài khoản thành công.');
    }

    public function confirmLock(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.show', $user)
                ->with('error', 'Bạn không thể tự khóa tài khoản của mình.');
        }

        if ($user->role === 'admin') {
            return redirect()
                ->route('admin.users.show', $user)
                ->with('error', 'Không thể khóa tài khoản Admin khác trong phạm vi đồ án.');
        }

        return view('admin.users.lock', compact('user'));
    }
}