# Hướng dẫn sử dụng AbyssalMind — Ocean Knowledge Quiz

Tài liệu này hướng dẫn thao tác trên giao diện dành cho **người học** và **quản trị viên**, kèm quy tắc tính điểm, giới hạn dữ liệu và cách xử lý sự cố thường gặp.

Phần cài đặt, cấu hình `.env` và chạy dự án nằm trong [`../README.md`](../README.md).

---

## 1. Vai trò và đường dẫn chính

| Vai trò | Cách nhận biết | Khu vực sử dụng |
| --- | --- | --- |
| Khách | Chưa đăng nhập | Trang chủ, bảng xếp hạng, đăng nhập/đăng ký |
| Người học (`role = user`) | Đăng ký mới hoặc dùng tài khoản mẫu | Bảng điều khiển, làm bài, ôn câu sai |
| Quản trị viên (`role = admin`) | Được seed sẵn hoặc được cấp quyền | Toàn bộ khu vực `/admin` |

Bản đồ nhanh:

| Trang | Đường dẫn |
| --- | --- |
| Trang chủ | `/` |
| Bảng xếp hạng | `/leaderboard` |
| Đăng nhập / Đăng ký | `/login` · `/register` |
| Bảng điều khiển | `/dashboard` |
| Làm bài | `/quiz/play` |
| Ôn tập câu sai | `/quiz/mistakes` |
| Quản trị bộ câu hỏi | `/admin` |
| Tìm Wikipedia | `/admin/wikipedia` |
| Bài đã lưu | `/admin/articles` |
| Gemini & nhật ký | `/admin/ai` |
| Tạo bộ thủ công | `/admin/question-sets/create` |

---

## 2. Dành cho người học

### 2.1. Đăng ký tài khoản

1. Mở `/register`.
2. Nhập **tên hiển thị**, **email** và **mật khẩu** (tối thiểu 6 ký tự, nhập lại để xác nhận).
3. Bấm đăng ký. Hệ thống đăng nhập ngay và chuyển tới Bảng điều khiển.

Lưu ý:

- Mỗi email chỉ dùng cho một tài khoản.
- Ứng dụng **chưa có chức năng quên mật khẩu**. Người dùng cần liên hệ quản trị viên; quản trị viên có thể đặt lại mật khẩu bằng dòng lệnh ở mục 6.3.

### 2.2. Đăng nhập và đăng xuất

- Đăng nhập tại `/login` bằng email + mật khẩu; có thể chọn ghi nhớ đăng nhập.
- Nút đăng xuất nằm trên thanh điều hướng sau khi đã đăng nhập.
- Tài khoản mẫu sau khi chạy seeder:

| Vai trò | Email | Mật khẩu |
| --- | --- | --- |
| Quản trị | `admin@ocean.io` | `password123` |
| Người học | `explorer@ocean.io` | `password123` |
| Người học | `cousteau@ocean.io` | `password123` |

### 2.3. Trang chủ và bảng xếp hạng

- **Trang chủ** giới thiệu hành trình 5 tầng đại dương và lối vào đăng nhập/đăng ký.
- **Bảng xếp hạng** hiển thị top 50 người chơi theo **tổng điểm tích lũy**, sau đó theo **số bài đã hoàn thành**.
- Khách chưa đăng nhập vẫn xem được bảng xếp hạng.

### 2.4. Bảng điều khiển

Sau khi đăng nhập, `/dashboard` hiển thị:

- Tổng điểm tích lũy và hạng toàn cầu hiện tại.
- Số câu sai trong 5 lượt làm bài gần nhất.
- Hai lối tắt: **Bắt đầu làm bài quiz** và **Ôn tập câu sai**.
- Lịch sử 5 lượt gần nhất; bấm vào từng lượt để xem chi tiết đúng/sai của từng câu.

### 2.5. Một lượt làm bài

Hệ thống bốc **ngẫu nhiên một bộ đề đang xuất bản**. Nếu chưa có bộ nào được xuất bản, bạn sẽ được đưa về Bảng điều khiển kèm thông báo.

**Bước 1 — Đọc tư liệu (tối đa 3 phút)**

- Trang hiển thị bài đọc khoa học của bộ đề.
- Đồng hồ đếm ngược 180 giây; hết giờ tự chuyển sang phần câu hỏi.
- Có thể bấm nút bắt đầu sớm để vào phần trắc nghiệm ngay.

**Bước 2 — Trắc nghiệm tốc độ**

- Mỗi bộ mẫu có **5 câu**, mỗi câu **20 giây**.
- Chọn một trong bốn đáp án A–D.
- Trả lời xong, hệ thống khóa câu, tô đáp án đúng, hiện phản hồi đúng/sai kèm điểm và tự chuyển câu sau khoảng 2,2 giây.
- Hết 20 giây mà chưa chọn: tính là hết giờ, 0 điểm cho câu đó và tự chuyển câu.
- Không thể quay lại câu trước trong cùng lượt.

**Kết thúc**

Hệ thống tự nộp bài khi hết câu cuối, hiển thị tổng điểm và số câu đúng, đồng thời cộng điểm vào tài khoản. Nếu không lưu được kết quả do lỗi mạng/máy chủ, trang tổng kết vẫn hiện nhưng lượt làm đó không được ghi vào lịch sử và điểm số có thể hiển thị 0 — hãy kiểm tra kết nối rồi làm lại.

### 2.6. Ôn tập câu sai

- Mở `/quiz/mistakes` hoặc bấm **Ôn tập câu sai** ở Bảng điều khiển.
- Hệ thống gom các câu trả lời **sai hoặc hết giờ** trong **5 lượt làm bài gần nhất**, mỗi câu chỉ hiện một lần.
- Mỗi câu hiển thị nội dung, bộ đề, đáp án đúng và giải thích để đọc lại.
- Chưa từng làm bài hoặc không có câu sai thì danh sách trống.

---

## 3. Dành cho quản trị viên

### 3.1. Điều kiện truy cập

Tài khoản phải có `role = admin`. Người dùng thường truy cập `/admin` sẽ bị chuyển về Bảng điều khiển kèm thông báo từ chối.

Cấp quyền cho một tài khoản đã tồn tại:

```bash
php artisan tinker
>>> App\Models\User::where('email', 'ban@example.com')->update(['role' => 'admin']);
```

### 3.2. Các trang quản trị

| Trang | Chức năng |
| --- | --- |
| **Bộ câu hỏi** (`/admin`) | Danh sách toàn bộ bộ đề: trạng thái nháp/đã xuất bản, số câu, số lượt làm, lối vào biên tập |
| **Tìm Wikipedia** | Tìm bài tiếng Việt, xem trước, lưu vào thư viện bài đọc |
| **Bài đã lưu** | Danh sách bài đọc, số bộ đề sử dụng, tạo/sửa/xóa bài thủ công |
| **Gemini & nhật ký** | Sinh 5 câu nháp từ một bài đã lưu; xem lịch sử gọi AI, lỗi và phản hồi thô |
| **Tạo bộ thủ công** | Tạo bộ nháp rỗng từ một bài đã lưu để tự nhập 5 câu |

### 3.3. Quy trình biên soạn chuẩn

```text
Tìm Wikipedia → Lưu bài đọc → Tạo 5 câu nháp (Gemini hoặc thủ công)
→ Kiểm chứng nội dung, sửa câu hỏi/đáp án/giải thích → Lưu
→ Kiểm tra & xuất bản → Người học có thể gặp bộ đề
```

Chỉ bộ **đã xuất bản** mới được bốc ngẫu nhiên trong trò chơi. Bộ nháp không hiển thị cho người học.

### 3.4. Tìm và lưu bài Wikipedia

1. Vào **Tìm Wikipedia**.
2. Nhập từ khóa tiếng Việt (tối đa 100 ký tự) và bấm **Tìm bài liên quan**.
3. Hệ thống trả tối đa 10 kết quả kèm đoạn trích.
4. Bấm **Xem trước bài này** để đọc nội dung và kiểm tra nguồn/giấy phép.
5. Bấm **Lưu bài vào thư viện**.

Quy tắc:

- Bài Wikipedia được nhận diện theo mã trang (`page_id`), nên **lưu lại cùng một bài không tạo bản trùng** và **không ghi đè** nội dung đã biên tập.
- Bài lưu xong được chuyển tới trang biên tập bài đọc.
- Nếu Wikipedia lỗi mạng hoặc dữ liệu, trang hiện thông báo và gợi ý nhập bài thủ công.
- Giới hạn lưu bài: **10 request/phút**.

### 3.5. Nhập bài đọc thủ công

1. Vào **Bài đã lưu** → **Tạo bài**.
2. Nhập tiêu đề, nội dung (tối thiểu 80 ký tự, tối đa 100.000), nguồn, URL nguồn (http/https) và giấy phép nếu có.
3. Lưu bài.

Bài đã được dùng bởi bộ đề đã xuất bản hoặc đã có lượt làm sẽ **bị khóa sửa**; muốn thay nội dung, hãy tạo bài mới. Bài đang được bộ đề hoặc nhật ký AI tham chiếu cũng **không xóa được**.

### 3.6. Tạo bộ đề thủ công

1. Vào **Tạo bộ thủ công**.
2. Chọn bài kiến thức, nhập tên bộ, chọn tầng sinh thái.
3. Bấm **Tạo bộ nháp** — bộ mới ở trạng thái nháp, chưa có câu hỏi.
4. Điền đủ **5 câu**, mỗi câu **4 lựa chọn khác nhau**, chọn **một đáp án đúng** và nhập **giải thích**.
5. Bấm **Lưu 5 câu nháp**, sau đó **Kiểm tra & xuất bản**.

Tầng sinh thái chọn trong danh sách cố định:

- Tầng Ánh Sáng (0 – 200 m)
- Tầng Chạng Vạng (200 – 1.000 m)
- Tầng Nửa Đêm (1.000 – 4.000 m)
- Tầng Đáy Thẳm (4.000 – 6.000 m)
- Tầng Hố Thẳm (6.000 – 11.000 m)

### 3.7. Sinh câu hỏi bằng Gemini

1. Cấu hình `GEMINI_API_KEY` (và tùy chọn `GEMINI_MODEL`) trong `.env`, sau đó chạy `php artisan config:clear`.
2. Vào **Gemini & nhật ký**; trang hiển thị model và trạng thái **Đã cấu hình khóa** / **Chưa cấu hình**.
3. Chọn **bài đã lưu**, nhập **tên bộ câu hỏi**, chọn **tầng sinh thái**.
4. Bấm **Sinh 5 câu nháp bằng Gemini** và chờ tối đa khoảng 90 giây.
5. Thành công: hệ thống tạo **đúng 5 câu nháp** (mỗi câu 4 lựa chọn, một đáp án đúng, có giải thích) và mở trang kiểm duyệt.
6. Thất bại: **không có bộ đề giả nào được tạo**; lỗi được ghi vào nhật ký và hiển thị trên form.

Lưu ý:

- AI chỉ dùng nội dung bài đọc được chọn, nhưng **kết quả vẫn phải được kiểm chứng thủ công** trước khi xuất bản.
- Nếu thiếu khóa, model không tồn tại, hết hạn mức (HTTP 429) hoặc dịch vụ lỗi, hãy sửa cấu hình rồi thử lại; vẫn có thể tạo bộ thủ công.
- Khóa API được che trong thông báo lỗi và nhật ký.
- Giới hạn gọi sinh câu hỏi: **5 request/phút**.
- Nhật ký hiển thị thời gian theo múi giờ Việt Nam, trạng thái, bộ nháp liên kết, lỗi và phản hồi thô (rút gọn).

### 3.8. Kiểm duyệt và xuất bản

Trang biên tập bộ đề cho phép:

- Xem lại bài đọc và nguồn.
- Sửa tên bộ, tầng sinh thái.
- Sửa 5 câu: nội dung, 4 lựa chọn, đáp án đúng, giải thích.
- Lưu nháp hoặc xuất bản/gỡ xuất bản.

Điều kiện xuất bản:

- Đúng **5 câu**.
- Mỗi câu có **4 lựa chọn khác nhau** (không trùng sau khi bỏ khoảng trắng/khác hoa thường).
- Đã chọn đáp án đúng (0–3).
- Các câu hỏi không trùng nội dung (được kiểm tra khi lưu nháp).

Nếu chưa đạt, hệ thống báo lỗi và giữ nguyên trạng thái nháp.

### 3.9. Khóa dữ liệu và lịch sử

| Tình huống | Được sửa nội dung? | Được xóa? | Ghi chú |
| --- | --- | --- | --- |
| Bộ nháp, chưa có lượt làm | Có | Có | Dùng để biên tập tự do |
| Bộ đã xuất bản | Không | Không | Phải **gỡ xuất bản** trước, nhưng vẫn không sửa được nếu đã có lượt làm |
| Bộ đã có lượt làm | Không | Không | Bảo toàn lịch sử điểm và câu trả lời |
| Bài đọc của bộ đã xuất bản/có lượt làm | Không | Không | Tạo bài mới nếu cần nội dung khác |

- **Gỡ xuất bản** chỉ đưa bộ về trạng thái nháp; lịch sử làm bài, điểm và bảng xếp hạng **không bị thay đổi**.
- Bộ đề lưu **bản chụp bài đọc** tại thời điểm tạo. Sửa thư viện bài đọc không làm thay đổi nội dung của bộ đã tạo.
- Nên kiểm tra kỹ trước khi xuất bản vì sau khi có người làm bài, nội dung sẽ bị khóa vĩnh viễn.

---

## 4. Quy tắc tính điểm và xếp hạng

Mỗi câu đúng nhận tối đa 1.000 điểm, giảm theo thời gian phản xạ:

```text
điểm = 1000 × max(1 − 0,5 × thời_gian_trả_lời / 20, 0,5)
```

| Thời gian trả lời đúng | Điểm xấp xỉ |
| --- | --- |
| 0 giây | 1.000 |
| 5 giây | 875 |
| 10 giây | 750 |
| 15 giây | 625 |
| 20 giây | 500 |

- Sai hoặc hết giờ: 0 điểm.
- Tổng điểm lượt làm = tổng điểm các câu đúng.
- Điểm được **cộng dồn** vào tổng điểm tài khoản; số bài hoàn thành tăng 1 sau mỗi lượt nộp thành công.
- Hạng toàn cầu = số người có tổng điểm cao hơn + 1.
- Bảng xếp hạng lấy top 50, ưu tiên tổng điểm rồi số bài hoàn thành.

---

## 5. Câu hỏi thường gặp

**Vì sao tôi không thấy bộ đề vừa tạo khi làm bài?**
Bộ đề phải ở trạng thái **đã xuất bản** và có đủ câu hỏi. Hệ thống bốc ngẫu nhiên nên có thể chưa gặp ngay.

**Vì sao không sửa được bộ đề?**
Bộ đã xuất bản hoặc đã có lượt làm sẽ khóa nội dung để bảo toàn lịch sử. Hãy tạo bộ mới nếu cần nội dung khác.

**Vì sao không xóa được bài đọc?**
Bài đang được bộ đề hoặc nhật ký AI sử dụng. Gỡ liên kết bằng cách xử lý các bộ đề liên quan trước, hoặc giữ nguyên bài.

**Tôi muốn làm lại từ đầu với dữ liệu mẫu?**
Chạy `php artisan migrate:fresh --seed`. Lệnh này **xóa toàn bộ dữ liệu hiện có**, chỉ dùng ở môi trường phát triển.

**Vì sao chạy seeder nhiều lần bị trùng bộ đề?**
Seeder tạo bộ đề bằng `create()`. Chỉ seed một lần trên cơ sở dữ liệu mới, hoặc dùng `migrate:fresh --seed` để làm sạch.

**Làm sao đổi mật khẩu khi không đăng nhập được?**
Chưa có chức năng quên mật khẩu. Quản trị viên đặt lại bằng lệnh ở mục 6.3.

---

## 6. Vận hành và bảo trì

### 6.1. Sau khi cập nhật mã nguồn

```bash
composer install
php artisan migrate
npm install
npm run build
php artisan config:clear && php artisan view:clear
```

Chỉ chạy `migrate:fresh` khi chấp nhận mất dữ liệu.

### 6.2. Sao lưu

- MySQL/MariaDB: `mysqldump -u root -p ocean_quiz_db > backup.sql`
- SQLite: dừng ứng dụng rồi sao chép `database/database.sqlite`.

Không commit file sao lưu, file `.sql` hay `.sqlite` lên Git.

### 6.3. Đặt lại mật khẩu người dùng

```bash
php artisan tinker
>>> App\Models\User::where('email', 'nguoi-dung@example.com')->update(['password' => Hash::make('mat-khau-moi')]);
```

### 6.4. Triển khai production (tóm tắt)

1. Đặt `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` đúng miền.
2. Trỏ web server vào thư mục `public/`.
3. Cấp quyền ghi cho `storage/` và `bootstrap/cache/`.
4. Chạy `composer install --no-dev --optimize-autoloader`, `npm install && npm run build`.
5. Chạy `php artisan migrate --force`, sau đó `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
6. Đổi toàn bộ tài khoản/mật khẩu mẫu và bảo vệ khóa Gemini.

Ứng dụng hiện không dùng hàng đợi cho nghiệp vụ chính; không bắt buộc chạy worker.

---

## 7. Xử lý sự cố nhanh

| Hiện tượng | Nguyên nhân thường gặp | Cách xử lý |
| --- | --- | --- |
| Vào `/admin` bị chuyển về dashboard | Tài khoản không phải admin | Cấp `role = admin` (mục 3.1) |
| Nút sinh Gemini bị mờ | Chưa có `GEMINI_API_KEY` hoặc chưa có bài đã lưu | Cấu hình khóa, lưu bài Wikipedia/manual trước |
| Gemini báo 429 | Hết hạn mức hoặc gọi quá nhanh | Chờ rồi thử lại; giới hạn 5 lần/phút |
| Gemini báo không tìm thấy model | `GEMINI_MODEL` không đúng quyền tài khoản | Đổi model trong `.env`, `config:clear` |
| Wikipedia không trả kết quả | Sai từ khóa hoặc lỗi mạng | Thử từ khóa khác; kiểm tra `WIKIPEDIA_USER_AGENT` |
| Lưu 5 câu báo lỗi | Thiếu câu, thiếu lựa chọn, lựa chọn trùng | Điền đủ 5 câu × 4 lựa chọn khác nhau, chọn đáp án đúng |
| Không xuất bản được | Bộ chưa đủ 5 câu hợp lệ | Lưu lại nội dung rồi xuất bản |
| Trang trắng/500 | Thiếu quyền ghi, lỗi cấu hình | Xem `storage/logs/laravel.log`, kiểm tra quyền thư mục |
| CSS không tải | Chưa build asset | `npm install && npm run build` hoặc `npm run dev` |
| Đổi `.env` không có tác dụng | Cấu hình bị cache | `php artisan config:clear`, khởi động lại server |

---

## 8. Checklist trước khi demo hoặc bàn giao

- [ ] `php artisan migrate --seed` chạy thành công, đăng nhập được cả 3 tài khoản mẫu.
- [ ] Làm thử một lượt: đọc tư liệu → 5 câu → điểm được lưu vào lịch sử và bảng xếp hạng.
- [ ] Ôn tập câu sai hiển thị đúng câu đã làm sai.
- [ ] `/admin` chỉ cho tài khoản admin truy cập.
- [ ] Tạo thử một bộ nháp, xuất bản, kiểm tra bộ đề xuất hiện trong vòng quay ngẫu nhiên.
- [ ] Kiểm tra trang Gemini: khóa đã cấu hình, sinh thử 5 câu, nhật ký ghi nhận thành công.
- [ ] Đã đổi mật khẩu tài khoản mẫu và không commit `.env`.
- [ ] `php artisan test` chạy qua toàn bộ.
