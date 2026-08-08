<?php
// teacher/includes/exam_form.php
// Premium UI Template - Section 1: Basic Details (Fixed IDs for JS)
?>
<style>
    .form-box { margin-top: 25px; }
    .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 20px; }
    .form-group-double { grid-column: span 2; }
    .field-group { display: flex; flex-direction: column; gap: 8px; }
    .field-group label { font-size: 14px; font-weight: 600; color: var(--text); letter-spacing: 0.3px; }
    .input-premium { width: 100%; padding: 14px 18px; border-radius: 12px; border: 1px solid #E2E8F0; font-family: 'Poppins', sans-serif; font-size: 14px; color: var(--text); background: #F8FAFC; outline: none; transition: all 0.3s ease; }
    .input-premium:focus { border-color: var(--primary); background: #FFFFFF; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
    .input-readonly { background: #F1F5F9; color: var(--text-light); border-color: #E2E8F0; cursor: not-allowed; font-weight: 500; }
    .input-total-marks { background: #EEF4FF; color: var(--primary); border-color: rgba(37, 99, 235, 0.2); font-weight: 700; font-size: 16px; }
    .premium-radio-group { display: flex; gap: 16px; margin-top: 4px; }
    .premium-radio-label { display: flex; align-items: center; gap: 10px; padding: 12px 20px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; cursor: pointer; font-size: 14px; font-weight: 500; flex: 1; transition: all 0.25s ease; }
    .premium-radio-label:hover { background: #F1F5F9; border-color: #CBD5E1; }
    .premium-radio-label input[type="radio"] { width: 18px; height: 18px; accent-color: var(--primary); margin: 0; }
    .premium-radio-label:has(input:checked) { background: #EEF4FF; border-color: var(--primary); color: var(--primary); }
    .section-divider { font-size: 16px; font-weight: 600; color: var(--text); padding-bottom: 12px; border-bottom: 2px solid #F1F5F9; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .section-divider svg { color: var(--primary); width: 20px; height: 20px; }
    .action-row { display: flex; justify-content: space-between; align-items: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #E2E8F0; }
    .btn-neutral { display: inline-flex; align-items: center; gap: 8px; background: #FFFFFF; color: var(--text-light); padding: 12px 24px; border-radius: 12px; font-weight: 600; text-decoration: none; border: 1px solid #E2E8F0; cursor: pointer; transition: all 0.25s ease; }
    .btn-neutral:hover { background: #F8FAFC; color: var(--text); border-color: #CBD5E1; }
    .btn-secondary-premium { display: inline-flex; align-items: center; gap: 8px; background: #E2E8F0; color: var(--text); padding: 12px 24px; border-radius: 12px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s ease; }
    .btn-secondary-premium:hover { background: #CBD5E1; transform: translateY(-2px); }
</style>

<form id="createExamWizardForm" method="POST" action="save_exam.php" class="form-box">    <!-- 1️⃣ Section: Exam Information -->
    <div class="section">
        <h4 class="section-divider">
            <i data-lucide="info"></i> Exam Information
        </h4>
        
        <div class="form-row">
            <div class="field-group form-group-double">
                <label for="exam_title">Exam Title <span class="negative">*</span></label>
                <input type="text" id="exam_title" name="exam_title" class="input-premium" placeholder="e.g., Programming in C Lab Examination" required>
            </div>
            
            <div class="field-group">
                <label for="subject">Subject <span class="negative">*</span></label>
                <input type="text" id="subject" name="subject" class="input-premium" placeholder="e.g., Data Structures" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="field-group">
                <label for="semester">Semester <span class="negative">*</span></label>
                <select id="semester" name="semester" class="input-premium" required>
                    <option value="" disabled selected>Select Semester</option>
                    <?php for($i=1; $i<=8; $i++): ?>
                        <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="field-group">
                <label for="academic_year">Academic Year <span class="negative">*</span></label>
                <input type="text" id="academic_year" name="academic_year" class="input-premium" placeholder="e.g., 2026-2027" required>
            </div>

            <div class="field-group">
                <label for="language">Programming Language <span class="negative">*</span></label>
                <select id="language" name="language" class="input-premium" required>
                    <option value="" disabled selected>Select Language</option>
                    <option value="C">C</option>
                    <option value="C++">C++</option>
                    <option value="Java">Java</option>
                    <option value="Python">Python</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="field-group form-group-double">
                <label>Exam Type <span class="negative">*</span></label>
                <div class="premium-radio-group">
                    <label class="premium-radio-label">
                        <input type="radio" id="type_internal" name="exam_type" value="Lab Internal" checked required>
                        <span>Lab Internal</span>
                    </label>
                    <label class="premium-radio-label">
                        <input type="radio" id="type_final" name="exam_type" value="Lab Final">
                        <span>Lab Final</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- 2️⃣ Section: Schedule -->
    <div class="section">
        <h4 class="section-divider">
            <i data-lucide="calendar"></i> Schedule Configuration
        </h4>
        
        <div class="form-row">
            <div class="field-group">
                <label for="exam_date">Exam Date <span class="negative">*</span></label>
                <input type="date" id="exam_date" name="exam_date" class="input-premium" required>
            </div>

            <div class="field-group">
                <label for="start_time">Start Time <span class="negative">*</span></label>
                <input type="time" id="start_time" name="start_time" class="input-premium" required>
            </div>

            <div class="field-group">
                <label for="end_time">End Time <span class="negative">*</span></label>
                <input type="time" id="end_time" name="end_time" class="input-premium" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="field-group">
                <label for="duration">Duration (Minutes)</label>
                <input type="number" id="duration" name="duration" class="input-premium input-readonly" readonly placeholder="Calculated from window">
            </div>

            <div class="field-group form-group-double">
                <label for="max_students">Maximum Student Capacity <span class="negative">*</span></label>
                <input type="number" id="max_students" name="max_students" class="input-premium" placeholder="e.g., 60" min="1" required>
            </div>
        </div>
    </div>

    <!-- 3️⃣ Section: Marks Configuration -->
    <div class="section">
        <h4 class="section-divider">
            <i data-lucide="award"></i> Marks Weights & Rules
        </h4>
        
        <div class="form-row">
            <div class="field-group">
                <label for="part_a_marks">Part A Score Weight <span class="negative">*</span></label>
                <input type="number" id="part_a_marks" name="part_a_marks" value="50" min="0" class="input-premium" required>
            </div>

            <div class="field-group">
                <label for="part_b_marks">Part B Score Weight <span class="negative">*</span></label>
                <input type="number" id="part_b_marks" name="part_b_marks" value="50" min="0" class="input-premium" required>
            </div>

            <div class="field-group">
                <label for="penalty_marks">Question Swap Penalty <span class="negative">*</span></label>
                <input type="number" id="penalty_marks" name="penalty_marks" value="5" min="0" class="input-premium" required>
            </div>

            <div class="field-group">
                <label for="total_marks">Aggregate Total Marks</label>
                <input type="number" id="total_marks" name="total_marks" class="input-premium input-readonly input-total-marks" readonly value="100">
            </div>
        </div>
    </div>

    <!-- 4️⃣ Form Operations Footer -->
    <div class="action-row">
        <a href="dashboard.php" class="btn-neutral">Cancel</a>
        <div style="display: flex; gap: 14px;">
            <button type="submit" name="status" value="Draft" class="btn-secondary-premium">
                <i data-lucide="save"></i> Save Draft
            </button>
            <button type="submit" name="navigate" value="next" class="btn-primary" style="border: none; cursor: pointer;">
                Next Step <i data-lucide="arrow-right"></i>
            </button>
        </div>
    </div>

</form>