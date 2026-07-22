console.log("Exam Shield Script: exam.js loaded successfully!");

document.addEventListener("DOMContentLoaded", function () {
    const startTimeInput = document.getElementById("start_time");
    const endTimeInput = document.getElementById("end_time");
    const durationInput = document.getElementById("duration");

    const partAMarksInput = document.getElementById("part_a_marks");
    const partBMarksInput = document.getElementById("part_b_marks");
    const totalMarksInput = document.getElementById("total_marks");

    function calculateDuration() {
        if (!startTimeInput || !endTimeInput || !durationInput) return;

        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;

        if (!startTime || !endTime) {
            durationInput.value = "";
            return;
        }

        const [startHours, startMinutes] = startTime.split(':').map(Number);
        const [endHours, endMinutes] = endTime.split(':').map(Number);

        let startTotalMinutes = (startHours * 60) + startMinutes;
        let endTotalMinutes = (endHours * 60) + endMinutes;

        if (endTotalMinutes < startTotalMinutes) {
            endTotalMinutes += 24 * 60; 
        }

        durationInput.value = endTotalMinutes - startTotalMinutes;
    }

    function calculateTotalMarks() {
        if (!partAMarksInput || !partBMarksInput || !totalMarksInput) return;

        const partAMarks = parseInt(partAMarksInput.value) || 0;
        const partBMarks = parseInt(partBMarksInput.value) || 0;

        totalMarksInput.value = partAMarks + partBMarks;
    }

    // Listen to BOTH 'input' and 'change' for instant real-time calculation
    if (startTimeInput && endTimeInput) {
        ['input', 'change'].forEach(eventType => {
            startTimeInput.addEventListener(eventType, calculateDuration);
            endTimeInput.addEventListener(eventType, calculateDuration);
        });
    }

    if (partAMarksInput && partBMarksInput) {
        ['input', 'change'].forEach(eventType => {
            partAMarksInput.addEventListener(eventType, calculateTotalMarks);
            partBMarksInput.addEventListener(eventType, calculateTotalMarks);
        });
    }

    // Initial check on load
    calculateDuration();
    calculateTotalMarks();
});