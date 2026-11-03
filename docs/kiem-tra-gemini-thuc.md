# Kiểm tra Gemini thực trên oceanquiz1l

Ngày: 03/11/2026. Chỉ kiểm tra local trên SQLite preview.sqlite, không commit/push.

- Theo yêu cầu người dùng, đã sao chép GEMINI_API_KEY và model đang dùng từ oceanquiz1 sang .env của oceanquiz1l. Không in khóa, không đưa vào tài liệu hoặc mã nguồn. Đã xóa cache cấu hình và khởi động lại server 8002.
- Trang Gemini hiển thị “Đã cấu hình khóa”, chọn bài Article #1 “Cá voi xanh”, nguồn https://vi.wikipedia.org/?curid=22924.
- Đã bấm nút sinh câu hỏi thật trên giao diện. Model gemini-3.5-flash-lite trả thành công, log #1, tạo QuizSet #3 “Cá voi xanh — Kiểm tra Gemini thực tế” ở trạng thái nháp, đủ 5 câu và 20 lựa chọn.
- Đã đối chiếu 5 câu và đáp án với bài đọc. Làm rõ câu 2 và câu 4 là thông tin theo bài đọc; sửa câu 5 để nói riêng về bảo vệ cá voi xanh từ năm 1966, dùng tên IWC chuẩn. Nguồn kiểm chứng: https://iwc.int/about-whales/whale-species/blue-whale. Số liệu sữa cũng được đối chiếu với https://www.mass.gov/info-details/blue-whale.
- Đã lưu 5 câu nháp qua biểu mẫu; thông báo lưu thành công xuất hiện và nội dung sửa được lưu trong DB.
- Đã bấm “Kiểm tra & xuất bản”; trang hiện thông báo xuất bản thành công và các trường biên tập bị khóa. Kiểm tra DB xác nhận is_active=true, source=ai, article_id=1, đúng 5 câu, mỗi câu 4 lựa chọn và đáp án hợp lệ.
- Không làm bài, không tạo điểm hoặc lịch sử giả. Không thay đổi CSS/layout.
- Đã kiểm tra khóa không có trong phản hồi/nhật ký của lần gọi. .env được Git bỏ qua.

Mở bộ đã xuất bản: http://127.0.0.1:8002/admin/question-sets/3/edit

Ảnh và kết quả kiểm tra local: storage/app/qa/gemini-blue-whale-published.jpg và gemini-live-result.json (không đưa lên Git).
