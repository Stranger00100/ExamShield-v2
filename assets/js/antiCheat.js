/**
 * Exam Shield Anti-Cheat & Clipboard Enforcement Engine
 */
(function() {
    if (typeof STUDENT_EXAM_ID === 'undefined' || !STUDENT_EXAM_ID) {
        return;
    }

    function logProctorEvent(eventType, eventDetails = '') {
        fetch('../api/activity_log.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                student_exam_id: STUDENT_EXAM_ID,
                event_type: eventType,
                details: eventDetails
            })
        }).catch(() => {});
    }

    // 1. Block Copy (Ctrl+C / Cmd+C)
    document.addEventListener('copy', (e) => {
        e.preventDefault();
        logProctorEvent('copy_attempt', 'Attempted to copy content from the examination.');
    }, true);

    // 2. Block Paste (Ctrl+V / Cmd+V)
    document.addEventListener('paste', (e) => {
        e.preventDefault();
        logProctorEvent('paste_attempt', 'Attempted to paste outside text into the workspace.');
    }, true);

    // 3. Block Cut (Ctrl+X / Cmd+X)
    document.addEventListener('cut', (e) => {
        e.preventDefault();
        logProctorEvent('cut_attempt', 'Attempted clipboard cut action.');
    }, true);

    // 4. Disable Right-Click Context Menu
    document.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        logProctorEvent('right_click', 'Attempted to open context menu.');
    }, true);

    // 5. Detect Tab Blur / Navigation Away
    window.addEventListener('blur', () => {
        logProctorEvent('tab_switch', 'Student navigated away or blurred the exam window.');
    });

    // 6. Detect Fullscreen Exits
    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) {
            logProctorEvent('fullscreen_exit', 'Exited fullscreen examination mode.');
        }
    });
})();