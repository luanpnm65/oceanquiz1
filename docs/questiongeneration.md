# Tích hợp questiongeneration vào oceanquiz1l

## Luồng sử dụng

1. Đăng nhập admin, vào Trung tâm AI.
2. Chọn **Tìm Wikipedia**, nhập từ khóa để xem tối đa 10 bài liên quan.
3. Chọn bài, xem trước rồi lưu vào **Bài đã lưu**. Lưu lại cùng mã Wikipedia không tạo bản trùng và không ghi đè nội dung đã biên tập.
4. Chọn bài đã lưu ở **Gemini & nhật ký**, nhập tên bộ và tầng sinh thái. Gemini chỉ tạo đúng 5 câu nháp hợp lệ; không tự xuất bản và không dùng câu hỏi giả khi API lỗi.
5. Mở bộ nháp, kiểm chứng bài đọc, sửa câu hỏi/lựa chọn/đáp án/giải thích, lưu rồi xuất bản. Có thể tạo bộ thủ công từ bài đã lưu.
6. Chỉ bộ đã xuất bản được bốc ngẫu nhiên trong trò chơi. Điểm tốc độ, thời gian đọc, lịch sử và bảng xếp hạng cũ được giữ lại.

## Cấu trúc file

- `config/services.php`, `.env.example`: cấu hình Wikipedia, model và khóa Gemini.
- `app/Services/WikipediaService.php`, `GeminiService.php`: sử dụng service từ bộ questiongeneration, gọi API có timeout, kiểm tra dữ liệu và che khóa trong lỗi.
- `app/Http/Controllers/Admin/`: WikipediaController, ArticleController, AiGenerationController, QuestionSetController phụ trách từng bước riêng.
- `routes/admin-articles.php`, `routes/admin-question-sets.php`: route quản trị mới; `routes/web.php` nạp hai file này.
- `resources/views/admin/`: trang Wikipedia, bài viết, AI/nhật ký, bộ nháp và các thành phần dùng chung. Tất cả kế thừa `admin/layout.blade.php` (sidebar Trung tâm AI + vùng nội dung), dùng `public/css/admin-app.css` với cùng biến màu/font của `public/css/ocean-app.css` để đồng bộ giao diện với trang web; các lớp gốc như `form-control`, `btn`, `data-table` vẫn được tái sử dụng.
- `app/Models/Article.php`, `AiGenerationLog.php`: bài đã lưu và nhật ký AI. QuizSet bổ sung liên kết Article và nguồn tạo.
- `database/migrations/2026_11_03_000001_add_question_generation.php`: migration bổ sung, không chép đè migration gốc.

Không sửa CSS hay phông chữ của trang người học: giao diện quản trị chỉ bổ sung `public/css/admin-app.css`. `layouts/app.blade.php` chỉ thêm `@stack('styles')` để nạp stylesheet quản trị. Không mang CSS hoặc layout oceanquiz1 sang dự án này.

## Dữ liệu và tương thích

Giữ các bảng `quiz_sets`, `questions` (đáp án JSON), `quiz_attempts`, `quiz_attempt_answers` và users. Thêm `articles`, `ai_generation_logs`; thêm article_id/source vào quiz_sets. Trạng thái nháp/xuất bản dùng trường is_active hiện có.

Migration tạo bản bài đọc riêng cho mỗi bộ có sẵn, nối article_id, không sửa điểm, câu hỏi, lịch sử hoặc trạng thái xuất bản. Seeder mới cũng tạo Article cho hai bộ mẫu. Bài đọc trong QuizSet là bản chụp lúc tạo bộ; không âm thầm thay bài đọc của bộ khi sửa thư viện.

Bộ đang xuất bản hoặc đã có lượt làm không được sửa nội dung; chỉ xóa bộ nháp chưa có lượt làm. Bài đã liên kết bộ xuất bản/lịch sử được khóa. Gỡ xuất bản vẫn giữ lịch sử. Có khóa hàng và transaction khi lưu/xuất bản.

Khác bộ mẫu: dùng QuizSet và options JSON để không phá cách chơi của oceanquiz1l, không thêm bảng question_options/attempts riêng của oceanquiz1. Không giữ các endpoint AJAX cũ fetch-wikipedia/generate-gemini/quiz-sets.store; giao diện quản trị đã chuyển hoàn toàn sang các route mới.

## Chạy trên cơ sở dữ liệu đã có

Cài dependency: `composer install`.
Giữ cấu hình DB và APP_KEY đang dùng trong .env. Chạy `php artisan migrate` để bổ sung cấu trúc. Không cần chạy lại seeder trên dữ liệu đã có.

Thiết lập `GEMINI_API_KEY` trong .env và chọn `GEMINI_MODEL` phù hợp tài khoản (mặc định theo bộ mẫu: gemini-3.5-flash-lite). Chạy `php artisan config:clear`, khởi động lại server sau khi đổi cấu hình. Không đưa .env lên Git.

## Môi trường xem thử và kiểm thử đã dùng

Bản clone này ban đầu chưa có vendor/.env. Đã cài dependency từ composer.lock; tạo .env local với **SQLite riêng database/preview.sqlite**, cổng **8002**, seed tài khoản mẫu. Không kết nối hoặc thay đổi MySQL ocean_quiz_db hiện có.

- Xem thử: http://127.0.0.1:8002/admin
- Admin mẫu: admin@ocean.io / password123.
- Khi chạy lại: `php artisan serve --host=127.0.0.1 --port=8002`.
- Khi tích hợp ban đầu, bản xem thử chưa cấu hình khóa Gemini. Sau đó đã dùng khóa từ oceanquiz1 theo yêu cầu rõ ràng của người dùng; khóa chỉ lưu trong .env bị Git bỏ qua.
- Đã tìm Wikipedia thực trên giao diện, chọn và lưu bài Cá voi xanh, chuyển sang Gemini với đúng bài đã chọn.
- `php artisan test`: **9 tests, 69 assertions**, SQLite :memory: tách biệt. Bao gồm quyền admin, tìm/xem/lưu Wikipedia, chống trùng, Gemini hợp lệ/sai dữ liệu/lỗi API/thiếu khóa, lưu nháp, kiểm duyệt/xuất bản, bảo vệ lịch sử, chuyển dữ liệu cũ và seeder/cách chơi.
- Kiểm thử tự động dùng Http::fake. Kiểm tra Gemini thực đã hoàn tất sau đó: xem `kiem-tra-gemini-thuc.md`.
- `php artisan view:cache`, route:list và kiểm tra diff: đạt.
- Các file CSS và layout gốc không có diff.

Chỉ thay đổi ở máy local, chưa commit hoặc push theo yêu cầu.
