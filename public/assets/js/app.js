/*
 * app.js
 *
 * Contains JavaScript logic for interactive features such as the Kanban
 * board drag & drop behaviour and AJAX order updates. This script
 * deliberately avoids external dependencies to keep the application
 * lightweight and self contained.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialise Kanban drag & drop if present on the page
    const board = document.querySelector('.kanban-board');
    if (board) {
        initKanban(board);
    }

    // Sidebar collapse toggle: clicking the toggle button will add/remove
    // the `.collapsed` class on the sidebar, hiding it on smaller screens.
    const toggleBtn = document.getElementById('sidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                const isCollapsed = sidebar.classList.toggle('collapsed');
                // Persist state via AJAX so the server remembers the preference
                fetch('index.php?controller=ui&action=setSidebar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'state=' + (isCollapsed ? 'collapsed' : 'expanded')
                }).catch(() => {
                    // Ignore errors; UI state persistence is non-critical
                });
            }
        });
    }
});

/**
 * Show a deletion confirmation dialog. If the task contains subtasks,
 * the message will warn the user that deleting the task will also
 * remove all of its subtasks. Returns true if the user confirms,
 * false otherwise.
 *
 * @param {number} subtaskCount
 */
function confirmDeleteTask(subtaskCount) {
    let message = 'Bạn có chắc muốn xóa task này?';
    if (subtaskCount && subtaskCount > 0) {
        message += '\nTask này có ' + subtaskCount + ' subtask. Hành động này sẽ xóa tất cả subtask.';
    }
    return window.confirm(message);
}

/**
 * Enable drag & drop on a Kanban board element.
 *
 * @param {HTMLElement} board
 */
function initKanban(board) {
    let draggedItem = null;
    const columns = board.querySelectorAll('.kanban-column');
    columns.forEach(column => {
        const itemsContainer = column.querySelector('.kanban-items');
        itemsContainer.addEventListener('dragover', evt => {
            evt.preventDefault();
            const dragging = document.querySelector('.dragging');
            const afterElement = getDragAfterElement(itemsContainer, evt.clientY);
            if (afterElement == null) {
                itemsContainer.appendChild(dragging);
            } else {
                itemsContainer.insertBefore(dragging, afterElement);
            }
        });
        itemsContainer.addEventListener('drop', evt => {
            evt.preventDefault();
            updateTaskOrder(board);
            // After updating the server with the new order, refresh styles for overdue/due soon
            updateKanbanItemStyles(board);
        });
    });
    // Only top‑level tasks should be draggable. Subtasks (marked with data‑subtask="true")
    // will not be given the draggable attribute and therefore cannot be moved.
    // Determine current grouping mode from a data attribute on the board. In
    // flat mode subtasks should be draggable just like top‑level tasks. In
    // nested mode we prevent dragging subtasks to avoid accidentally moving
    // them independently of their parent.
    const groupMode = board.getAttribute('data-group-mode') || 'nested';
    const items = board.querySelectorAll('.kanban-item');
    items.forEach(item => {
        const isSubtask = item.getAttribute('data-subtask') === 'true';
        // Allow drag if this is not a subtask or if we are in flat grouping
        // mode. Otherwise leave the item non-draggable.
        if (!isSubtask || groupMode === 'flat') {
            item.setAttribute('draggable', 'true');
            item.addEventListener('dragstart', evt => {
                draggedItem = item;
                item.classList.add('dragging');
            });
            item.addEventListener('dragend', evt => {
                item.classList.remove('dragging');
                draggedItem = null;
            });
        }
    });

    // Initialise colour coding for items on load.  This will ensure tasks
    // dropped into a column prior to the page load (e.g. server‑rendered) get
    // appropriate overdue/due soon highlighting based on their current
    // status and due date.
    updateKanbanItemStyles(board);
}

/**
 * Update background/border styles on Kanban items based on due dates and
 * statuses. Items in the 'done' column should have no overdue/due soon
 * highlight. Items in other columns should reflect overdue (due date
 * earlier than today) or due soon (due date within the next 3 days).
 *
 * @param {HTMLElement} board The kanban board root element.
 */
function updateKanbanItemStyles(board) {
    const today = new Date();
    // Format into yyyy-mm-dd for comparison
    const todayStr = today.toISOString().slice(0, 10);
    const soonLimit = new Date(today.getTime() + 3 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);
    board.querySelectorAll('.kanban-item').forEach(item => {
        const due = item.getAttribute('data-due-date');
        // Determine current status from column dataset
        const column = item.closest('.kanban-column');
        const status = column ? column.getAttribute('data-status') : null;
        // Skip resetting styles on items flagged with data-warning (completed tasks with pending subtasks)
        if (item.getAttribute('data-warning') === '1') {
            return;
        }
        // Reset styles to default
        item.style.backgroundColor = '';
        item.style.border = '';
        // For non-done items, apply overdue or due soon colours
        if (status !== 'done') {
            if (due && due !== '' && due < todayStr) {
                // Overdue: red border and pastel red background
                item.style.border = '2px solid #b91c1c';
                item.style.backgroundColor = 'var(--status-overdue)';
            } else if (due && due !== '' && due >= todayStr && due <= soonLimit) {
                // Due soon: yellow border and pastel yellow background
                item.style.border = '2px solid #ca8a04';
                item.style.backgroundColor = 'var(--status-due-soon)';
            }
        }
    });
}

/**
 * Get the element after which the dragged element should be inserted.
 *
 * @param {HTMLElement} container
 * @param {number} y
 */
function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.kanban-item:not(.dragging)')];
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

/**
 * Collect current task order and send it to the server.
 * The server expects an object of the form {status: [ids], ...}.
 *
 * @param {HTMLElement} board
 */
function updateTaskOrder(board) {
    const order = {};
    const columns = board.querySelectorAll('.kanban-column');
    columns.forEach(column => {
        const status = column.dataset.status;
        const ids = [];
        column.querySelectorAll('.kanban-item').forEach(item => {
            ids.push(item.dataset.id);
        });
        order[status] = ids;
    });
    // Send to server via fetch
    fetch('index.php?controller=task&action=order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(order),
    }).then(res => res.json()).then(data => {
        // Show a temporary notification when the order update completes.
        const notif = document.createElement('div');
        notif.className = 'toast-notif';
        notif.textContent = 'Đã lưu cập nhật';
        document.body.appendChild(notif);
        setTimeout(() => {
            notif.classList.add('show');
        }, 10);
        // Remove after 2 seconds
        setTimeout(() => {
            notif.classList.remove('show');
            notif.addEventListener('transitionend', () => notif.remove(), { once: true });
        }, 2000);
    });
}