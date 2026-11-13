/**
 * Flowzen - Kanban Board Drag-and-Drop & Accessibility Handler
 * Synchronizes task state changes with the PHP backend using real persistence.
 */

document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.kanban-card');
    const columns = document.querySelectorAll('.kanban-cards-wrapper');

    let draggedCard = null;
    let sourceColumn = null;

    cards.forEach(card => {
        card.addEventListener('dragstart', (e) => {
            draggedCard = card;
            sourceColumn = card.parentElement;
            card.classList.add('dragging');
            e.dataTransfer.setData('text/plain', card.getAttribute('data-task-id'));
            e.dataTransfer.effectAllowed = 'move';
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            columns.forEach(col => col.classList.remove('drag-over'));
        });
    });

    columns.forEach(col => {
        col.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            col.classList.add('drag-over');
        });

        col.addEventListener('dragleave', (e) => {
            if (!col.contains(e.relatedTarget)) {
                col.classList.remove('drag-over');
            }
        });

        col.addEventListener('drop', async (e) => {
            e.preventDefault();
            col.classList.remove('drag-over');

            if (!draggedCard) return;

            const targetStatus = col.getAttribute('data-status');
            const currentStatus = draggedCard.getAttribute('data-status');
            const taskId = draggedCard.getAttribute('data-task-id');

            // If dropped in the same column, no update needed
            if (targetStatus === currentStatus) {
                return;
            }

            // Move in UI optimistically
            col.appendChild(draggedCard);
            draggedCard.setAttribute('data-status', targetStatus);
            updateColumnCounts();

            // Persist to backend
            const success = await sendStatusUpdate(taskId, targetStatus);
            if (!success) {
                // Revert on failure
                sourceColumn.appendChild(draggedCard);
                draggedCard.setAttribute('data-status', currentStatus);
                updateColumnCounts();
                alert('Could not update task status. Changes have been reverted.');
            }
        });
    });

    // Accessible select dropdown change
    const statusSelects = document.querySelectorAll('.kanban-accessible-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', async (e) => {
            const card = select.closest('.kanban-card');
            const taskId = card.getAttribute('data-task-id');
            const oldStatus = card.getAttribute('data-status');
            const newStatus = select.value;

            if (newStatus === oldStatus) return;

            const targetCol = document.querySelector(`.kanban-cards-wrapper[data-status="${newStatus}"]`);
            if (targetCol) {
                targetCol.appendChild(card);
                card.setAttribute('data-status', newStatus);
                updateColumnCounts();
            }

            const success = await sendStatusUpdate(taskId, newStatus);
            if (!success) {
                // Revert
                const originalCol = document.querySelector(`.kanban-cards-wrapper[data-status="${oldStatus}"]`);
                if (originalCol) originalCol.appendChild(card);
                card.setAttribute('data-status', oldStatus);
                select.value = oldStatus;
                updateColumnCounts();
                alert('Could not update task status. Reverted to previous state.');
            }
        });
    });

    /**
     * Send status update to backend endpoint
     */
    async function sendStatusUpdate(taskId, newStatus) {
        try {
            const csrfToken = getCsrfToken();
            const response = await fetch(window.FLOWZEN_APP_URL + '/kanban/update-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    task_id: taskId,
                    status: newStatus,
                    csrf_token: csrfToken
                })
            });

            const data = await response.json();
            return data.success === true;
        } catch (err) {
            console.error('Kanban sync error:', err);
            return false;
        }
    }

    /**
     * Recalculate badge counts in column headers
     */
    function updateColumnCounts() {
        ['pending', 'in_progress', 'completed'].forEach(st => {
            const wrapper = document.querySelector(`.kanban-cards-wrapper[data-status="${st}"]`);
            const badge = document.querySelector(`.kanban-count-badge[data-status="${st}"]`);
            if (wrapper && badge) {
                badge.textContent = wrapper.querySelectorAll('.kanban-card').length;
            }
        });
    }
});
