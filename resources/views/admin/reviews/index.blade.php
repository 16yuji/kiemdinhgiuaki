@extends('layouts.dashboard')

@section('title', 'Kiểm duyệt đánh giá')
@section('page-title', 'Kiểm duyệt đánh giá')

@section('content')
<div class="tm-page-toolbar">
    <div>
        <div class="tm-eyebrow"><i class="bi bi-star-half"></i> Review moderation</div>
        <h4 class="fw-black mb-1">Danh sách đánh giá</h4>
        <p class="text-muted fw-semibold mb-0">Ẩn nội dung vi phạm hoặc khôi phục đánh giá hợp lệ.</p>
    </div>
    <span class="tm-dashboard-chip"><i class="bi bi-chat-heart"></i> {{ $reviews->total() }} đánh giá</span>
</div>

<div class="tm-form-card mb-4">
    <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-7">
            <div class="tm-field">
                <label><i class="bi bi-search me-1"></i> Từ khóa</label>
                <input
                    type="text"
                    name="keyword"
                    class="form-control"
                    value="{{ request('keyword') }}"
                    placeholder="Tên khách sạn, khách hàng, nội dung đánh giá"
                >
            </div>
        </div>

        <div class="col-lg-3">
            <div class="tm-field">
                <label>Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="visible" @selected(request('status') === 'visible')>Đang hiển thị</option>
                    <option value="hidden" @selected(request('status') === 'hidden')>Đã ẩn</option>
                </select>
            </div>
        </div>

        <div class="col-lg-2 d-flex gap-2">
            <button class="btn tm-btn-primary flex-fill" type="submit">
                <i class="bi bi-funnel me-1"></i> Lọc
            </button>
            <a href="{{ route('admin.reviews.index') }}" class="btn tm-btn-light">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

<div class="row g-3">
    @forelse($reviews as $review)
        <div class="col-12">
            <div class="tm-review-card">
                <div class="d-flex justify-content-between gap-3 flex-wrap">
                    <div>
                        <div class="tm-eyebrow mb-1"><i class="bi bi-building"></i> {{ $review->hotel->name }}</div>
                        <h5 class="fw-black mb-1">Đơn {{ $review->booking->booking_code }}</h5>
                        <div class="text-muted fw-semibold small">
                            Khách hàng: {{ $review->customer->name }} · {{ $review->customer->email }}
                        </div>
                    </div>

                    <div class="text-end">
                        <div class="text-warning fw-black mb-2">
                            <i class="bi bi-star-fill"></i> {{ $review->rating }}/5
                        </div>

                        @if($review->status === 'visible')
                            <span class="tm-status tm-status-success">Đang hiển thị</span>
                        @else
                            <span class="tm-status tm-status-muted">Đã ẩn</span>
                        @endif
                    </div>
                </div>

                <div class="tm-neutral-box mt-3">
                    <p class="mb-0 fw-semibold">{{ $review->comment ?: 'Khách hàng không để lại nhận xét.' }}</p>
                </div>

                @if($review->reply)
                    <div class="tm-soft-panel mt-3">
                        <strong><i class="bi bi-reply me-1"></i> Phản hồi của Owner</strong>
                        <p class="mb-1 mt-2">{{ $review->reply->reply }}</p>
                        <div class="text-muted small">Người phản hồi: {{ $review->reply->owner->name ?? 'Owner' }}</div>
                    </div>
                @endif

                @if($review->status === 'hidden')
                    <div class="tm-policy-box mt-3">
                        <strong>Lý do ẩn:</strong> {{ $review->hidden_reason ?: '-' }}
                        <div class="small fw-semibold mt-1">
                            Ẩn bởi {{ $review->hiddenBy->name ?? 'Admin' }}
                            · {{ $review->hidden_at ? $review->hidden_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                @endif

                <div class="mt-3">
                    @if($review->status === 'visible')
                        <button
                            class="btn btn-sm btn-outline-danger"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#hideReview{{ $review->id }}"
                        >
                            <i class="bi bi-eye-slash me-1"></i> Ẩn đánh giá
                        </button>

                        <div class="collapse mt-3" id="hideReview{{ $review->id }}">
                            <form method="POST" action="{{ route('admin.reviews.hide', $review) }}" class="tm-form-card">
                                @csrf

                                <div class="tm-field">
                                    <label>Lý do ẩn đánh giá <span class="text-danger">*</span></label>
                                    <textarea
                                        name="hidden_reason"
                                        class="form-control @error('hidden_reason') is-invalid @enderror"
                                        rows="3"
                                        placeholder="Ví dụ: Nội dung không phù hợp, spam, xúc phạm..."
                                        required
                                    >{{ old('hidden_reason') }}</textarea>

                                    @error('hidden_reason')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button class="btn btn-danger btn-sm mt-3" type="submit">
                                    <i class="bi bi-eye-slash me-1"></i> Xác nhận ẩn
                                </button>
                            </form>
                        </div>
                    @else
                        <form
                            method="POST"
                            action="{{ route('admin.reviews.restore', $review) }}"
                            class="d-inline"
                            onsubmit="return confirm('Khôi phục đánh giá này?')"
                        >
                            @csrf
                            <button class="btn btn-sm btn-outline-success" type="submit">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Khôi phục đánh giá
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="tm-surface">
                <div class="tm-empty-state">
                    <i class="bi bi-chat-square-heart"></i>
                    Chưa có đánh giá nào.
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="mt-3">
    {{ $reviews->links() }}
</div>
@endsection
