# Change Log

File này ghi lại các thay đổi chính được thực hiện cho dự án Project Manager qua các phiên bản 4.x. Mỗi bản cập nhật bao gồm danh sách file bị ảnh hưởng, mô tả logic và tính năng mới.

## Phiên bản 4.9

Ngày phát hành: 10/08/2025

**Tính năng mới và sửa lỗi**

* **Cải tiến modal chuyển trạng thái Done** – Modal hỏi xử lý subtasks khi kéo task sang Done trên bảng Kanban được thay dropdown thành **hai nút radio** “Giữ nguyên trạng thái cho subtasks” và “Chuyển hết subtasks sang Hoàn thành”, kèm các nút “Hủy bỏ” và “Xác nhận”. Phần JavaScript (`app.js`) được cập nhật để đọc giá trị từ radio.

* **Thiết kế lại báo cáo** – Giao diện báo cáo theo thời gian được chia thành các nhóm rõ ràng theo dự án. Mỗi dự án hiển thị ba cột: số công việc bắt đầu, thống kê hoàn thành và số công việc quá hạn. Các danh sách công việc dài được rút gọn: hiển thị 5 mục đầu tiên và phần còn lại nằm trong thẻ `<details>` có thể bung/thu. Điều này mô phỏng layout của các ứng dụng quản lý dự án phổ biến. Ngoài ra, bảng thống kê chi tiết theo người dùng vẫn khả dụng cho admin trong phần mở rộng.

* **Modal chỉnh sửa ghi chú trong trang ghi chú** – Trang danh sách ghi chú (`app/View/notes/index.php`) giờ đây không chuyển sang trang edit khi nhấn biểu tượng chỉnh sửa. Thay vào đó, mỗi ghi chú có một nút sửa với thuộc tính dữ liệu (`data-note-edit`) chứa ID, tiêu đề và nội dung thô. Một modal `noteEditModal` được thêm vào cuối trang để chỉnh sửa nhanh. Sau khi lưu, trang tự động tải lại để phản ánh thay đổi. Modal cung cấp thanh công cụ đơn giản cho Bold, nghiêng và list bằng cách chèn cú pháp markdown.

* **Chỉnh sửa ghi chú từ modal chi tiết** – Trong trang chỉnh sửa công việc (`tasks/edit.php`), khi người dùng mở chi tiết ghi chú, modal chi tiết giờ có thêm nút **“Sửa”**. Nút này đóng modal chi tiết và mở modal chỉnh sửa với dữ liệu hiện tại của ghi chú. Script đã được cập nhật để lưu trữ ID và nội dung thô của ghi chú khi mở chi tiết.

* **Cập nhật tài liệu** – File `docs/overview.md` bổ sung mô tả về modal chỉnh sửa ghi chú, thanh định dạng nội dung ghi chú và bố cục báo cáo mới. Tệp mới `docs/change_log.md` được tạo để ghi lại các thay đổi qua các phiên bản.

## Phiên bản 4.9.1

Ngày phát hành: 10/08/2025 (bản vá)

**Thay đổi và sửa lỗi**

* **Bổ sung tooltip và danh sách chi tiết trong báo cáo** – Các mục thống kê trên báo cáo theo thời gian giờ có tooltip giải thích tiêu chí tính toán (ví dụ: “Bắt đầu trong giai đoạn”, “Quá hạn”). Ngoài các danh sách công việc bắt đầu và quá hạn, báo cáo còn hiển thị danh sách chi tiết cho các mục **Hoàn thành đúng hạn**, **Hoàn thành trễ** và **Chưa hoàn thành**. Mỗi danh sách hiển thị tối đa 5 dòng, phần còn lại ẩn trong thẻ `<details>` có thể mở rộng. Điều này giúp người quản lý dự án dễ dàng nắm bắt cụ thể những công việc nào thuộc từng nhóm.

* **Sửa lỗi modal chỉnh sửa ghi chú** – Khi lưu ghi chú từ modal, ghi chú không còn bị mất liên kết với dự án hoặc task gốc. `NoteController::edit()` giờ đây chỉ cập nhật `project_id` và `task_ids` nếu các tham số tương ứng có trong POST; nếu không có, những liên kết hiện tại sẽ được giữ nguyên. Điều này giải quyết lỗi trước đây khi người dùng chỉnh sửa nội dung mà không điền dự án (hoặc danh sách công việc) khiến ghi chú mất liên kết.

* **Hiển thị nút Sửa cho ghi chú khi gán vào task** – Trên trang chỉnh sửa công việc, danh sách ghi chú liên kết giờ hiển thị nút **Sửa** bên cạnh mỗi ghi chú nếu người dùng là tác giả hoặc admin. Khi thêm một ghi chú vào công việc thông qua modal “Đính kèm ghi chú”, danh sách được cập nhật bằng AJAX và bao gồm nút Sửa. Hàm `updateNotesList()` đã được viết lại: nó nhận thêm `content_raw` và `user_id` để phân quyền chỉnh sửa và gán `data` cho nút mở modal chi tiết.

* **Cải thiện modal chi tiết ghi chú** – Khi người dùng nhấp vào tiêu đề ghi chú trong danh sách, script lưu lại ID và nội dung thô; nút **Sửa** trong modal chi tiết sẽ mở modal chỉnh sửa với dữ liệu chính xác. Đây là bước cần thiết để đảm bảo việc chỉnh sửa sau khi xem chi tiết ghi chú hoạt động đúng.

## Phiên bản 4.9.2

Ngày phát hành: 10/08/2025 (bản vá thứ hai)

**Tính năng mới và sửa lỗi**

* **Hiển thị nội dung ghi chú dưới dạng HTML** – Các trang hiển thị ghi chú (bao gồm modal chi tiết) giờ sử dụng hàm `markdown_to_html` để chuyển đổi nội dung Markdown sang HTML an toàn (hỗ trợ in đậm, nghiêng, danh sách và liên kết). Điều này giúp người dùng đọc ghi chú dễ dàng hơn thay vì xem văn bản Markdown thô.

* **Sửa lỗi nút Sửa ghi chú trong trang công việc** – Do việc truyền tham số trong thuộc tính `onclick` không thoát dấu nháy, trình duyệt đã báo lỗi `Unexpected end of input`. Thuộc tính `onclick` đã được sửa lại: các tham số chuỗi được bao trong dấu nháy đơn và được escape bằng `str_replace()`. Nhờ đó, nút Sửa bên cạnh mỗi ghi chú trong trang chỉnh sửa công việc hoạt động chính xác.

* **Bổ sung thanh định dạng cho trang tạo/chỉnh sửa ghi chú** – Các trang tạo ghi chú (`notes/create.php`), chỉnh sửa ghi chú (`notes/edit.php`) và modal “Tạo ghi chú mới” trên trang task đều có thanh công cụ định dạng (Bold, Italic, List) giống modal chỉnh sửa. Nhấp vào nút sẽ chèn cú pháp Markdown tương ứng vào vị trí chọn trong ô soạn thảo.

* **Chỉnh sửa API gắn ghi chú vào task** – Phương thức `TaskController::addNoteToTaskAjax()` giờ sử dụng `markdown_to_html()` để tạo trường `content_html` trả về cho JavaScript, giúp hiển thị ghi chú dạng HTML khi đính kèm. Trước đây sử dụng `linkify()` chỉ chuyển đổi URL và không hỗ trợ Markdown.

* **Bổ sung bộ lọc khoảng ngày tùy chỉnh cho báo cáo** – ReportController đọc thêm các tham số `start_date` và `end_date`. Nếu được cung cấp và hợp lệ (định dạng `YYYY-MM-DD` và `end_date` ≥ `start_date`), hệ thống sẽ dùng khoảng này thay cho các lựa chọn tuần/tháng/năm mặc định. Giao diện báo cáo (`report/workload.php`) thêm hai ô nhập ngày *Từ ngày* và *Đến ngày*, cùng tùy chọn **Tùy chỉnh** trong menu *Khoảng thời gian*. Hàm kiểm tra tự động giới hạn ngày kết thúc không vượt quá ngày hiện tại.

* **Thống kê “Đang thực hiện”** – Trong phần thống kê theo thời gian, một mục mới **Đang thực hiện** được tính và hiển thị: đây là các công việc chưa hoàn thành và chưa quá hạn (due date ≥ hôm nay). Controller bổ sung các trường `in_progress` và `in_progress_tasks` vào cấu trúc dữ liệu, còn view hiển thị số lượng và danh sách tương tự các mục khác. Tooltips được thêm để giải thích tiêu chí.

* **Cập nhật localisation** – Bổ sung khóa dịch `time_based_statistics`, `time_based_statistics_hint`, `tasks_in_progress`, `in_progress_hint` và `custom_range` trong `config/localization.php` để mô tả rõ ràng hơn các mục trong báo cáo và tùy chọn mới.

* **Cập nhật tài liệu** – `docs/overview.md` bổ sung mô tả về chuyển đổi Markdown→HTML, thanh định dạng trên trang tạo/chỉnh sửa ghi chú và bộ lọc khoảng ngày tùy chỉnh cùng mục “Đang thực hiện”. Thêm khóa dịch mới ở `localization.php` để hiển thị tiếng Việt.

## Phiên bản 4.9.3

Ngày phát hành: 10/08/2025 (bản vá thứ ba)

**Sửa lỗi và cải tiến nhỏ**

* **Hiển thị định dạng Markdown trên trang quản lý ghi chú** – Trước đây, trang danh sách ghi chú (`notes/index.php`) sử dụng hàm `linkify()` để chuyển URL thành liên kết, dẫn tới việc hiển thị nội dung Markdown thô. Phiên bản này chuyển sang sử dụng `markdown_to_html()` khi render nội dung ghi chú trong modal chi tiết, giúp hiển thị HTML an toàn với in đậm, nghiêng và danh sách. Phần preview trong bảng vẫn dùng văn bản thu gọn (strip_tags).  Mã thay đổi ở file `app/View/notes/index.php`.

* **Cập nhật tức thời màu cảnh báo khi kéo task sang Done** – Khi người dùng kéo một task có subtasks chưa hoàn thành sang cột Done và chọn giữ nguyên trạng thái, JavaScript trước đây chỉ cập nhật `data-warning` mà không làm mới kiểu hiển thị nên viền đỏ không xuất hiện cho tới khi refresh. Bản vá sửa hàm xử lý drop trong `public/assets/js/app.js` bằng cách gọi `updateKanbanItemStyles(board)` ngay sau khi `handleStatusChangesModal()` hoàn tất và trước khi thực hiện các yêu cầu AJAX. Điều này giúp viền đỏ và màu nền cảnh báo cập nhật ngay lập tức.

* **Cập nhật tài liệu** – `docs/overview.md` ghi nhận hai thay đổi trên: hiển thị Markdown của ghi chú ở trang danh sách và cập nhật màu cảnh báo realtime.

## Phiên bản 4.8

Ngày phát hành: 09/08/2025

* **Sửa lỗi kéo thả Kanban** – Đảm bảo cập nhật trạng thái task và lưu vào cơ sở dữ liệu sau khi người dùng xác nhận modal. Trước đây trạng thái bị reset sau khi refresh.
* **Thay dropdown bằng radio** trong modal khi chuyển task sang Done mà còn subtasks.
* **Bổ sung bộ lọc báo cáo** – Cho phép chọn giai đoạn tuần/tháng/năm, lọc theo dự án và (với admin) theo người dùng. Thống kê bao gồm số công việc bắt đầu, hoàn thành đúng/trễ, tỉ lệ hoàn thành và số công việc quá hạn, được nhóm theo dự án với chi tiết per-user.
* **Modal chỉnh sửa ghi chú trong trang công việc** – Khi chỉnh sửa task, mỗi ghi chú có nút sửa mở modal. Modal có thanh công cụ Bold/Italic/List giúp định dạng nội dung.

## Phiên bản 4.7

Ngày phát hành: 08/08/2025

* **Theo dõi ngày hoàn thành** – Thêm cột `completed_at` vào bảng `tasks` và cập nhật model để ghi nhận thời gian khi task chuyển sang trạng thái Done và xoá khi chuyển khỏi Done.
* **Xác nhận khi đổi trạng thái** – Khi kéo task khỏi Done, modal với checkbox yêu cầu xác nhận. Khi kéo task vào Done có subtasks chưa hoàn thành, modal hỏi người dùng có muốn đánh dấu tất cả subtasks là Done hay giữ nguyên.
* **Thêm action `completeSubtasks`** trong `TaskController` để đánh dấu tất cả subtasks.
* **Xóa biểu đồ đến hạn** trong báo cáo và thay bằng thống kê theo giai đoạn tuần/tháng, tính số công việc bắt đầu, đúng/trễ hạn, tỉ lệ hoàn thành và danh sách quá hạn.
