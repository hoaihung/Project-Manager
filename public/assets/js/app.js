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
            // Process status changes asynchronously using modals.  Once
            // resolved, update the server and UI accordingly.  Use async/await
            // here to ensure that subtask completion requests finish before
            // persisting the new order and statuses.
            handleStatusChangesModal(board).then(async toComplete => {
                // Immediately refresh item styles so visual warnings (e.g., dashed border for
                // incomplete subtasks) are applied without waiting for the AJAX calls or
                // server update.  This helps ensure the border colour updates in real time.
                updateKanbanItemStyles(board);

                // Prepare requests to complete subtasks
                const requests = toComplete.map(taskId => {
                    return fetch('index.php?controller=task&action=completeSubtasks', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'task_id=' + encodeURIComponent(taskId)
                    });
                });
                // Wait for all completion requests to finish (ignore errors)
                try {
                    await Promise.all(requests);
                } catch (e) {
                    // ignore any network or server error
                }
                // Persist the new order and statuses to the server.  This call
                // will update status and sort_order for all tasks based on
                // current column positions.
                updateTaskOrder(board);
                // After updating the server with the new order, refresh styles
                updateKanbanItemStyles(board);
            });
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
 * Display a modal prompting the user to confirm moving a completed task
 * back to an active status.  A checkbox must be checked to enable the
 * confirmation button.  Returns a Promise resolved with true if the
 * user confirms, or false if the user cancels.
 *
 * @returns {Promise<boolean>}
 */
function showStatusRevertModal() {
    return new Promise(resolve => {
        const modal = document.getElementById('statusRevertModal');
        const confirmChk = document.getElementById('statusRevertConfirm');
        const okBtn = document.getElementById('statusRevertOk');
        const cancelBtn = document.getElementById('statusRevertCancel');
        // Reset checkbox and disable confirm button
        confirmChk.checked = false;
        okBtn.style.pointerEvents = 'none';
        okBtn.style.opacity = 0.5;
        // Show modal
        modal.style.display = 'flex';
        // Checkbox handler enables confirm button
        const onChkChange = () => {
            if (confirmChk.checked) {
                okBtn.style.pointerEvents = 'auto';
                okBtn.style.opacity = 1;
            } else {
                okBtn.style.pointerEvents = 'none';
                okBtn.style.opacity = 0.5;
            }
        };
        confirmChk.addEventListener('change', onChkChange);
        // Cancel handler
        const onCancel = () => {
            cleanup();
            modal.style.display = 'none';
            resolve(false);
        };
        // Confirm handler
        const onOk = () => {
            cleanup();
            modal.style.display = 'none';
            resolve(true);
        };
        function cleanup() {
            confirmChk.removeEventListener('change', onChkChange);
            cancelBtn.removeEventListener('click', onCancel);
            okBtn.removeEventListener('click', onOk);
        }
        cancelBtn.addEventListener('click', onCancel);
        okBtn.addEventListener('click', onOk);
    });
}

/**
 * Display a modal asking the user whether to mark all incomplete subtasks
 * as done when moving a task into the done column.  Returns a Promise
 * resolved with true if the user chooses to mark all subtasks as done,
 * or false if the user wants to keep their current statuses (and thus
 * highlight the parent task).
 *
 * @param {number} remaining The number of incomplete subtasks
 * @returns {Promise<boolean>}
 */
function showSubtaskCompleteModal(remaining) {
    // Display a modal with a select input and confirm/cancel buttons.  The user
    // chooses between keeping subtask statuses or completing them all.  The
    // function resolves with true when user selects "Chuyển hết sang Hoàn thành"
    // and confirms, false otherwise (including cancel or keep).
    return new Promise(resolve => {
        const modal = document.getElementById('subtaskCompleteModal');
        const messageEl = document.getElementById('subtaskCompleteMessage');
        // Radio options and buttons
        const cancelBtn = document.getElementById('subtaskCompleteCancel');
        const okBtn = document.getElementById('subtaskCompleteOk');
        // Update message
        messageEl.textContent = 'Nhiệm vụ này còn ' + remaining + ' subtask chưa hoàn thành.';
        // Show modal
        modal.style.display = 'flex';
        const onCancel = () => {
            cleanup();
            modal.style.display = 'none';
            resolve(false);
        };
        const onOk = () => {
            // Determine which radio option is selected
            const checked = document.querySelector('input[name="subtaskCompleteOption"]:checked');
            const value = checked ? checked.value : 'keep';
            cleanup();
            modal.style.display = 'none';
            resolve(value === 'all');
        };
        function cleanup() {
            cancelBtn.removeEventListener('click', onCancel);
            okBtn.removeEventListener('click', onOk);
        }
        cancelBtn.addEventListener('click', onCancel);
        okBtn.addEventListener('click', onOk);
    });
}

/**
 * Determine status transitions for tasks after a drop and prompt the user
 * when necessary using modals.  This helper compares each kanban item's
 * previous status (stored in data-current-status) with its new status
 * based on the column it resides in.  If a task is moved into the
 * 'done' column and still has incomplete subtasks, a modal prompts
 * whether to mark all subtasks as done.  If a task is moved out of the
 * 'done' column, a modal with a checkbox asks for confirmation.  The
 * function updates each item's data-current-status accordingly and
 * returns a Promise resolved with an array of task IDs whose subtasks
 * should be marked as done.
 *
 * @param {HTMLElement} board The kanban board root element
 * @returns {Promise<Array<string>>} Promise resolving to an array of task IDs
 */
async function handleStatusChangesModal(board) {
    const completeList = [];
    const items = board.querySelectorAll('.kanban-item');
    for (const item of items) {
        const prevStatus = item.getAttribute('data-current-status');
        const column = item.closest('.kanban-column');
        if (!column) continue;
        const newStatus = column.getAttribute('data-status');
        if (!newStatus || !prevStatus || prevStatus === newStatus) {
            continue;
        }
        const subTotal = parseInt(item.getAttribute('data-subtask-total') || '0', 10);
        const subDone = parseInt(item.getAttribute('data-subtask-done') || '0', 10);
        // Moving into done column with incomplete subtasks
        if (newStatus === 'done' && subTotal > 0 && subDone < subTotal) {
            const remaining = subTotal - subDone;
            const markAll = await showSubtaskCompleteModal(remaining);
            if (markAll) {
                completeList.push(item.dataset.id);
                item.removeAttribute('data-warning');
            } else {
                item.setAttribute('data-warning', '1');
            }
        }
        // Moving out of done column
        if (prevStatus === 'done' && newStatus !== 'done') {
            const confirmRevert = await showStatusRevertModal();
            if (!confirmRevert) {
                // User cancelled: move the item back to its original column
                const originalColumnItems = board.querySelector('.kanban-column[data-status="' + prevStatus + '"] .kanban-items');
                if (originalColumnItems) {
                    originalColumnItems.appendChild(item);
                }
                // Restore current status on item
                item.setAttribute('data-current-status', prevStatus);
                // Skip further updates for this item
                continue;
            } else {
                item.removeAttribute('data-warning');
            }
        }
        // Update cached current status on the item
        item.setAttribute('data-current-status', newStatus);
    }
    return completeList;
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
        // Items flagged with data-warning (completed tasks with pending subtasks)
        // should display a dashed red border via CSS.  Remove any inline
        // styles so the CSS rule can take effect and skip further styling.
        if (item.getAttribute('data-warning') === '1') {
            item.style.backgroundColor = '';
            item.style.border = '';
            return;
        }
        // Reset styles to default before applying due / soon colours
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