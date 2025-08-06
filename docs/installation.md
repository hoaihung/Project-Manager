# Hướng dẫn cài đặt

Tài liệu này hướng dẫn cách cài đặt và khởi chạy ứng dụng Project Manager trên môi trường local. Nếu bạn đã cài đặt phiên bản cũ, vui lòng xem thêm phần nâng cấp trong `update_3.7.sql`.

## Yêu cầu hệ thống

* PHP 7.4 hoặc cao hơn với các extension `pdo_mysql`, `mbstring`, `json`.
* MySQL 5.7 hoặc MariaDB tương đương.
* Composer (tùy chọn, dùng để quản lý thư viện nếu bạn muốn bổ sung).

## Các bước cài đặt

1. **Tải mã nguồn**: giải nén gói `project_mvc_3.7.tar.gz` vào thư mục bạn muốn triển khai.

2. **Tạo cơ sở dữ liệu:** đăng nhập MySQL và tạo database mới, ví dụ:

   ```sql
   CREATE DATABASE project_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   ```

3. **Import cấu trúc và dữ liệu:** chạy lệnh sau để tạo bảng và dữ liệu mẫu:

   ```sql
   SOURCE /đường/dẫn/tới/project_mvc/sql/schema.sql;
   ```

   Nếu nâng cấp từ phiên bản cũ (v3.5), hãy chạy tiếp:

   ```sql
   SOURCE /đường/dẫn/tới/project_mvc/sql/update_3.7.sql;
   ```

4. **Cấu hình kết nối DB:** mở file `config/config.php` và chỉnh sửa các tham số trong mảng `db` cho phù hợp với database của bạn:

   ```php
   'db' => [
       'host' => 'localhost',
       'name' => 'project_manager',
       'user' => 'root',
       'pass' => ''
   ],
   ```

5. **Khởi chạy ứng dụng:** sử dụng PHP built‑in server cho môi trường phát triển:

   ```bash
   php -S localhost:8000 -t project_mvc/public
   ```

   Truy cập [http://localhost:8000](http://localhost:8000) và đăng nhập bằng tài khoản admin mặc định (tên người dùng `admin`, mật khẩu `admin123`).

6. **Thiết lập người dùng và quyền:** sử dụng giao diện admin để tạo người dùng mới, phân vai trò và thiết lập quyền bằng cách chỉnh sửa JSON trong mục “Quản lý người dùng”. Bạn có thể bật quyền `view_any_note` để cho phép thành viên xem tất cả ghi chú.

7. **Thiết lập biến môi trường (tùy chọn):** nếu triển khai lên môi trường sản xuất, hãy cấu hình các tham số như URL, bảo mật session, v.v. trong `config/config.php`. Ngoài ra, bạn nên thiết lập server web (Apache/Nginx) để trỏ DocumentRoot tới `project_mvc/public`.

## Nâng cấp từ phiên bản cũ

Phiên bản 3.7 bổ sung các bảng và quyền mới. Nếu bạn đã sử dụng phiên bản cũ (3.5), cần thực hiện thêm:

1. Sao lưu database hiện tại (dump dữ liệu). 
2. Chạy `sql/update_3.7.sql` để tạo bảng mới và cập nhật JSON quyền. 
3. Triển khai mã nguồn mới (ghi đè lên phiên bản cũ) hoặc merge vào dự án hiện tại. 
4. Kiểm tra lại các tùy chỉnh trước đó (ví dụ quyền người dùng) và cập nhật nếu cần.

Sau khi nâng cấp, hãy thử tạo ghi chú mới, thêm checklist và liên kết để chắc chắn mọi thứ hoạt động như mong đợi.