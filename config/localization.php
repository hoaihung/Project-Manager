<?php
/**
 * Localization strings for the application.
 *
 * This file defines translation strings keyed by language. To support
 * additional languages simply add a new top‑level array keyed by the
 * locale (e.g. 'en', 'vi', 'fr'...). Within each locale provide
 * associative arrays of string identifiers to translated text. When
 * you need to display text in your views or controllers use the
 * helper function __($key) defined in app/helpers.php. This function
 * looks up the current locale from config and returns the appropriate
 * translation.
 */

return [
    // Vietnamese translations
    'vi' => [
        'login' => 'Đăng nhập',
        'logout' => 'Đăng xuất',
        'username' => 'Tên đăng nhập',
        'password' => 'Mật khẩu',
        'remember_me' => 'Nhớ đăng nhập',
        'dashboard' => 'Bảng điều khiển',
        'projects' => 'Dự án',
        'tasks' => 'Công việc',
        'users' => 'Người dùng',
        'roles' => 'Vai trò',
        'create_project' => 'Tạo dự án',
        'edit_project' => 'Chỉnh sửa dự án',
        'project_name' => 'Tên dự án',
        'project_description' => 'Mô tả dự án',
        'start_date' => 'Ngày bắt đầu',
        'end_date' => 'Ngày kết thúc',
        'status' => 'Trạng thái',
        'priority' => 'Mức độ ưu tiên',
        'tags' => 'Thẻ',
        'assigned_to' => 'Giao cho',
        'parent_id' => 'Nhiệm vụ cha',
        'actions' => 'Thao tác',
        'save' => 'Lưu',
        'cancel' => 'Hủy',
    // Back to parent task
    'back_to_parent_task' => 'Quay lại Task cha',
        'delete' => 'Xóa',
        'back' => 'Quay lại',
        'create_task' => 'Tạo công việc',
        'edit_task' => 'Chỉnh sửa công việc',
        'edit' => 'Chỉnh sửa',
        'subtask' => 'Nhiệm vụ con',
        'create_subtask' => 'Tạo nhiệm vụ con',
        'todo' => 'Cần làm',
        'in_progress' => 'Đang làm',
        'bug_review' => 'Bug/Review',
        'done' => 'Hoàn thành',
        'urgent' => 'Cấp bách',
        'high' => 'Cao',
        'normal' => 'Trung bình',
        'low' => 'Thấp',
        'collapse_subtasks' => 'Thu gọn subtasks',
        'expand_subtasks' => 'Hiển thị subtasks',
        'separate_subtasks' => 'Tách subtasks',
        'comment' => 'Bình luận',
        'comments' => 'Bình luận',
        'add_comment' => 'Thêm bình luận',
        'attachments' => 'Tệp đính kèm',
        'upload_file' => 'Tải lên tệp',
        'kanban_board' => 'Bảng Kanban',
        'calendar_view' => 'Lịch',
        'list_view' => 'Danh sách',
        'gantt_view' => 'Biểu đồ Gantt',
        'flow_view' => 'Lưu đồ',
        'notifications' => 'Thông báo',
        'reports' => 'Báo cáo',
        'profile' => 'Hồ sơ',
        'log' => 'Nhật ký',
        'settings' => 'Cài đặt',
        'admin_panel' => 'Trang quản trị',
        'overdue' => 'Quá hạn',

        // Dashboard day tasks
        'today_and_tomorrow_tasks' => 'Công việc cần làm hôm nay và ngày mai',
        'today' => 'Hôm nay',
        'tomorrow' => 'Ngày mai',
        'other_tasks' => 'Công việc còn lại',

        // Project filter
        'all_projects' => 'Tất cả dự án',

        // Upcoming tasks/projects headings
        'upcoming_tasks_7_days' => 'Công việc sắp đến hạn 7 ngày tới',
        'no_upcoming_tasks' => 'Không có công việc nào sắp tới.',
        'upcoming_projects_7_days' => 'Dự án sắp hết hạn 7 ngày tới',
        'no_upcoming_projects' => 'Không có dự án nào sắp hết hạn.',
        'due_soon' => 'Sắp đến hạn',
        'summary' => 'Tổng quan',
        'overlap_tasks' => 'Nhiệm vụ chồng chéo',
        'today' => 'Hôm nay',
        'month' => 'Tháng',
        'no_tasks' => 'Không có công việc',
        'no_subtasks' => 'Không có nhiệm vụ con',
        'priority_high' => 'Cao',
        'priority_normal' => 'Trung bình',
        'priority_low' => 'Thấp',
        'assignee' => 'Người được giao',
        'due_date' => 'Hạn chót',
        'start_date_col' => 'Ngày bắt đầu',
        'end_date_col' => 'Ngày kết thúc',
        'file' => 'Tệp',
        'upload_attachments' => 'Tải lên tệp đính kèm',
        'language' => 'Ngôn ngữ',
        'vietnamese' => 'Tiếng Việt',
        'english' => 'Tiếng Anh',
        'navigation' => 'Điều hướng',
        'my_projects' => 'Dự án của tôi',
        'tags' => 'Thẻ',
        'reports' => 'Báo cáo',
        'board' => 'Bảng',
        'list' => 'Danh sách',
        'calendar' => 'Lịch',
        'gantt' => 'Gantt',
        'flow' => 'Lưu đồ',
        'reorder_subtasks' => 'Sắp xếp thứ tự công việc con (kéo thả)',
        'tags_placeholder' => 'Thẻ (phân tách bằng dấu phẩy)',
        'days' => 'ngày',
        'from' => 'từ',
        'to' => 'đến',

        // Additional UI labels used in filters and forms
        'apply' => 'Áp dụng',
        'clear' => 'Xóa',
        'tag_label' => 'Thẻ',
        'user_label' => 'Người dùng',
        'priority_label' => 'Ưu tiên',
        'any' => '-- Bất kỳ --',
        'task_name' => 'Tên công việc',
        'task_description' => 'Mô tả công việc',
        'subtask_of' => 'Nhiệm vụ con của',
        'project' => 'Dự án',
        'task' => 'Công việc',
        'start' => 'Bắt đầu',
        'due' => 'Kết thúc',
        'assignee' => 'Người được giao',
        'no_relationships' => 'Không có mối quan hệ nào được tìm thấy.',
        'flow_description' => 'Biểu đồ quan hệ giữa các công việc (flow chart).',
        'apply_filter' => 'Áp dụng bộ lọc',
        'clear_filter' => 'Xóa bộ lọc',
        'task_name_col' => 'Tên công việc',
        'project_name_col' => 'Dự án',
        'workload_report' => 'Báo cáo khối lượng công việc',
        'total_tasks' => 'Tổng số công việc',
        'due_soon_days' => 'Sắp đến hạn (≤ 3 ngày)',
        'no_workload' => 'Không có dữ liệu khối lượng.',

        // New keys for improved workload and due soon charts
        // Hint shown above the workload chart explaining stacked bars
        'workload_chart_hint' => 'Biểu đồ dưới đây cho thấy khối lượng công việc của mỗi thành viên được phân tách theo trạng thái. Các thanh được xếp chồng giúp bạn dễ dàng so sánh tổng số việc và mức độ phân bố.',
        // Hint for the upcoming due soon tasks chart
        'due_soon_chart_hint' => 'Số lượng công việc đến hạn trong 7 ngày tới (bao gồm hôm nay).',
        // Title for the due soon tasks chart
        // Title for the chart showing number of tasks due by date
        'due_soon_tasks_chart' => 'Số công việc đến hạn theo ngày',
        // Axis title for number of tasks
        'number_of_tasks' => 'Số lượng công việc',
        // Label for tasks that are due (used in chart legend)
        'due_tasks' => 'Công việc đến hạn',
        // Generic label for date axis in charts
        'date' => 'Ngày',

        // Analysis of completed tasks relative to due dates
        'done_early' => 'Hoàn thành sớm',
        'done_on_time' => 'Hoàn thành đúng hạn',
        'done_late' => 'Hoàn thành trễ',
        'done_due_analysis_title' => 'Thống kê công việc hoàn thành theo hạn',
        'done_due_analysis_hint' => 'Phân loại công việc đã hoàn thành dựa trên ngày đến hạn (so với hiện tại).',

        // Time based statistics section in reports
        'time_based_statistics' => 'Thống kê theo thời gian',
        'time_based_statistics_hint' => 'Bạn có thể lọc theo tuần, tháng, năm, dự án và (nếu là quản trị) theo người dùng. Các số liệu hiển thị trong khoảng từ ngày bắt đầu tới ngày hiện tại hoặc theo khoảng tùy chỉnh.',
        'tasks_in_progress' => 'Đang thực hiện',
        'in_progress_hint' => 'Công việc chưa hoàn thành và vẫn còn trong thời hạn (chưa quá hạn).',

        // Custom range label in reports
        'custom_range' => 'Khoảng tùy chỉnh',

        // Labels for current week/month/year used in reports
        'week_current'  => 'Tuần hiện tại',
        'month_current' => 'Tháng hiện tại',
        'year_current'  => 'Năm hiện tại',

        // Buttons
        'edit_user' => 'Chỉnh sửa',
        'delete_user' => 'Xóa',

        // Confirmation messages
        'confirm_delete_subtasks' => 'Tôi hiểu rằng hành động này sẽ xóa cả nhiệm vụ con (nếu có).',

        // Permission management
        'permissions' => 'Phân quyền',
        // Permission to create a new project and edit one’s own projects
        'perm_create_project' => 'Được tạo/sửa dự án của mình',
        // Permission to edit projects created by other users (adding members, changing details).  Deletion is reserved for admins only.
        'perm_edit_project' => 'Được sửa dự án của người khác',
        'perm_delete_project' => 'Được xoá dự án',
        // Clarify that these permissions apply to tasks created by others.  All users
        // can edit or delete their own tasks; this permission controls whether
        // they may edit or delete tasks created by other users.
        'perm_edit_task' => 'Được sửa công việc (kể cả do người khác tạo)',
        'perm_delete_task' => 'Được xoá công việc (kể cả do người khác tạo)',
        'project_access' => 'Quyền truy cập dự án',
        'save_permissions' => 'Lưu phân quyền',
        'no_permission' => 'Bạn không có quyền thực hiện hành động này.',
        'no_projects' => 'Không có dự án nào.',

        // Project membership management
        'project_members' => 'Thành viên dự án',
        'all_users' => 'Tất cả người dùng',
        'select_members_hint' => 'Chọn người dùng được tham gia dự án. Nếu chọn Tất cả, tất cả người dùng (ngoại trừ admin) sẽ được thêm.',

        // Notes module
        'notes' => 'Ghi chú',
        'note' => 'Ghi chú',
        'create_note' => 'Tạo ghi chú',
        'edit_note' => 'Chỉnh sửa ghi chú',
        'title' => 'Tiêu đề',
        'content' => 'Nội dung',
        'author' => 'Tác giả',
        'linked_tasks' => 'Công việc liên kết',
        'view' => 'Xem',
        'optional' => 'Không bắt buộc',
        'select_tasks_hint' => 'Chọn các công việc liên quan (nếu có).',
        'select_project_for_tasks' => 'Chọn một dự án để hiển thị các công việc.',
        // Renamed from global to private since only author and admin can view
        'global' => 'Riêng tư',
        'scope' => 'Phạm vi',
        'no_notes' => 'Không có ghi chú.',
        'no_links' => 'Không có liên kết.',
        'confirm_delete_note' => 'Bạn có chắc muốn xóa ghi chú này?',

        // Dates
        'created_at' => 'Ngày tạo',

        // Note attachment UI
        'select_note' => 'Chọn ghi chú',
        'attach_note' => 'Gắn ghi chú',
        'select_note_placeholder' => 'Chọn ghi chú để liên kết',
        'existing_notes' => 'Ghi chú có sẵn',
        'add' => 'Thêm',
        // Checklist and links
        'checklist' => 'Danh sách kiểm',
        'add_checklist_item' => 'Thêm mục checklist',
        'links' => 'Liên kết',
        'link_name' => 'Tên liên kết',
        'link_url' => 'URL liên kết',
        'add_link' => 'Thêm liên kết',
        'add_note' => 'Thêm ghi chú',
        'no_notes_for_task' => 'Không có ghi chú nào cho công việc này.',
        'member_count' => 'Số thành viên',

        // Tag page
        'tags_page_title' => 'Tất cả thẻ',
        'tags_page_description' => 'Danh sách tất cả các thẻ đã được sử dụng trong hệ thống cùng với số lượng công việc gắn thẻ đó.',
        'no_tags' => 'Chưa có thẻ nào.',
        'tag_singular' => 'Thẻ',
        'task_count' => 'Số công việc',
        'in_project' => 'Trong dự án',
        'tag_instructions' => 'Để xem danh sách công việc theo thẻ, hãy sử dụng bộ lọc Thẻ trong chế độ xem Danh sách của dự án tương ứng.',

        // Misc generic labels
        'description' => 'Mô tả',
        'subtasks' => 'Nhiệm vụ con',

        // Toggle subtasks in Kanban
        'toggle_subtasks' => 'Hiển thị/ẩn nhiệm vụ con',

        // List view grouping
        'none' => 'Không',
        'unassigned' => 'Chưa giao',
        'no_due_date' => 'Không hạn',
        'no_tags' => 'Không nhãn',

        // Profile page
        'profile_title' => 'Hồ sơ cá nhân',
        'user_info' => 'Thông tin người dùng',
        'full_name' => 'Họ tên',
        'email' => 'Email',
        'change_password' => 'Đổi mật khẩu',
        'my_tasks' => 'Công việc của tôi',
        'no_assigned_tasks' => 'Bạn chưa được gán vào công việc nào.',
        'my_comments' => 'Bình luận của tôi',
        'no_comments' => 'Chưa có bình luận.',
        'my_attachments' => 'Tập tin đính kèm của tôi',
        'no_attachments' => 'Không có tập tin đính kèm nào.',

        // Profile summary and notifications enhancements
        // Summary card heading on profile page
        'task_summary' => 'Tổng quan công việc',
        // Label for tasks due today in summary/notifications
        'due_today' => 'Đến hạn hôm nay',
        // Label for high priority tasks in notifications
        'high_priority' => 'Ưu tiên cao',

        // Dashboard terms
        'task_statistics' => 'Thống kê công việc',
        'total' => 'Tổng cộng',
        'overdue' => 'Quá hạn',

        // Kanban grouping labels and parent indicator
        'group_subtasks' => 'Nhóm nhiệm vụ con',
        'separate_subtasks' => 'Hiển thị nhiệm vụ con riêng',
        'parent' => 'Nhiệm vụ cha',

        // Trash management
        'trash' => 'Thùng rác',
    'admin_tools' => 'Quản lý',

        // Task deletion confirmations
        'delete_task' => 'Xóa công việc',
        'delete_task_prompt' => 'Bạn có chắc chắn muốn xóa công việc này?',
        'delete_subtasks_option' => 'Xóa luôn các nhiệm vụ con',
        'delete_task_prompt_with_subtasks' => 'Task này có :count subtask. Xóa task sẽ xóa luôn các subtask. Bạn có chắc?',
        'confirm_delete_option' => 'Tôi xác nhận xóa công việc này',
        'delete_not_confirmed' => 'Bạn phải xác nhận xóa trước khi tiếp tục',
    ],
    // English translations
    'en' => [
        'login' => 'Login',
        'logout' => 'Logout',
        'username' => 'Username',
        'password' => 'Password',
        'remember_me' => 'Remember me',
        'dashboard' => 'Dashboard',
        'projects' => 'Projects',
        'tasks' => 'Tasks',
        'users' => 'Users',
        'roles' => 'Roles',
        'create_project' => 'Create Project',
        'edit_project' => 'Edit Project',
        'project_name' => 'Project Name',
        'project_description' => 'Project Description',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'status' => 'Status',
        'priority' => 'Priority',
        'tags' => 'Tags',
        'assigned_to' => 'Assigned to',
        'parent_id' => 'Parent Task',
        'actions' => 'Actions',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'back_to_parent_task' => 'Back to parent task',
        'delete' => 'Delete',
        'back' => 'Back',
        'create_task' => 'Create Task',
        'edit_task' => 'Edit Task',
        'edit' => 'Edit',
        'subtask' => 'Subtask',
        'create_subtask' => 'Create Subtask',
        'todo' => 'To Do',
        'in_progress' => 'In Progress',
        'bug_review' => 'Bug/Review',
        'done' => 'Done',
        'urgent' => 'Urgent',
        'high' => 'High',
        'normal' => 'Normal',
        'low' => 'Low',
        'collapse_subtasks' => 'Collapse subtasks',
        'expand_subtasks'   => 'Show subtasks',
        'separate_subtasks' => 'Separate subtasks',
        'comment' => 'Comment',
        'comments' => 'Comments',
        'add_comment' => 'Add Comment',
        'attachments' => 'Attachments',
        'upload_file' => 'Upload File',
        'kanban_board' => 'Kanban Board',
        'calendar_view' => 'Calendar',
        'list_view' => 'List',
        'gantt_view' => 'Gantt Chart',
        'flow_view' => 'Flow',
        'notifications' => 'Notifications',
        'reports' => 'Reports',
        'profile' => 'Profile',
        'log' => 'Log',
        'settings' => 'Settings',
        'admin_panel' => 'Admin Panel',
        'overdue' => 'Overdue',

        // Dashboard day tasks
        'today_and_tomorrow_tasks' => 'Tasks for today and tomorrow',
        'today' => 'Today',
        'tomorrow' => 'Tomorrow',
        'other_tasks' => 'Other tasks',

        // Project filter
        'all_projects' => 'All projects',

        // Upcoming tasks/projects headings
        'upcoming_tasks_7_days' => 'Tasks due in the next 7 days',
        'no_upcoming_tasks' => 'No upcoming tasks.',
        'upcoming_projects_7_days' => 'Projects ending in the next 7 days',
        'no_upcoming_projects' => 'No projects nearing deadline.',
        'due_soon' => 'Due soon',
        'summary' => 'Summary',
        'overlap_tasks' => 'Overlapping tasks',
        'today' => 'Today',
        'month' => 'Month',
        'no_tasks' => 'No tasks',
        'no_subtasks' => 'No subtasks',
        'priority_high' => 'High',
        'priority_normal' => 'Normal',
        'priority_low' => 'Low',
        'assignee' => 'Assignee',
        'due_date' => 'Due Date',
        'start_date_col' => 'Start Date',
        'end_date_col' => 'End Date',
        'file' => 'File',
        'upload_attachments' => 'Upload attachments',
        'language' => 'Language',
        'vietnamese' => 'Vietnamese',
        'english' => 'English',
        'navigation' => 'Navigation',
        'my_projects' => 'My Projects',
        'tags' => 'Tags',
        'reports' => 'Reports',
        'board' => 'Board',
        'list' => 'List',
        'calendar' => 'Calendar',
        'gantt' => 'Gantt',
        'flow' => 'Flow',
        'reorder_subtasks' => 'Reorder subtasks (drag to sort)',
        'tags_placeholder' => 'Tags (comma-separated)',
        'days' => 'days',
        'from' => 'from',
        'to' => 'to',

        // Additional UI labels used in filters and forms
        'apply' => 'Apply',
        'clear' => 'Clear',
        'tag_label' => 'Tag',
        'user_label' => 'User',
        'priority_label' => 'Priority',
        'any' => '-- Any --',
        'task_name' => 'Task Name',
        'task_description' => 'Task Description',
        'subtask_of' => 'Subtask of',
        'project' => 'Project',
        'task' => 'Task',
        'start' => 'Start',
        'due' => 'Due',
        'assignee' => 'Assignee',
        'no_relationships' => 'No relationships found.',
        'flow_description' => 'Diagram showing relationships between tasks (flow chart).',
        'apply_filter' => 'Apply filter',
        'clear_filter' => 'Clear filter',
        'task_name_col' => 'Task Name',
        'project_name_col' => 'Project',
        'workload_report' => 'Workload Report',
        'total_tasks' => 'Total tasks',
        'due_soon_days' => 'Due soon (≤ 3 days)',
        'no_workload' => 'No workload data found.',

        // Buttons
        'edit_user' => 'Edit',
        'delete_user' => 'Delete',

        // New keys for improved workload and due soon charts
        // Hint displayed above the workload chart to explain stacked bars
        'workload_chart_hint' => 'The chart below shows each member’s workload broken down by status. Stacked bars help you compare total tasks and distribution.',
        // Hint for the due soon tasks chart explaining its time window
        'due_soon_chart_hint' => 'Number of tasks due in the next 7 days (including today).',
        // Title for the chart showing tasks due soon
        // Title for the chart showing number of tasks due by date
        'due_soon_tasks_chart' => 'Number of tasks due by date',
        // Axis title indicating number of tasks
        'number_of_tasks' => 'Number of tasks',
        // Label for tasks that are due (used in chart legend)
        'due_tasks' => 'Due tasks',
        // Generic label for the date axis in charts
        'date' => 'Date',

        // Analysis of completed tasks relative to due dates
        'done_early' => 'Completed early',
        'done_on_time' => 'Completed on time',
        'done_late' => 'Completed late',
        'done_due_analysis_title' => 'Completed tasks relative to due date',
        'done_due_analysis_hint' => 'Classify completed tasks based on due date relative to today.',

        // Confirmation messages
        'confirm_delete_subtasks' => 'I understand this will delete all subtasks (if any).',

        // Permission management
        'permissions' => 'Permissions',
        // Permission to create a new project and edit one’s own projects
        'perm_create_project' => 'Can create/edit own projects',
        // Permission to edit projects created by other users. Deleting projects remains an admin-only operation.
        'perm_edit_project' => 'Can edit other users\' projects',
        'perm_delete_project' => 'Can delete projects',
        // Clarify that these apply to tasks created by other users.  All users
        // may always edit or delete their own tasks; these permissions cover
        // tasks assigned to or created by someone else.
        'perm_edit_task' => 'Can edit tasks created by others',
        'perm_delete_task' => 'Can delete tasks created by others',
        'project_access' => 'Project access',
        'save_permissions' => 'Save permissions',
        'no_permission' => 'You do not have permission to perform this action.',
        'no_projects' => 'No projects available.',

        // Project membership management
        'project_members' => 'Project members',
        'all_users' => 'All users',
        'select_members_hint' => 'Select users who can access this project. Choosing All will add all non-admin users.',
        'member_count' => 'Members',

        // Tag page
        'tags_page_title' => 'All Tags',
        'tags_page_description' => 'List of all tags used in the system along with the number of tasks tagged.',
        'no_tags' => 'No tags found.',
        'tag_singular' => 'Tag',
        'task_count' => 'Task count',
        'in_project' => 'In project',
        'tag_instructions' => 'To see tasks by tag, use the Tag filter in the List view of the corresponding project.',

        // Misc generic labels
        'description' => 'Description',
        'subtasks' => 'Subtasks',

        // Toggle subtasks in Kanban
        'toggle_subtasks' => 'Toggle subtasks',

        // List view grouping
        'none' => 'None',
        'unassigned' => 'Unassigned',
        'no_due_date' => 'No due date',
        'no_tags' => 'No tags',

        // Profile page
        'profile_title' => 'Profile',
        'user_info' => 'User information',
        'full_name' => 'Full name',
        'email' => 'Email',
        'change_password' => 'Change password',
        'my_tasks' => 'My tasks',
        'no_assigned_tasks' => 'You are not assigned to any tasks.',
        'my_comments' => 'My comments',
        'no_comments' => 'No comments.',
        'my_attachments' => 'My attachments',
        'no_attachments' => 'No attachments.',

        // Profile summary and notifications enhancements
        // Summary card heading on profile page
        'task_summary' => 'Task summary',
        // Label for tasks due today in summary/notifications
        'due_today' => 'Due today',
        // Label for high priority tasks in notifications
        'high_priority' => 'High priority',

        // Dashboard terms
        'task_statistics' => 'Task statistics',
        'total' => 'Total',
        'overdue' => 'Overdue',

        // Kanban grouping labels and parent indicator
        'group_subtasks' => 'Group subtasks',
        'separate_subtasks' => 'Show subtasks separately',
        'parent' => 'Parent task',

        // Trash management
        'trash' => 'Trash',
    'admin_tools' => 'Admin',

        // Task deletion confirmations
        'delete_task' => 'Delete task',
        'delete_task_prompt' => 'Are you sure you want to delete this task?',
        'delete_subtasks_option' => 'Also delete subtasks',
        'delete_task_prompt_with_subtasks' => 'This task has :count subtasks. Deleting will remove all subtasks. Are you sure?',
        'confirm_delete_option' => 'I confirm deleting this task',
        'delete_not_confirmed' => 'You must confirm deletion before proceeding',
        // Notes module
        'notes' => 'Notes',
        'note' => 'Note',
        'create_note' => 'Create Note',
        'edit_note' => 'Edit Note',
        'title' => 'Title',
        'content' => 'Content',
        'author' => 'Author',
        'linked_tasks' => 'Linked tasks',
        'view' => 'View',
        'optional' => 'Optional',
        'select_tasks_hint' => 'Select related tasks (if any).',
        'select_project_for_tasks' => 'Choose a project to display tasks.',
        // Renamed from global to private since only author and admin can view
        'global' => 'Private',
        'scope' => 'Scope',
        'no_notes' => 'No notes.',
        'no_links' => 'No links.',
        'confirm_delete_note' => 'Are you sure you want to delete this note?',

        // Dates
        'created_at' => 'Created at',

        // Note attachment UI
        'select_note' => 'Select note',
        'attach_note' => 'Attach note',
        'select_note_placeholder' => 'Select a note to attach',
        'existing_notes' => 'Existing notes',
        'add' => 'Add',
        // Checklist and links
        'checklist' => 'Checklist',
        'add_checklist_item' => 'Add checklist item',
        'links' => 'Links',
        'link_name' => 'Link name',
        'link_url' => 'Link URL',
        'add_link' => 'Add link',
        'add_note' => 'Add note',
        'no_notes_for_task' => 'No notes for this task.',
    ],
];