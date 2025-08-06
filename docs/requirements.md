# Yêu cầu chức năng

Tài liệu này mô tả các yêu cầu chức năng và phi chức năng của hệ thống Project Manager phiên bản 3.7.

## 1. Quản lý dự án

* Người dùng có thể tạo, chỉnh sửa và xóa dự án (nếu có quyền `create_project`/`edit_project`/`delete_project`).
* Dự án có các thuộc tính: tên, mô tả, ngày bắt đầu, ngày kết thúc.  
* Thành viên dự án được quản lý thông qua danh sách người dùng được phép truy cập (`access_projects` trong bảng `settings`).

## 2. Quản lý công việc (Task)

* Mỗi dự án chứa nhiều công việc, phân loại theo trạng thái (To Do, In Progress, Done) và mức ưu tiên (Low, Medium, High, Urgent).  
* Công việc có thể có công việc con (subtask) với quan hệ cha–con.  
* Công việc có thể gán cho nhiều người dùng (nhiều assignee).  
* Có thể thêm ngày bắt đầu và hạn chót.  
* Hỗ trợ mô tả công việc và bình luận của người dùng. Bình luận tự động chuyển URL thành liên kết.
* Có thể đính kèm nhiều file. File lưu trong `public/assets/uploads` và thông tin lưu trong bảng `files`.
* Hỗ trợ Checklist: mỗi công việc có một danh sách các mục công việc nhỏ có thể tick hoàn thành. Người dùng có thể thêm, sửa, xóa mục checklist khi chỉnh sửa công việc.
* Hỗ trợ Liên kết (Task Links): người dùng có thể thêm nhiều link bên ngoài (tên + URL) và hiển thị trong giao diện công việc. Các link được lưu trong bảng `task_links`.

## 3. Module ghi chú (Notes)

* Người dùng có thể tạo ghi chú độc lập (Global) không thuộc dự án nào, ghi chú thuộc một dự án cụ thể hoặc gắn với nhiều công việc.
* Ghi chú hỗ trợ định dạng Markdown cơ bản (in đậm, nghiêng, đường dẫn, checklist) và tự động chuyển URL thuần thành liên kết.
* Ghi chú có thể liên kết với nhiều công việc và hiển thị cùng công việc đó trong giao diện chỉnh sửa task.  
* Chỉ người tạo ghi chú và admin mới có quyền chỉnh sửa hoặc xóa ghi chú.  
* Quyền truy cập ghi chú:  
  * Admin và người tạo ghi chú: luôn truy cập được.  
  * Người dùng có quyền `view_any_note`: xem được mọi ghi chú.  
  * Người dùng có quyền truy cập dự án của ghi chú (thông qua `access_projects`).  
  * Người dùng được gán vào một trong các công việc liên kết với ghi chú.  
  * Các ghi chú Global (không thuộc dự án, không gắn task) chỉ có admin và người tạo ghi chú xem được.

## 4. Giao diện người dùng

* **Dashboard** hiển thị thống kê công việc theo trạng thái, ưu tiên và danh sách việc tới hạn (hôm nay, ngày mai), bao gồm cả mức Urgent.
* **Kanban board** cho phép kéo thả công việc giữa các cột trạng thái, sắp xếp thứ tự.
* **List view** hiển thị bảng dữ liệu chi tiết với các cột tùy chỉnh.
* **Calendar view** hiển thị công việc theo ngày bắt đầu/hạn chót.
* **Sidebar** hiển thị danh sách dự án với các liên kết con Board, List, Calendar, Notes; có liên kết Global Notes ở cấp cao.
* Hệ thống hỗ trợ ngôn ngữ Việt/Anh, có thể bổ sung ngôn ngữ mới qua `config/localization.php`.

## 5. Yêu cầu phi chức năng

* Ứng dụng sử dụng PHP thuần, không phụ thuộc framework, dễ triển khai trên shared hosting.
* Sử dụng PDO với prepared statements để chống SQL injection.  
* Sử dụng session để xác thực và quản lý trạng thái người dùng.  
* Giao diện responsive, tối ưu cho desktop và tablet.  
* Source code tuân theo cấu trúc MVC, dễ đọc, dễ mở rộng, có phân chia rõ Controller/Model/View.

## 6. Giới hạn

* Ứng dụng không hỗ trợ đăng ký tự do; chỉ admin mới tạo được người dùng.  
* Không có hệ thống phân quyền chi tiết theo module (ACL); quyền được lưu dạng JSON và được kiểm tra bằng các helper (`user_can`).  
* Không có API REST chính thức; nếu cần có thể xây dựng thêm controller trả JSON.