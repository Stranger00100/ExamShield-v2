/**
 * Exam Shield Proctoring & Activity Monitor
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
        }).catch(() => {
            // Suppress network errors silently so student exam flow isn't interrupted
        });
    }

    // 1. Tab Switch / Window Blur Detection
    window.addEventListener('blur', () => {
        logProctorEvent('tab_switch', 'Student navigated away or blurred the exam window.');
    });

    // 2. Fullscreen Change Detection
    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement) {
            logProctorEvent('fullscreen_exit', 'Student exited fullscreen examination mode.');
        }
    });

    // 3. Clipboard Interception (Copy, Paste, Cut)
    document.addEventListener('copy', (e) => {
        logProctorEvent('copy_attempt', 'Clipboard copy action triggered.');
    });

    document.addEventListener('paste', (e) => {
        logProctorEvent('paste_attempt', 'Clipboard paste action triggered.');
    });

    document.addEventListener('cut', (e) => {
        logProctorEvent('cut_attempt', 'Clipboard cut action triggered.');
    });

    // 4. Right-Click Context Menu Prevention & Log
    document.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        logProctorEvent('right_click', 'Student attempted right-click context menu.');
    });
})();