# AbyssalMind — Ocean Knowledge Quiz

Ứng dụng web học kiến thức đại dương theo mô hình **đọc tư liệu → trả lời trắc nghiệm tốc độ → xem kết quả → ôn lại câu sai → leo bảng xếp hạng**. Nội dung câu hỏi có thể biên soạn thủ công hoặc tạo nháp tự động từ bài Wikipedia bằng Gemini AI, sau đó được quản trị viên kiểm duyệt trước khi xuất bản.

- Người học: đăng ký/đăng nhập, làm bài, xem lịch sử, ôn câu sai, bảng xếp hạng.
- Quản trị: quản lý bài đọc, bộ câu hỏi, tìm/nhập bài Wikipedia, sinh 5 câu hỏi nháp bằng Gemini, kiểm duyệt và xuất bản.

> Hướng dẫn thao tác từng màn hình: [`docs/huong-dan-su-dung.md`](docs/huong-dan-su-dung.md).

---

## 1. Công nghệ

| Thành phần | Phiên bản / ghi chú |
| --- | --- |
| PHP | 8.2+ |
| Laravel | 12 |
| Cơ sở dữ liệu | MySQL/MariaDB (mặc định) hoặc SQLite |
| Giao diện | Blade + Tailwind CSS 4 + Vite 7 |
| AI | Google Gemini API (tùy chọn, chỉ dùng ở khu vực quản trị) |
| Nguồn bài đọc | Wikipedia API tiếng Việt (không cần khóa) |

## 2. Yêu cầu hệ thống

- PHP **8.2+** với các extension thường dùng của Laravel (`mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`).
- Composer 2.
- Node.js **20.19+** hoặc **22.12+** (do Vite 7) và npm.
- MySQL 8 / MariaDB 10.4+ (nếu không dùng SQLite).

Kiểm tra nhanh:

```bash
php -v
composer -V
node -v
npm -v
```

## 3. Cài đặt

### 3.1. Lấy mã nguồn và cài dependency

```bash
git clone <URL-repo> oceanquiz1
cd oceanquiz1
composer install
npm install
```

### 3.2. Tạo file môi trường

```bash
cp .env.example .env
php artisan key:generate
```

### 3.3. Cấu hình cơ sở dữ liệu

**Cách A — MySQL/MariaDB (mặc định):**

```sql
CREATE DATABASE ocean_quiz_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Trong `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ocean_quiz_db
DB_USERNAME=root
DB_PASSWORD=
```

**Cách B — SQLite (nhanh, không cần cài MySQL):**

```bash
touch database/database.sqlite
```

Trong `.env`, đổi và bỏ các dòng MySQL:

```dotenv
DB_CONNECTION=sqlite
# DB_HOST=...
# DB_PORT=...
# DB_DATABASE=...   # để trống để Laravel dùng database/database.sqlite
# DB_USERNAME=...
# DB_PASSWORD=...
```

### 3.4. Tạo bảng và dữ liệu mẫu

```bash
php artisan migrate --seed
```

Lệnh này tạo 3 tài khoản mẫu và 2 bộ đề hoàn chỉnh (Rãnh Mariana, Phát quang sinh học), mỗi bộ 5 câu.

> ⚠️ `DatabaseSeeder` dùng `create()` cho bộ đề, nên **chạy seed lặp lại sẽ nhân đôi bộ đề**. Khi muốn làm lại từ đầu, dùng `php artisan migrate:fresh --seed` (xóa toàn bộ dữ liệu).

### 3.5. Build giao diện và chạy server

```bash
npm run build
php artisan serve
```

Mở http://127.0.0.1:8000.

Khi đang phát triển, chạy song song Vite để tự nạp lại giao diện:

```bash
# cửa sổ 1
php artisan serve

# cửa sổ 2
npm run dev
```

Hoặc dùng lệnh gộp có sẵn (server + queue + log + Vite):

```bash
composer dev
```

## 4. Tài khoản mẫu (sau khi seed)

| Vai trò | Email | Mật khẩu | Quyền |
| --- | --- | --- | --- |
| Quản trị | `admin@ocean.io` | `password123` | Vào `/admin`, quản lý bài đọc & bộ đề |
| Người học | `explorer@ocean.io` | `password123` | Làm bài, xem lịch sử, xếp hạng |
| Người học | `cousteau@ocean.io` | `password123` | Làm bài, xem lịch sử, xếp hạng |

Đổi mật khẩu/email mẫu trước khi triển khai thật. Có thể cấp quyền quản trị cho một tài khoản đã đăng ký:

```bash
php artisan tinker
>>> App\Models\User::where('email', 'ban@example.com')->update(['role' => 'admin']);
```

## 5. Biến môi trường quan trọng

| Biến | Bắt buộc | Ý nghĩa |
| --- | --- | --- |
| `APP_KEY` | Có | Sinh bằng `php artisan key:generate` |
| `APP_URL` | Nên đặt | URL gốc của ứng dụng |
| `DB_CONNECTION`, `DB_DATABASE`, ... | Có | Kết nối cơ sở dữ liệu |
| `GEMINI_API_KEY` | Không | Chỉ cần cho tính năng sinh câu hỏi AI; lấy miễn phí tại Google AI Studio |
| `GEMINI_MODEL` | Không | Model Gemini sử dụng, mặc định `gemini-3.5-flash-lite` |
| `WIKIPEDIA_USER_AGENT` | Không | User-Agent gửi tới Wikipedia API, nên ghi rõ tên dự án/liên hệ |

Sau khi sửa `.env`, chạy:

```bash
php artisan config:clear
```

Không commit `.env`, khóa API, file SQL dump hay file `.sqlite` lên Git (repo đã có `.gitignore` cho các file này).

## 6. Sử dụng nhanh

### Người học

1. Đăng ký tài khoản mới hoặc đăng nhập.
2. Vào **Bảng điều khiển** → **Làm bài thi**.
3. **Bước 1:** đọc tư liệu khoa học trong tối đa 3 phút (có thể bắt đầu sớm).
4. **Bước 2:** trả lời 5 câu, mỗi câu 20 giây; trả lời đúng càng nhanh điểm càng cao.
5. Xem điểm, số câu đúng và xếp hạng; vào **Ôn tập câu sai** để xem lại các lỗi trong 5 lượt gần nhất.

### Quản trị

1. Đăng nhập bằng tài khoản `role = admin`, mở **Trung Tâm AI (Admin)** trên thanh điều hướng hoặc `/admin`.
2. Quy trình biên soạn: **Tìm Wikipedia → Lưu bài → Tạo 5 câu nháp (Gemini hoặc thủ công) → Kiểm duyệt/sửa → Xuất bản**.
3. Chỉ bộ đã xuất bản mới được đưa vào trò chơi. Bộ đã xuất bản hoặc đã có lượt làm sẽ bị khóa nội dung để bảo toàn lịch sử điểm.
4. Theo dõi các lần gọi Gemini và lỗi tại trang **Gemini & nhật ký**.

Chi tiết từng bước, quy tắc tính điểm, giới hạn và xử lý sự cố: [`docs/huong-dan-su-dung.md`](docs/huong-dan-su-dung.md).

## 7. Quy tắc tính điểm

- Mỗi câu đúng: tối đa **1.000 điểm**, giảm dần theo thời gian phản xạ, tối thiểu **500 điểm** nếu vẫn kịp trả lời đúng.
- Công thức: `điểm = 1000 × max(1 − 0,5 × thời_gian / 20, 0,5)`.
- Trả lời sai hoặc hết giờ: 0 điểm cho câu đó.
- Điểm mỗi lượt được cộng dồn vào `total_score` của người dùng; `quizzes_completed` tăng 1 sau mỗi lượt nộp thành công.
- Bảng xếp hạng sắp theo tổng điểm, sau đó theo số bài đã hoàn thành (top 50).

## 8. Cấu trúc thư mục chính

```text
app/
  Http/Controllers/            # Trang chủ, auth, dashboard, quiz
  Http/Controllers/Admin/      # Wikipedia, bài đọc, AI, bộ câu hỏi
  Http/Middleware/             # AdminMiddleware (chặn người không phải admin)
  Models/                      # User, Article, QuizSet, Question, QuizAttempt...
  Services/                    # WikipediaService, GeminiService
database/
  migrations/                  # Bảng người dùng, quiz, article, log AI
  seeders/DatabaseSeeder.php   # 3 user + 2 bộ đề mẫu
resources/views/               # Blade: welcome, auth, dashboard, quiz, admin
routes/
  web.php                      # Route công khai + người học + admin
  admin-articles.php           # Quản lý bài đọc & Wikipedia
  admin-question-sets.php      # Gemini, bộ câu hỏi, xuất bản
docs/                          # Hướng dẫn sử dụng, mockup, ghi chú tích hợp
tests/                         # Feature/Unit test (SQLite in-memory)
```

## 9. Kiểm thử

```bash
php artisan test
```

Bộ test dùng SQLite `:memory:` theo `phpunit.xml`, không cần MySQL và không đụng dữ liệu thật. Nội dung bao gồm: phân quyền admin, luồng Wikipedia, sinh câu hỏi Gemini (hợp lệ/lỗi/thiếu khóa), lưu nháp, xuất bản, bảo vệ lịch sử và tương thích dữ liệu cũ.

Kiểm tra định dạng mã nguồn (tùy chọn):

```bash
./vendor/bin/pint
```

## 10. Lỗi thường gặp

| Hiện tượng | Cách xử lý |
| --- | --- |
| Trang trắng / lỗi 500 | Kiểm tra `storage/` và `bootstrap/cache/` có quyền ghi; xem `storage/logs/laravel.log` |
| Giao diện không có CSS | Chạy `npm install && npm run build` (hoặc `npm run dev`) |
| `419 Page Expired` | Thiếu `APP_KEY` hoặc session lỗi: `php artisan key:generate`, `php artisan config:clear` |
| Bị đẩy về dashboard khi vào `/admin` | Tài khoản chưa có `role = admin` (xem mục 4) |
| Trang Gemini báo chưa cấu hình khóa | Thêm `GEMINI_API_KEY` vào `.env`, chạy `php artisan config:clear`, khởi động lại server |
| Gemini báo model không tồn tại | Đổi `GEMINI_MODEL` sang model mà tài khoản của bạn được cấp |
| Wikipedia lỗi kết nối | Kiểm tra mạng ra Internet và `WIKIPEDIA_USER_AGENT` |
| Seed chạy nhiều lần bị trùng bộ đề | Dùng `php artisan migrate:fresh --seed` để làm sạch (mất dữ liệu hiện có) |
| Đổi `.env` nhưng không có tác dụng | `php artisan config:clear` rồi khởi động lại server |

## 11. Tài liệu khác

- [`docs/huong-dan-su-dung.md`](docs/huong-dan-su-dung.md) — hướng dẫn sử dụng cho người học và quản trị viên.
- [`docs/questiongeneration.md`](docs/questiongeneration.md) — kiến trúc luồng Wikipedia → Gemini → bộ đề nháp.
- [`docs/kiem-tra-gemini-thuc.md`](docs/kiem-tra-gemini-thuc.md) — biên bản kiểm thử Gemini thực tế.
- [`docs/mockup/README.md`](docs/mockup/README.md) — bộ mockup giao diện và nguồn ảnh.

## 12. Đóng góp

1. Tạo nhánh mới từ `main`, đặt tên ngắn gọn theo tính năng.
2. Chạy `php artisan test` (và `npm run build` nếu sửa giao diện) trước khi mở pull request.
3. Không đưa `.env`, khóa API, file SQL dump hoặc cơ sở dữ liệu SQLite lên Git.
4. Mô tả rõ thay đổi về dữ liệu/migration trong pull request.

## Giấy phép

Mã nguồn dựa trên Laravel skeleton, phát hành theo giấy phép [MIT](https://opensource.org/licenses/MIT).
