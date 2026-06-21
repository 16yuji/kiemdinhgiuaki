@extends('layouts.frontend')

@section('title', 'Hồ sơ cá nhân')

@section('content')
<section class="tm-section">
    <div class="container">
        <div class="tm-page-toolbar">
            <div>
                <div class="tm-eyebrow"><i class="bi bi-person-badge"></i> Tài khoản Travel Mate</div>
                <h1 class="tm-heading-md mb-2">Hồ sơ cá nhân</h1>
                <p class="tm-lead mb-0">Cập nhật thông tin liên hệ, ảnh đại diện và bảo mật đăng nhập.</p>
            </div>

            @if($user->role === 'owner')
                <a href="{{ route('profile.payment-info') }}" class="btn tm-btn-primary">
                    <i class="bi bi-credit-card-2-front me-1"></i> Thông tin thanh toán
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="tm-form-card mb-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        @if($user->avatar)
                            <img
                                src="{{ asset('storage/' . $user->avatar) }}"
                                alt="Avatar"
                                class="rounded-circle border"
                                style="width: 92px; height: 92px; object-fit: cover;"
                            >
                        @else
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                                 style="width: 92px; height: 92px; background: linear-gradient(135deg,#060daa,#38bdf8); color: #fff; font-size: 34px; font-weight: 900;">
                                {{ mb_substr($user->name, 0, 1) }}
                            </div>
                        @endif

                        <div>
                            <h2 class="h4 fw-black mb-1">{{ $user->name }}</h2>
                            <div class="tm-status-stack">
                                <span class="tm-status tm-status-primary">{{ ucfirst($user->role) }}</span>
                                <span class="tm-status {{ $user->status === 'active' ? 'tm-status-success' : 'tm-status-danger' }}">
                                    {{ $user->status === 'active' ? 'Đang hoạt động' : 'Bị khóa' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="tm-field">
                                    <label><i class="bi bi-image me-1"></i> Ảnh đại diện</label>
                                    <input
                                        type="file"
                                        name="avatar"
                                        class="form-control @error('avatar') is-invalid @enderror"
                                        accept="image/*"
                                    >
                                    @error('avatar')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text mt-2">Hỗ trợ jpg, jpeg, png, webp. Tối đa 2MB.</div>
                            </div>

                            <div class="col-md-6">
                                <div class="tm-field">
                                    <label>Họ và tên <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $user->name) }}"
                                        required
                                    >
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="tm-field">
                                    <label>Email đăng nhập</label>
                                    <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                                </div>
                                <div class="form-text mt-2">Email đăng nhập đang được khóa chỉnh sửa trong phạm vi đồ án.</div>
                            </div>

                            <div class="col-md-6">
                                <div class="tm-field">
                                    <label>Số điện thoại</label>
                                    <input
                                        type="text"
                                        name="phone"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone', $user->phone) }}"
                                    >
                                    @error('phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="tm-field">
                                    <label>Giới tính</label>
                                    <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                        <option value="">Chưa chọn</option>
                                        <option value="male" @selected(old('gender', $user->gender) === 'male')>Nam</option>
                                        <option value="female" @selected(old('gender', $user->gender) === 'female')>Nữ</option>
                                        <option value="other" @selected(old('gender', $user->gender) === 'other')>Khác</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="tm-field">
                                    <label>Ngày sinh</label>
                                    <input
                                        type="date"
                                        name="birthday"
                                        class="form-control @error('birthday') is-invalid @enderror"
                                        value="{{ old('birthday', $user->birthday ? $user->birthday->format('Y-m-d') : '') }}"
                                    >
                                    @error('birthday')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button class="btn tm-btn-primary" type="submit">
                                <i class="bi bi-save2 me-1"></i> Lưu thay đổi
                            </button>
                            <a href="{{ route('customer.home') }}" class="btn tm-btn-light">
                                <i class="bi bi-house-door me-1"></i> Về trang chủ
                            </a>
                        </div>
                    </form>
                </div>

                <div class="tm-form-card">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h2 class="h4 fw-black mb-1">Đổi mật khẩu</h2>
                            <p class="text-muted fw-semibold mb-0">Dùng mật khẩu mạnh để bảo vệ tài khoản đặt phòng và vận hành.</p>
                        </div>
                        <span class="tm-dashboard-chip"><i class="bi bi-shield-lock"></i> Bảo mật</span>
                    </div>

                    <form method="POST" action="{{ route('profile.password.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="tm-field">
                                    <label>Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                    <input
                                        type="password"
                                        name="current_password"
                                        class="form-control @error('current_password') is-invalid @enderror"
                                        required
                                    >
                                    @error('current_password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="tm-field">
                                    <label>Mật khẩu mới <span class="text-danger">*</span></label>
                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        required
                                    >
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="tm-field">
                                    <label>Nhập lại mật khẩu mới <span class="text-danger">*</span></label>
                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        class="form-control"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <button class="btn tm-btn-light mt-4" type="submit">
                            <i class="bi bi-key me-1"></i> Đổi mật khẩu
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="tm-soft-panel mb-3">
                    <div class="tm-eyebrow mb-2"><i class="bi bi-info-circle"></i> Quyền truy cập</div>
                    <div class="tm-meta-card">
                        <div>
                            <span>Vai trò</span>
                            <strong>{{ ucfirst($user->role) }}</strong>
                        </div>
                        <div>
                            <span>Trạng thái</span>
                            <strong>{{ $user->status === 'active' ? 'Hoạt động' : 'Bị khóa' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="tm-neutral-box mb-3">
                    <h3 class="h5 fw-black mb-2"><i class="bi bi-headset me-1 text-primary"></i> Hỗ trợ Travel Mate</h3>
                    <div class="tm-info-list">
                        <div>
                            <span>Hotline</span>
                            <strong>1900 2026</strong>
                        </div>
                        <div>
                            <span>Email</span>
                            <strong>support@travelmate.local</strong>
                        </div>
                    </div>
                </div>

                <div class="tm-policy-box">
                    <strong>Lưu ý bảo mật</strong>
                    <p class="mb-0 mt-2 fw-semibold">Không chia sẻ mật khẩu, mã OTP hoặc tài khoản Google với người khác. Khi thấy hoạt động bất thường, hãy đổi mật khẩu ngay.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
