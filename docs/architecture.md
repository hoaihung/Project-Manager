# Kiến trúc hệ thống

Project Manager được xây dựng dựa trên mô hình Model–View–Controller (MVC) thuần với mục tiêu đơn giản, dễ đọc và dễ mở rộng. Dưới đây là cái nhìn tổng quan về kiến trúc:

## Thư mục `app/`

* **Core/** – chứa các lớp lõi:  
  * `App.php`: router đơn giản đọc tham số `controller` và `action` từ query string, rồi gọi phương thức tương ứng trong controller.  
  * `Controller.php`: lớp cơ sở cho mọi controller, cung cấp phương thức `render` để tải view và `loadModel` để khởi tạo model.  
  * `Model.php`: lớp cơ sở cho mọi model, bọc PDO và cung cấp hàm `query` với prepared statements.  
  * `Autoloader.php`: tự động nạp các lớp theo quy ước namespace và đường dẫn.

* **Controller/** – các controller xử lý request:
  * `AuthController`: đăng nhập/đăng xuất.
  * `DashboardController`: hiển thị bảng thống kê trạng thái, ưu tiên và công việc sắp tới.
  * `ProjectController`: CRUD dự án.
  * `TaskController`: CRUD công việc, hỗ trợ kéo thả, subtask, checklist, links.
  * `NoteController`: CRUD ghi chú, kèm logic kiểm tra quyền xem/sửa.
  * `UiController`: chứa các phương thức AJAX nhỏ như lưu trạng thái sidebar.
  * Các controller khác như `LogController`, `ProfileController`, `ReportController`, `TagController`, `UserController`.

* **Model/** – các lớp tương tác với database:
  * `Task.php`: thao tác với bảng `tasks`. Ngoài các CRUD cơ bản và thống kê theo trạng thái/ưu tiên, lớp này còn theo dõi thời điểm một công việc được hoàn thành thông qua cột `completed_at` (được set khi chuyển trạng thái sang `done` và xóa khi chuyển ra khỏi `done`). Phương thức `markSubtasksDone()` hỗ trợ đánh dấu tất cả subtasks của một công việc là hoàn thành khi người dùng chọn.
  * `Note.php`: thao tác với bảng `notes` và `note_task`.  
  * `TaskLink.php`: quản lý bảng `task_links`.  
  * `ChecklistItem.php`: quản lý bảng `checklist_items`.  
  * `TaskUser.php`: bảng phụ `task_user` quản lý người được gán vào task.  
  * `Project.php`, `User.php`, `Role.php`, `Setting.php`, `File.php`, `Log.php`…

* **View/** – chứa các file PHP hiển thị giao diện. Mỗi view có thể sử dụng helper `e()` để escape dữ liệu, và `__()` để dịch chuỗi. Layout chung (`layouts/header.php` và `layouts/footer.php`) định nghĩa cấu trúc HTML với sidebar, navigation và modals.

## Luồng xử lý

1. Trình duyệt gửi request tới `public/index.php` với các tham số query string `?controller=task&action=edit&id=5`.
2. `index.php` khởi tạo lớp `App`, `App` đọc tham số và định tuyến đến controller tương ứng (`TaskController`) và action (`edit`).
3. Controller xử lý logic nghiệp vụ: kiểm tra quyền, gọi Model để truy vấn DB, xử lý form (GET/POST), và cuối cùng gọi `render()` để hiển thị View.
4. View kết hợp dữ liệu truyền sang với HTML/CSS/JS để trả kết quả cho người dùng.
5. Tất cả truy vấn DB đều đi qua lớp `Model`, sử dụng prepared statements để đảm bảo an toàn.

## Lưu trữ dữ liệu và quyền

* Mỗi người dùng có một bản ghi quyền trong bảng `settings` với key `permissions_{user_id}`. JSON này chứa các trường như `create_project`, `edit_project`, `delete_task`, `access_projects` (danh sách ID dự án) và `view_any_note`.
* Quyền được kiểm tra bằng helper `user_can($permission, $resource)`. Admin (role_id = 1) luôn được phép thực hiện mọi thao tác.
* Thêm quyền mới chỉ cần mở rộng JSON và cập nhật hàm `user_can` theo khóa mới.

## Giao tiếp frontend

 Giao diện chủ yếu sử dụng Bootstrap và một tệp JS (`public/assets/js/app.js`) để thực hiện các thao tác tương tác: kéo thả trong Kanban, xử lý sidebar, thêm/chỉnh sửa liên kết và checklist. Khi kéo thả giữa các cột Kanban, hệ thống hiển thị các modal xác nhận:
 
 * Khi chuyển một công việc đã hoàn thành sang trạng thái khác, người dùng phải tick chọn “tôi hiểu và vẫn muốn tiếp tục” trước khi trạng thái được cập nhật và thời điểm hoàn thành (`completed_at`) sẽ bị xóa.
 * Khi chuyển một công việc có subtasks chưa hoàn thành vào cột “Hoàn thành”, modal sẽ hiển thị **hai nút radio** với lựa chọn “Giữ nguyên trạng thái cho subtasks” hoặc “Chuyển hết subtasks sang Hoàn thành”. Người dùng chọn một tùy chọn rồi bấm nút “Xác nhận” để áp dụng hoặc “Hủy bỏ” để bỏ qua. Tùy theo lựa chọn, hệ thống sẽ đánh dấu toàn bộ subtasks hoàn thành (cập nhật `completed_at`) hoặc giữ nguyên và tô viền cảnh báo cho task cha.

 Các modal này giúp tránh thao tác nhầm và bảo toàn dữ liệu. Sau khi người dùng xác nhận, JavaScript gửi yêu cầu tới máy chủ để cập nhật trạng thái và thứ tự của nhiệm vụ; các bản ghi được lưu lại trong cơ sở dữ liệu nên khi tải lại trang trạng thái không bị mất.
 Không sử dụng framework JS lớn nên ứng dụng tải nhanh và dễ tùy chỉnh.

### Modal chỉnh sửa ghi chú

 Từ phiên bản 4.8 trở đi, ghi chú có thể được chỉnh sửa trực tiếp thông qua các modal mà không cần rời khỏi trang hiện tại. Trong trang chỉnh sửa công việc, danh sách ghi chú kèm nút Sửa mở `noteEditModal` chứa form tiêu đề và nội dung. Modal này có thanh công cụ nhỏ cho phép người dùng bôi đậm, nghiêng hoặc tạo danh sách (đầu dòng) bằng cú pháp Markdown. Khi mở chi tiết ghi chú (modal xem), hệ thống lưu lại ID và nội dung thô của ghi chú; nút **Sửa** trong modal xem sẽ gọi lại modal chỉnh sửa với dữ liệu này. Tương tự, trong trang danh sách ghi chú (`notes/index`), các nút Sửa cũng mở modal chỉnh sửa ghi chú thay vì điều hướng tới trang khác. Thao tác lưu được xử lý qua AJAX và trang sẽ reload sau khi cập nhật để phản ánh thay đổi.

 Khi lưu ghi chú từ modal, chỉ tiêu đề và nội dung được gửi về backend. Để tránh mất thông tin liên kết (dự án hoặc các công việc đã gán), phương thức `NoteController::edit()` được cập nhật (từ bản 4.9.1) để **chỉ cập nhật `project_id` hoặc danh sách `task_ids` khi các tham số này có mặt trong request**. Nhờ vậy, việc chỉnh sửa nhanh trong modal sẽ không làm ghi chú mất liên kết với dự án hay công việc liên quan.