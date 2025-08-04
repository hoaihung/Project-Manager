<h1>Cài đặt giao diện và tham số</h1>
<div class="card">
    <form method="post" action="">
        <div class="form-group">
            <label for="color_primary">Màu chính</label>
            <input type="color" id="color_primary" name="color_primary" value="<?php echo htmlspecialchars($settings['color_primary'] ?? '#3b82f6'); ?>">
        </div>
        <div class="form-group">
            <label for="color_secondary">Màu phụ</label>
            <input type="color" id="color_secondary" name="color_secondary" value="<?php echo htmlspecialchars($settings['color_secondary'] ?? '#64748b'); ?>">
        </div>
        <div class="form-group">
            <label for="color_success">Màu thành công</label>
            <input type="color" id="color_success" name="color_success" value="<?php echo htmlspecialchars($settings['color_success'] ?? '#10b981'); ?>">
        </div>
        <div class="form-group">
            <label for="color_warning">Màu cảnh báo</label>
            <input type="color" id="color_warning" name="color_warning" value="<?php echo htmlspecialchars($settings['color_warning'] ?? '#fbbf24'); ?>">
        </div>
        <div class="form-group">
            <label for="color_danger">Màu nguy hiểm</label>
            <input type="color" id="color_danger" name="color_danger" value="<?php echo htmlspecialchars($settings['color_danger'] ?? '#ef4444'); ?>">
        </div>
        <div class="form-group">
            <label for="overload_threshold">Ngưỡng task quá tải trong 1 ngày (số lượng)</label>
            <input type="number" id="overload_threshold" name="overload_threshold" min="1" value="<?php echo htmlspecialchars($settings['overload_threshold'] ?? '5'); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
    </form>
</div>