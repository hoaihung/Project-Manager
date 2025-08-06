# Hướng dẫn sử dụng

## Đăng nhập & giao diện chính

Sau khi cài đặt, truy cập địa chỉ của ứng dụng (ví dụ http://localhost:8000) và đăng nhập bằng tài khoản admin mặc định. Giao diện chính (dashboard) hiển thị biểu đồ thống kê công việc theo trạng thái, mức độ ưu tiên (bao gồm cả mức Urgent) và danh sách công việc tới hạn hôm nay/ ngày mai.

Thanh bên trái (sidebar) chứa các liên kết:

* **Dashboard** – trở về trang tổng quan.
* **Projects** – danh sách dự án. Mỗi dự án có các liên kết con Board, List, Calendar, Notes.
* **Notes** – danh sách tất cả ghi chú bạn có quyền xem, gồm ghi chú toàn cục, ghi chú dự án và ghi chú gắn task.

Bạn có thể thu gọn hoặc mở rộng sidebar. Ứng dụng sẽ ghi nhớ trạng thái giữa các trang.

## Quản lý dự án

Trong mục Projects:

* Nhấn “Tạo dự án” để thêm dự án mới (cần quyền `create_project`).
* Nhấn vào tên dự án để chuyển tới bảng Kanban của dự án.
* Tại trang dự án, bạn có thể chỉnh sửa hoặc xóa dự án nếu có quyền.

## Quản lý công việc

Mỗi dự án có ba chế độ xem: **Board**, **List** và **Calendar**.

### Board (Kanban)

* Kéo thả công việc giữa các cột (To Do, In Progress, Done) để cập nhật trạng thái.
* Nhấn nút “+ Task” ở cuối cột để tạo công việc mới. Form tạo hỗ trợ điền tên, mô tả, mức độ ưu tiên (Low/Medium/High/Urgent), thẻ, thời gian và gán người thực hiện.
* Chọn biểu tượng checklist để thêm các mục checklist; chọn biểu tượng link để thêm liên kết tới tài liệu ngoài.

### List view

* Hiển thị dạng bảng với các cột: tên, dự án, ưu tiên, trạng thái, hạn chót, người thực hiện.
* Có thể sắp xếp, lọc theo tag hoặc trạng thái.

### Calendar view

* Sử dụng để xem công việc theo lịch (ngày bắt đầu/hạn chót).

### Chỉnh sửa task

Khi mở một công việc, trang chỉnh sửa hiển thị các thông tin chi tiết:

* **Mô tả** và **bình luận**: bạn có thể dùng Markdown và các đường link sẽ tự động được chuyển thành liên kết.
* **Checklist**: thêm/xóa mục, đánh dấu hoàn thành.
* **Links**: thêm nhiều liên kết bên ngoài; nhập tên hiển thị và URL. Bấm dấu “+” để thêm dòng mới.
* **Notes**: liệt kê các ghi chú liên kết với task; bấm vào tiêu đề ghi chú để xem chi tiết; bấm “Add note” để tạo ghi chú mới (sẽ tự động chọn dự án/task hiện tại).

## Quản lý ghi chú (Notes)

* Trong sidebar, chọn **Notes** để xem tất cả ghi chú. Bạn có thể lọc theo dự án ở phía trên.
* Nhấn “Tạo ghi chú” để thêm ghi chú mới. Bạn có thể chọn dự án (hoặc để trống để tạo ghi chú toàn cục), nhập tiêu đề, nội dung (hỗ trợ Markdown) và chọn các công việc liên kết. Khi truy cập từ trang Task, ghi chú sẽ tự chọn dự án và công việc hiện tại.
* Danh sách ghi chú hiển thị cột “Scope” để phân biệt ghi chú toàn cục, ghi chú dự án hay ghi chú gắn task (nếu gắn nhiều task, chỉ hiển thị một vài tên đầu). Bạn chỉ nhìn thấy những ghi chú mình có quyền truy cập.
* Chỉ người tạo và admin được phép sửa/xóa ghi chú. Người dùng có quyền `view_any_note` có thể xem tất cả ghi chú, kể cả những dự án mình không tham gia.

## Quản lý người dùng và quyền

Admin có thể tạo người dùng mới, gán vai trò (admin hoặc member) và tùy chỉnh quyền bằng cách sửa JSON trong trường “Permissions”. Ví dụ:

```json
{
  "create_project":1,
  "edit_project":1,
  "delete_project":0,
  "edit_task":1,
  "delete_task":0,
  "access_projects":[1,2,3],
  "view_any_note":0
}
```

Trong đó `access_projects` là danh sách ID dự án mà user được tham gia. Quyền `view_any_note` nếu đặt 1 cho phép xem toàn bộ ghi chú (kể cả những dự án không có trong `access_projects`).

## Tips

* Sử dụng Markdown để trình bày nội dung ghi chú và mô tả rõ ràng hơn.
* Sử dụng mức ưu tiên “Urgent” cho những công việc cần xử lý gấp; chúng sẽ được tô màu đỏ trên dashboard và bảng Kanban.
* Luôn kiểm tra quyền truy cập trước khi chia sẻ liên kết công việc/ghi chú với người khác.