@if($booking->status === 'completed')
    <div class="card shadow-sm mt-3">
        <div class="card-header bg-white">
            <h5 class="mb-0">Đánh giá dịch vụ</h5>
        </div>

        <div class="card-body">
            @if($booking->review)
                <p class="mb-1"><strong>Đánh giá của bạn:</strong></p>
                <div class="text-warning mb-2">★ {{ $booking->review->rating }}/5</div>
                <p class="text-muted mb-0">{{ $booking->review->comment ?: 'Không có nhận xét.' }}</p>
            @else
                <p class="text-muted">
                    Đơn đã hoàn tất. Bạn có thể đánh giá trải nghiệm lưu trú.
                </p>

                <a href="{{ route('customer.reviews.create', $booking) }}" class="btn btn-primary w-100">
                    Viết đánh giá
                </a>
            @endif
        </div>
    </div>
@endif