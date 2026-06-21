@extends('layouts.dashboard')

@section('title', 'Đánh giá & phản hồi')
@section('page-title', 'Đánh giá & phản hồi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Đánh giá từ khách hàng</h4>
        <p class="text-muted mb-0">Theo dõi và phản hồi đánh giá thuộc khách sạn của bạn.</p>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        @forelse($reviews as $review)
            <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="mb-1">{{ $review->hotel->name }}</h5>
                        <div class="text-muted small">
                            Khách hàng: {{ $review->customer->name }}
                            |
                            Đơn: {{ $review->booking->booking_code }}
                        </div>
                    </div>

                    <div class="text-end">
                        <div class="text-warning">★ {{ $review->rating }}/5</div>
                        @if($review->status === 'visible')
                            <span class="badge bg-success">Hiển thị</span>
                        @else
                            <span class="badge bg-secondary">Đã ẩn</span>
                        @endif
                    </div>
                </div>

                <p class="mt-3 mb-2">
                    {{ $review->comment ?: 'Khách hàng không để lại nhận xét.' }}
                </p>

                @if($review->reply)
                    <div class="bg-light rounded p-3 mt-3">
                        <strong>Phản hồi của bạn:</strong>
                        <p class="mb-0">{{ $review->reply->reply }}</p>
                    </div>
                @elseif($review->status === 'visible')
                    <form method="POST" action="{{ route('owner.reviews.reply.store', $review) }}" class="mt-3">
                        @csrf

                        <div class="mb-2">
                            <label class="form-label">Phản hồi đánh giá</label>
                            <textarea
                                name="reply"
                                class="form-control @error('reply') is-invalid @enderror"
                                rows="3"
                                placeholder="Nhập phản hồi của chủ cơ sở..."
                                required
                            >{{ old('reply') }}</textarea>

                            @error('reply')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="btn btn-primary" type="submit">
                            Gửi phản hồi
                        </button>
                    </form>
                @else
                    <div class="alert alert-secondary mt-3 mb-0">
                        Đánh giá đã bị ẩn nên không thể phản hồi.
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center text-muted py-4">
                Chưa có đánh giá nào.
            </div>
        @endforelse

        <div class="mt-3">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection