# Tổng quan dự án

**Project Manager** là một ứng dụng web mã nguồn mở giúp nhóm nhỏ quản lý dự án và công việc. Ứng dụng được xây dựng theo mô hình MVC với PHP thuần và MySQL, dễ dàng triển khai trên các môi trường shared hosting. Phần giao diện sử dụng Bootstrap và CSS thuần để đảm bảo tính nhẹ nhàng và linh hoạt, đồng thời hỗ trợ tùy chỉnh dễ dàng.

Mục tiêu của dự án là cung cấp một công cụ quản lý công việc đơn giản nhưng đủ mạnh cho nhóm làm việc từ 3–10 người, cho phép theo dõi tiến độ, giao nhiệm vụ, đính kèm tài liệu và trao đổi trực tiếp ngay trên từng công việc.

## Các thành phần chính

* **Projects** – quản lý thông tin dự án, bao gồm tên, mô tả, thời gian bắt đầu/kết thúc. Người dùng cần có quyền để truy cập dự án.
* **Tasks** – mô tả công việc thuộc dự án, có hỗ trợ subtask, phân công người thực hiện, đặt ngày bắt đầu và hạn chót, gắn thẻ (tags) và mức độ ưu tiên. Giao diện Kanban giúp trực quan hóa luồng công việc.
* **Notes** – ghi chú linh hoạt với hỗ trợ Markdown, có thể tồn tại độc lập hoặc gắn với dự án/công việc. Notes cho phép liên kết nhiều công việc một lúc và hỗ trợ checklist nội dung.
* **Checklist Items** – các mục hành động nhỏ trong một công việc, cho phép đánh dấu hoàn thành riêng biệt.
* **Task Links** – lưu trữ liên kết tới tài liệu ngoài như Google Docs, Sheets hoặc Drive; hiển thị trực quan ngay trong giao diện công việc.
* **Users & Roles** – hai vai trò mặc định: *admin* và *member*. Quyền được lưu trong bảng `settings` theo định dạng JSON, dễ dàng mở rộng.
* **Logs** – lưu lại thao tác quan trọng trong hệ thống để theo dõi và kiểm tra.

## Bản quyền và phân phối

Dự án này được phát hành theo giấy phép MIT. Bạn được tự do sao chép, sử dụng và sửa đổi mã nguồn cho mục đích cá nhân hoặc thương mại. Khi phân phối lại, vui lòng giữ nguyên thông tin giấy phép.