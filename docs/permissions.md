# Hệ thống phân quyền

Hệ thống sử dụng bảng `settings` để lưu quyền của từng người dùng dưới dạng JSON. Mỗi người dùng có khóa `permissions_{user_id}` trong bảng `settings`. Quyền được kiểm tra thông qua helper `user_can($permission, $resource)`. Dưới đây là một số quyền mặc định:

| Khóa quyền       | Ý nghĩa                                                                                 |
|------------------|-----------------------------------------------------------------------------------------|
| `create_project` | Cho phép tạo dự án mới.                                                                  |
| `edit_project`   | Cho phép chỉnh sửa thông tin dự án.                                                      |
| `delete_project` | Cho phép xóa dự án.                                                                      |
| `edit_task`      | Cho phép chỉnh sửa/di chuyển công việc. Quyền này mặc định bật cho tất cả thành viên dự án. |
| `delete_task`    | Cho phép xóa công việc. Quyền này mặc định bật cho tất cả thành viên dự án.            |
| `access_projects`| Mảng chứa ID của các dự án mà người dùng được phép truy cập.                            |
| `view_any_note`  | Khi đặt thành `1`, cho phép người dùng xem tất cả ghi chú (kể cả ngoài dự án họ tham gia). |

## Kiểm tra quyền

Helper `user_can($permission, $resource = null)` nằm trong `app/helpers.php` và trả về `true` hoặc `false`. Cách hoạt động:

```php
if (user_can('access_project', $projectId)) {
    // user có thể xem/ thao tác với dự án này
}

if (user_can('view_any_note')) {
    // user có thể xem mọi ghi chú
}
```

Admin (role_id = 1) bỏ qua toàn bộ kiểm tra và luôn trả về `true` cho tất cả quyền.

## Mở rộng quyền

Để thêm quyền mới:

1. Cập nhật default JSON trong `sql/patch_permissions.sql` và `sql/update_3.7.sql` để bổ sung trường quyền mới cho người dùng không phải admin.  
2. Bổ sung case mới trong `user_can()` để xử lý khóa mới.  
3. Tại các điểm logic cần kiểm tra, gọi `user_can('ten_quyen')` và xử lý theo kết quả.

Khi chỉnh sửa JSON quyền trong bảng `settings`, hãy đảm bảo định dạng JSON hợp lệ. Có thể sử dụng giao diện admin để chỉnh sửa quyền người dùng thông qua trường “permissions”.