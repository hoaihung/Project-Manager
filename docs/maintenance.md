# Hướng dẫn bảo trì và mở rộng

Tài liệu này cung cấp một số lưu ý khi bảo trì hoặc mở rộng hệ thống Project Manager.

## 1. Sao lưu dữ liệu

Thường xuyên sao lưu database (dump) để phòng trường hợp mất dữ liệu. Bạn có thể sử dụng công cụ `mysqldump`:

```bash
mysqldump -u username -p project_manager > backup.sql
```

Ngoài ra, sao lưu thư mục `public/assets/uploads` vì đây là nơi lưu file đính kèm.

## 2. Nâng cấp phiên bản

Khi nâng cấp lên phiên bản mới, hãy kiểm tra xem có file SQL update nào đi kèm (`sql/update_x.y.sql`) và chạy trước khi đẩy code mới. Luôn thực hiện nâng cấp trên môi trường test trước khi áp dụng vào môi trường thật.

## 3. Thêm tính năng mới

* **Thêm bảng/thuộc tính mới:** tạo file SQL mới trong `sql/` đặt tên `update_x.y.sql` mô tả thay đổi và hướng dẫn người dùng chạy.  
* **Thêm controller/model/view:** giữ nguyên quy ước đặt tên (Controller nối tiếp chữ `Controller.php`, Model nằm trong `app/Model`, View trong `app/View/{controller}/{action}.php`). Sử dụng các helper có sẵn (`__`, `e`, `user_can`) để tái sử dụng logic.
* **Thêm quyền mới:** cập nhật `sql/patch_permissions.sql`, bổ sung khóa mới vào JSON mặc định, cập nhật hàm `user_can` để xử lý, và viết doc hướng dẫn.

## 4. Bảo mật

* Luôn dùng prepared statements (đã được thực hiện trong `Model.php`).  
* Escape mọi dữ liệu đầu ra bằng helper `e()` để tránh XSS.  
* Đặt session cookie là `httponly` và `secure` khi triển khai trên HTTPS (có thể cấu hình trong php.ini).  
* Triển khai CSRF token cho các form quan trọng nếu cần nâng cao bảo mật.

## 5. Tối ưu hiệu năng

* Chỉ tải thư viện JS/CSS cần thiết cho trang hiện tại (đã có logic trong `layouts/header.php`).  
* Sử dụng paginations hoặc lazy load khi danh sách công việc hoặc ghi chú quá dài.  
* Tối ưu truy vấn trong model, thêm chỉ mục (index) cho các cột thường tìm kiếm nếu database lớn.

## 6. Kiểm thử

Khuyến khích viết unit test cho các hàm quan trọng (ví dụ `Task::getPriorityCounts`, `NoteController::canViewNote`). Bạn có thể sử dụng PHPUnit. Đảm bảo chạy test sau mỗi lần thay đổi để tránh regressions.

## 7. Hỗ trợ và đóng góp

Dự án được phát hành mã nguồn mở. Nếu bạn phát hiện lỗi hoặc có ý tưởng cải tiến, hãy tạo issue hoặc pull request trên kho chứa (repository). Mọi đóng góp đều được hoan nghênh!