# Project Manager Application

Một ứng dụng web quản lý dự án và công việc dành cho các nhóm nhỏ. Ứng dụng được xây dựng theo mô hình MVC sử dụng PHP thuần, MySQL ở phía backend và giao diện hiện đại với HTML, CSS thuần và AlpineJS nhỏ gọn để bổ sung tính tương tác. Ứng dụng lấy cảm hứng từ UX của ClickUp, Trello nhưng ưu tiên cấu trúc nhẹ, dễ đọc và dễ mở rộng.

## Tính năng chính

* **Quản lý dự án:** tạo/sửa/xóa dự án, trạng thái, ngày bắt đầu và kết thúc.
* **Quản lý công việc:** tạo/sửa/xóa công việc, phân chia thành các cột Kanban (To Do, In Progress, Done), phân công người thực hiện, thiết lập ngày bắt đầu và hạn chót, hỗ trợ công việc con (subtask).
* **Kanban Board:** kéo thả công việc giữa các cột, sắp xếp thứ tự công việc. Sau khi kéo thả thứ tự sẽ được lưu tự động.
* **Calendar & List View:** hiển thị công việc dưới dạng danh sách hoặc lịch đơn giản (nhóm theo ngày hạn chót).
* **Quản lý người dùng:** chỉ admin được tạo người dùng mới, phân vai trò admin hoặc member, chỉnh sửa thông tin, đổi vai trò.
* **Đăng nhập/đăng xuất:** xác thực bằng username/password. Mặc định tài khoản `admin`/`admin123` được tạo sẵn sau khi import database.
* **Nhật ký (Audit Log):** lưu trữ các thao tác quan trọng (có thể mở rộng sau này).

### Tính năng mới trong phiên bản 3.7

Phiên bản 3.7 bổ sung một số tính năng giúp quản lý thông tin và tài liệu kèm theo công việc linh hoạt hơn:

* **Module ghi chú (Notes):** cho phép tạo các ghi chú độc lập (global), ghi chú gắn với dự án hoặc gắn với các công việc cụ thể. Ghi chú hỗ trợ định dạng Markdown (in đậm, nghiêng, liên kết, checklist) và có thể liên kết với nhiều công việc. Người dùng được gán vào một công việc cũng có quyền xem các ghi chú liên kết với công việc đó. Chỉ người tạo ghi chú và admin mới có quyền sửa/xóa ghi chú. Người dùng có thể được cấp quyền “xem mọi ghi chú” (`view_any_note`) để truy cập cả những ghi chú không thuộc dự án của mình.
* **Liên kết tài liệu:** mỗi công việc có thể lưu nhiều liên kết tới các tài liệu bên ngoài như Google Docs, Sheets… Liên kết được hiển thị dưới dạng biểu tượng + tên và mở trong tab mới khi nhấn.
* **Checklist cho công việc:** hỗ trợ tạo danh sách các bước nhỏ cần thực hiện trong mỗi công việc. Mỗi mục checklist có thể đánh dấu hoàn thành riêng và được sắp xếp.
* **Mức ưu tiên “urgent”:** bên cạnh các mức “low”, “medium”, “high”, công việc có thể gắn mức ưu tiên “urgent” với màu sắc và biểu tượng riêng trong dashboard.
* **Sidebar mới:** thanh bên hiển thị danh sách dự án của bạn, kèm theo các liên kết con (Board, List, Calendar, Notes). Ngoài ra có thêm liên kết “Notes” ở cấp cao để truy cập nhanh danh sách tất cả ghi chú bạn có quyền xem (bao gồm ghi chú toàn cục, ghi chú dự án và ghi chú gắn với công việc).
* **Tùy biến quyền xem ghi chú:** thông qua trường `view_any_note` trong bảng `settings`, bạn có thể cho phép một người dùng được xem tất cả ghi chú (kể cả các ghi chú không thuộc dự án mà họ tham gia). Mặc định quyền này tắt đối với member.

## Cấu trúc thư mục

```
project_mvc/
├── app/               # Mã nguồn ứng dụng
│   ├── Controller/    # Controllers
│   ├── Model/         # Models (tương tác DB)
│   ├── View/          # Views (giao diện người dùng)
│   ├── Core/          # Các lớp lõi: App, Controller, Model, Autoloader
│   └── helpers.php    # Các hàm trợ giúp chung (dịch, escape, redirect)
├── config/            # Cấu hình ứng dụng và localization
├── public/            # Thư mục public, index.php là entrypoint
│   ├── assets/
│   │   ├── css/       # file style.css
│   │   └── js/        # file app.js
│   └── index.php
├── sql/               # Các file SQL tạo database
│   └── schema.sql
├── README.md          # Tài liệu hướng dẫn
└── ...
```

## Cài đặt

1. **Tạo cơ sở dữ liệu**: tạo một database MySQL mới, ví dụ `project_manager`.

2. **Import dữ liệu mẫu:** chạy file `sql/schema.sql` để tạo các bảng và dữ liệu mẫu. Trong MySQL CLI:

   ```sql
   SOURCE /đường/dẫn/tới/project_mvc/sql/schema.sql;
   ```

   Tài khoản admin mặc định: `admin`/`admin123`. Hãy đổi mật khẩu ngay sau lần đăng nhập đầu tiên.

3. **Cấu hình kết nối DB:** mở file `config/config.php` và chỉnh sửa thông tin `db` (`host`, `name`, `user`, `pass`) phù hợp với môi trường của bạn.

4. **Cập nhật DB từ phiên bản trước:** nếu bạn đã cài đặt phiên bản cũ (v3.5), hãy chạy script `sql/update_3.7.sql` trước khi nâng cấp code. Script này tạo các bảng `notes`, `note_task`, `task_links`, `checklist_items` và bổ sung trường quyền `view_any_note` vào file settings. Trong MySQL CLI:

   ```sql
   SOURCE /đường/dẫn/tới/project_mvc/sql/update_3.7.sql;
   ```

   Sau khi chạy script, bạn có thể cấu hình quyền `view_any_note` thông qua bảng `settings` hoặc qua giao diện admin (phần chỉnh sửa quyền người dùng).

4. **Khởi chạy ứng dụng:** bạn có thể sử dụng PHP built‑in server trong môi trường phát triển:

   ```bash
   php -S localhost:8000 -t public
   ```

   Sau đó truy cập [http://localhost:8000](http://localhost:8000) trong trình duyệt.

5. **Đăng nhập:** sử dụng tài khoản admin để quản trị hệ thống. Admin có thể tạo người dùng mới và phân công vai trò.

## Mở rộng và tuỳ biến

* **Đa ngôn ngữ (i18n):** Các chuỗi giao diện được khai báo trong `config/localization.php`. Bạn có thể thêm các ngôn ngữ khác bằng cách bổ sung mảng ngôn ngữ mới (ví dụ `en`, `fr`, ...). Hàm `__(...)` sẽ tự động lấy chuỗi theo locale cấu hình.

* **Giao diện & CSS:** Toàn bộ giao diện sử dụng CSS thuần trong `public/assets/css/style.css`. Bạn có thể tùy chỉnh màu sắc hoặc layout bằng cách chỉnh các biến CSS tại đầu file. Nếu muốn dùng TailwindCSS/Bootstrap, bạn có thể thay thế bằng file CSS tương ứng (tải về và lưu nội bộ) rồi cập nhật liên kết trong header.

* **Plugin/Module:** Cấu trúc MVC tách biệt giúp bạn dễ dàng thêm controller/model/view mới. Bạn có thể tạo thư mục `app/Plugins` để chứa các module mở rộng và sử dụng cơ chế autoload trong `Autoloader.php` để tự động nạp.

* **Audit Log:** Model `Log` và controller `LogController` đã sẵn sàng, nhưng hiện chưa tự động ghi log cho mọi hành động. Bạn có thể gọi `$logModel->create([...])` trong các controller khác để lưu lại các thao tác quan trọng.

* **API/PWA:** Hiện ứng dụng chưa cung cấp API. Bạn có thể xây dựng thêm các controller/API trả về JSON để phục vụ ứng dụng di động hoặc PWA.

* **File upload:** Mẫu code có sẵn model `File` nhưng chưa thực hiện upload trong controller. Bạn có thể bổ sung input `file` vào form tạo/sửa công việc và xử lý upload trong `TaskController` bằng `$_FILES` rồi lưu bản ghi trong bảng `files`.

* **Thêm view nâng cao:** Nếu muốn tích hợp thư viện lịch (FullCalendar) hoặc drag‑and‑drop nâng cao (SortableJS), hãy tải các file JS/CSS tương ứng và lưu vào `public/assets/libs`, sau đó nhúng vào layout. Bạn cần cập nhật code trong `app.js` để sử dụng các thư viện đó.

## Lưu ý

* Ứng dụng này được tối ưu cho nhóm nhỏ và mục đích học tập/demonstration. Để đưa vào môi trường sản xuất, bạn cần thực hiện nhiều bước cứng hóa bảo mật (hash mật khẩu mạnh, phân quyền chi tiết, kiểm tra quyền truy cập URL, CSRF token, v.v.).
* Không cho phép người dùng tự do đăng ký – chỉ tài khoản admin tạo được người dùng.
* Không sử dụng CDN quốc tế: tất cả thư viện bạn bổ sung cần được tải và lưu tại chỗ (local).

Chúc bạn xây dựng và mở rộng ứng dụng thành công!