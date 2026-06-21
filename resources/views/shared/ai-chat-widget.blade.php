<div class="tm-ai-chat js-ai-chat" data-endpoint="{{ route('ai.chat.store') }}">
    <button type="button" class="tm-ai-chat-toggle js-ai-chat-toggle" aria-label="Mở trợ lý Travel Mate">
        <i class="bi bi-stars"></i>
    </button>

    <section class="tm-ai-chat-panel" aria-label="AI tư vấn Travel Mate">
        <header>
            <div>
                <span>Travel Mate AI</span>
                <strong>Trợ lý du lịch</strong>
            </div>
            <button type="button" class="js-ai-chat-close" aria-label="Đóng chatbot">
                <i class="bi bi-x-lg"></i>
            </button>
        </header>

        <div class="tm-ai-chat-messages js-ai-chat-messages">
            <div class="tm-ai-message tm-ai-message-bot">
                Chào bạn, mình là Travel Mate AI. Bạn có thể trò chuyện tự nhiên với mình về chuyến đi, khách sạn, lịch trình, thanh toán, hủy đơn hoặc cách dùng Travel Mate. Mình chỉ tư vấn, mọi đặt phòng/thanh toán vẫn cần bạn xác nhận trên giao diện.
            </div>
        </div>

        <div class="tm-ai-chat-prompts" aria-label="Gợi ý câu hỏi nhanh">
            <button type="button" class="js-ai-chat-prompt" data-message="Bạn có thể giúp mình làm gì?">Bạn làm được gì?</button>
            <button type="button" class="js-ai-chat-prompt" data-message="Tìm khách sạn Đà Nẵng có hồ bơi">Gợi ý khách sạn</button>
            <button type="button" class="js-ai-chat-prompt" data-message="Nếu mình đã thanh toán rồi muốn hủy thì sao?">Hủy/hoàn tiền</button>
            <button type="button" class="js-ai-chat-prompt" data-message="Gợi ý lịch trình 2 ngày gần biển">Lịch trình 2 ngày</button>
        </div>

        <form class="tm-ai-chat-form js-ai-chat-form" method="POST" action="{{ route('ai.chat.store') }}">
            @csrf
            <input
                type="text"
                name="message"
                maxlength="600"
                autocomplete="off"
                placeholder="Nhập câu hỏi hoặc trò chuyện với Travel Mate AI..."
                required
            >
            <button type="submit" aria-label="Gửi câu hỏi">
                <i class="bi bi-send"></i>
            </button>
        </form>
    </section>
</div>
