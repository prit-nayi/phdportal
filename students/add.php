<?php

$title = "Add Student";
Authorize::forRolesOrRedirect([Role::ADMIN]);

$db      = Database::getInstance();
$batches = $db->getAll("SELECT `id`, `name`, `enrollmentYear` FROM `phdBatches` ORDER BY `enrollmentYear` DESC, `name` ASC");

require_once pathOf('/@/includes/header.php');
?>

<style>
/* ── Student Add Page – Scoped Styles ───────────────────────────────────────── */
.section-card { border-left: 4px solid #17a2b8; margin-bottom: 1.25rem; }
.section-card .card-header { background: #f8f9fa; padding: .6rem 1rem; }
.section-card .card-header h3 { font-size: 1rem; font-weight: 600; color: #343a40; margin: 0; }
.section-card .card-body { padding: 1rem 1.25rem .5rem; }
.sub-section-title { font-size: .82rem; font-weight: 700; color: #17a2b8; text-transform: uppercase;
  letter-spacing: .04em; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin: 1rem 0 .75rem; }
.form-group { margin-bottom: .85rem; }
.form-group label { font-size: .82rem; font-weight: 600; color: #495057; margin-bottom: .25rem; }
.form-control { font-size: .88rem; }
.form-control.is-invalid { border-color: #dc3545; }
.invalid-feedback { font-size: .78rem; }
/* Entry mode switcher */
.entry-mode-switcher { display: flex; gap: .5rem; margin-bottom: 1.25rem; }
.entry-mode-btn { flex: 1; padding: .65rem 1rem; border: 2px solid #dee2e6; border-radius: 6px;
  background: #fff; cursor: pointer; text-align: center; transition: all .15s; font-size: .9rem; }
.entry-mode-btn.active { border-color: #17a2b8; background: #e8f6f9; color: #0c7789; font-weight: 600; }
.entry-mode-btn i { margin-right: .35rem; }
/* CSV panel */
.csv-drop-zone { border: 2px dashed #ced4da; border-radius: 8px; padding: 2.5rem 1.5rem;
  text-align: center; background: #fafafa; transition: border-color .2s, background .2s; cursor: pointer; }
.csv-drop-zone.drag-over { border-color: #17a2b8; background: #e8f6f9; }
.csv-drop-zone i { font-size: 2.5rem; color: #adb5bd; margin-bottom: .75rem; }
.csv-results-table td, .csv-results-table th { font-size: .82rem; vertical-align: middle; }
</style>

<div class="content-wrapper">
  <div class="container">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 mt-2">
          <div class="col-sm-6"><h1 style="font-size:1.5rem;">Add Student</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/students') ?>">Students</a></li>
              <li class="breadcrumb-item active">Add</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">

      <!-- Alert placeholder (single form) -->
      <div id="form-alert" style="display:none;"></div>

      <!-- Entry mode switcher -->
      <div class="entry-mode-switcher">
        <div class="entry-mode-btn active" id="btn-mode-single" onclick="switchMode('single')">
          <i class="fas fa-user-plus"></i> Single Student Entry
        </div>
        <div class="entry-mode-btn" id="btn-mode-bulk" onclick="switchMode('bulk')">
          <i class="fas fa-file-csv"></i> Bulk Upload via CSV
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════════
           PANEL 1 — SINGLE STUDENT FORM
           ═══════════════════════════════════════════════════════════════════ -->
      <div id="panel-single">
        <form id="student-form" onsubmit="addStudent(event);" enctype="multipart/form-data" novalidate>

          <!-- ── 1. Application Info ──────────────────────────────────────── -->
          <div class="card section-card">
            <div class="card-header"><h3>Application Info</h3></div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="batchId">Batch <span class="text-danger">*</span></label>
                    <select class="form-control" id="batchId" name="batchId" required>
                      <option value="">— Select Batch —</option>
                      <?php foreach ($batches as $b): ?>
                        <option value="<?= (int)$b->id ?>"><?= htmlspecialchars($b->name, ENT_QUOTES) ?> (<?= (int)$b->enrollmentYear ?>)</option>
                      <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select a batch.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="ApplicationNo">Application No <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="ApplicationNo" name="ApplicationNo" required placeholder="e.g. PHD2024001">
                    <div class="invalid-feedback">Application No is required.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="ApplicationDate">Application Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="ApplicationDate" name="ApplicationDate" required>
                    <div class="invalid-feedback">Application Date is required.</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="ProgramName">Program Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="ProgramName" name="ProgramName" required placeholder="e.g. PhD Management">
                    <div class="invalid-feedback">Program Name is required.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="InstituteName">Institute Name</label>
                    <input type="text" class="form-control" id="InstituteName" name="InstituteName" placeholder="e.g. FCAIT">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="Status">Status <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="Status" name="Status" required placeholder="e.g. Active">
                    <div class="invalid-feedback">Status is required.</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="Graduate">Graduate</label>
                    <input type="text" class="form-control" id="Graduate" name="Graduate" placeholder="Yes / No">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="AreaofResearch">Area of Research <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="AreaofResearch" name="AreaofResearch" required placeholder="e.g. Machine Learning">
                    <div class="invalid-feedback">Area of Research is required.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="isEligible">Eligible? <span class="text-danger">*</span></label>
                    <select class="form-control" id="isEligible" name="isEligible" required>
                      <option value="">— Select —</option>
                      <option value="1">Yes</option>
                      <option value="0">No</option>
                    </select>
                    <div class="invalid-feedback">Please select eligibility.</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── 2. Personal Details ──────────────────────────────────────── -->
          <div class="card section-card">
            <div class="card-header"><h3>Personal Details</h3></div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="StudentName">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="StudentName" name="StudentName" required placeholder="First Name">
                    <div class="invalid-feedback">First Name is required.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="Surname">Surname <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="Surname" name="Surname" required placeholder="Surname">
                    <div class="invalid-feedback">Surname is required.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="BirthDateStudent">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="BirthDateStudent" name="BirthDateStudent" required>
                    <div class="invalid-feedback">Date of Birth is required.</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="FathersName">Father's Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="FathersName" name="FathersName" required>
                    <div class="invalid-feedback">Father's Name is required.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="MothersName">Mother's Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="MothersName" name="MothersName" required>
                    <div class="invalid-feedback">Mother's Name is required.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="NameofLocalGuardian">Guardian Name</label>
                    <input type="text" class="form-control" id="NameofLocalGuardian" name="NameofLocalGuardian">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="Sex">Gender <span class="text-danger">*</span></label>
                    <select class="form-control" id="Sex" name="Sex" required>
                      <option value="">— Select —</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                      <option value="Other">Other</option>
                    </select>
                    <div class="invalid-feedback">Gender is required.</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="Religion">Religion <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="Religion" name="Religion" required>
                    <div class="invalid-feedback">Religion is required.</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="Caste">Caste <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="Caste" name="Caste" required>
                    <div class="invalid-feedback">Caste is required.</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="Category">Category <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="Category" name="Category" required placeholder="e.g. OBC / GEN">
                    <div class="invalid-feedback">Category is required.</div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="Minority">Minority <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="Minority" name="Minority" required placeholder="Yes / No">
                    <div class="invalid-feedback">Minority is required.</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="BloodGroup">Blood Group</label>
                    <input type="text" class="form-control" id="BloodGroup" name="BloodGroup" placeholder="e.g. O+">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="Height_inCms">Height (cm)</label>
                    <input type="text" class="form-control" id="Height_inCms" name="Height_inCms">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="Weight_inKg">Weight (kg)</label>
                    <input type="text" class="form-control" id="Weight_inKg" name="Weight_inKg">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="NRI">NRI <span class="text-danger">*</span></label>
                    <select class="form-control" id="NRI" name="NRI" required>
                      <option value="">— Select —</option>
                      <option value="Yes">Yes</option>
                      <option value="No">No</option>
                    </select>
                    <div class="invalid-feedback">NRI status is required.</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="English">English Medium <span class="text-danger">*</span></label>
                    <select class="form-control" id="English" name="English" required>
                      <option value="">— Select —</option>
                      <option value="Yes">Yes</option>
                      <option value="No">No</option>
                    </select>
                    <div class="invalid-feedback">English Medium is required.</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="HSCStream">HSC Stream <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="HSCStream" name="HSCStream" required placeholder="e.g. Science">
                    <div class="invalid-feedback">HSC Stream is required.</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="Activities">Extra Activities</label>
                    <input type="text" class="form-control" id="Activities" name="Activities">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="PANNoStudent">PAN No (Student)</label>
                    <input type="text" class="form-control" id="PANNoStudent" name="PANNoStudent">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="PANNoParent">PAN No (Parent)</label>
                    <input type="text" class="form-control" id="PANNoParent" name="PANNoParent">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="AadharNoStudent">Aadhar No (Student)</label>
                    <input type="text" class="form-control" id="AadharNoStudent" name="AadharNoStudent">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="AadharNoParent">Aadhar No (Parent)</label>
                    <input type="text" class="form-control" id="AadharNoParent" name="AadharNoParent">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── 3. Contact Details ───────────────────────────────────────── -->
          <div class="card section-card">
            <div class="card-header"><h3>Contact Details</h3></div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="MobileStudent">Mobile (Student) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="MobileStudent" name="MobileStudent" required placeholder="10-digit mobile">
                    <div class="invalid-feedback">Mobile (Student) is required.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="MobileParent">Mobile (Parent) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="MobileParent" name="MobileParent" required placeholder="10-digit mobile">
                    <div class="invalid-feedback">Mobile (Parent) is required.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="MobileGuardian">Mobile (Guardian)</label>
                    <input type="text" class="form-control" id="MobileGuardian" name="MobileGuardian">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="EmailStudent">Email (Student) <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="EmailStudent" name="EmailStudent" required placeholder="student@email.com">
                    <div class="invalid-feedback">Email (Student) is required.</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="EmailParent">Email (Parent)</label>
                    <input type="email" class="form-control" id="EmailParent" name="EmailParent">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="EmailGuardian">Email (Guardian)</label>
                    <input type="email" class="form-control" id="EmailGuardian" name="EmailGuardian">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="LocalPhone">Local Phone <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="LocalPhone" name="LocalPhone" required>
                    <div class="invalid-feedback">Local Phone is required.</div>
                  </div>
                </div>
              </div>

              <p class="sub-section-title">Permanent Address</p>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="PermanentAddress">Address <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="PermanentAddress" name="PermanentAddress" rows="2" required></textarea>
                    <div class="invalid-feedback">Permanent Address is required.</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="PermanentCity">City <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="PermanentCity" name="PermanentCity" required>
                    <div class="invalid-feedback">City is required.</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="PermanentPinCode">Pin Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="PermanentPinCode" name="PermanentPinCode" required>
                    <div class="invalid-feedback">Pin Code is required.</div>
                  </div>
                </div>
              </div>

              <p class="sub-section-title">Local Address</p>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="LocalAddress">Address <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="LocalAddress" name="LocalAddress" rows="2" required></textarea>
                    <div class="invalid-feedback">Local Address is required.</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="LocalCity">City <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="LocalCity" name="LocalCity" required>
                    <div class="invalid-feedback">City is required.</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="LocalPinCode">Pin Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="LocalPinCode" name="LocalPinCode" required>
                    <div class="invalid-feedback">Pin Code is required.</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── 4. Academic Records ──────────────────────────────────────── -->
          <div class="card section-card">
            <div class="card-header"><h3>Academic Records</h3></div>
            <div class="card-body">

              <p class="sub-section-title">SSC (10th)</p>
              <div class="row">
                <div class="col-md-3"><div class="form-group"><label>Month / Year <span class="text-danger">*</span></label><input type="text" class="form-control" name="SSC_Month_Year" required placeholder="e.g. Mar 2015"><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-3"><div class="form-group"><label>Board <span class="text-danger">*</span></label><input type="text" class="form-control" name="SSC_BoardName" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-2"><div class="form-group"><label>Total Marks <span class="text-danger">*</span></label><input type="text" class="form-control" name="SSC_TotalMarks" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-2"><div class="form-group"><label>Obtained <span class="text-danger">*</span></label><input type="text" class="form-control" name="SSC_ObtainedMarks" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-2"><div class="form-group"><label>Percentage <span class="text-danger">*</span></label><input type="text" class="form-control" name="SSC_Percentage" required><div class="invalid-feedback">Required.</div></div></div>
              </div>

              <p class="sub-section-title">HSC (12th)</p>
              <div class="row">
                <div class="col-md-3"><div class="form-group"><label>Month / Year <span class="text-danger">*</span></label><input type="text" class="form-control" name="HSC_Month_Year" required placeholder="e.g. Mar 2017"><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-3"><div class="form-group"><label>Board <span class="text-danger">*</span></label><input type="text" class="form-control" name="HSC_BoardName" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-2"><div class="form-group"><label>Total Marks <span class="text-danger">*</span></label><input type="text" class="form-control" name="HSC_TotalMarks" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-2"><div class="form-group"><label>Obtained <span class="text-danger">*</span></label><input type="text" class="form-control" name="HSC_ObtainedMarks" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-2"><div class="form-group"><label>Percentage <span class="text-danger">*</span></label><input type="text" class="form-control" name="HSC_Percentage" required><div class="invalid-feedback">Required.</div></div></div>
              </div>
              <div class="row">
                <div class="col-md-3"><div class="form-group"><label>Seat No</label><input type="text" class="form-control" name="HSC_SeatNo"></div></div>
              </div>

              <p class="sub-section-title">11th (if applicable)</p>
              <div class="row">
                <div class="col-md-3"><div class="form-group"><label>Month / Year</label><input type="text" class="form-control" name="11th_Month_Year"></div></div>
                <div class="col-md-3"><div class="form-group"><label>Board</label><input type="text" class="form-control" name="11th_BoardName"></div></div>
                <div class="col-md-2"><div class="form-group"><label>Total Marks</label><input type="text" class="form-control" name="11th_TotalMarks"></div></div>
                <div class="col-md-2"><div class="form-group"><label>Obtained</label><input type="text" class="form-control" name="11th_ObtainedMarks"></div></div>
                <div class="col-md-2"><div class="form-group"><label>Percentage</label><input type="text" class="form-control" name="11th_Percentage"></div></div>
              </div>

              <p class="sub-section-title">UG – 5th / 7th Semester</p>
              <div class="row">
                <div class="col-md-3"><div class="form-group"><label>Month / Year <span class="text-danger">*</span></label><input type="text" class="form-control" name="UG_5th_7th_Month_Year" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-3"><div class="form-group"><label>Board / University <span class="text-danger">*</span></label><input type="text" class="form-control" name="UG_5th_7th_BoardName" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-2"><div class="form-group"><label>Total Marks <span class="text-danger">*</span></label><input type="text" class="form-control" name="UG_5th_7th_TotalMarks" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-2"><div class="form-group"><label>Obtained <span class="text-danger">*</span></label><input type="text" class="form-control" name="UG_5th_7th_ObtainedMarks" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-2"><div class="form-group"><label>Percentage <span class="text-danger">*</span></label><input type="text" class="form-control" name="UG_5th_7th_Percentage" required><div class="invalid-feedback">Required.</div></div></div>
              </div>
              <div class="row">
                <div class="col-md-3"><div class="form-group"><label>Seat No</label><input type="text" class="form-control" name="UG_5th_7th_SeatNo"></div></div>
              </div>

              <p class="sub-section-title">UG – 6th Semester (optional)</p>
              <div class="row">
                <div class="col-md-3"><div class="form-group"><label>Month / Year</label><input type="text" class="form-control" name="UG_6th_Month_Year"></div></div>
                <div class="col-md-3"><div class="form-group"><label>Board / University</label><input type="text" class="form-control" name="UG_6th_BoardName"></div></div>
                <div class="col-md-2"><div class="form-group"><label>Total Marks</label><input type="text" class="form-control" name="UG_6th_TotalMarks"></div></div>
                <div class="col-md-2"><div class="form-group"><label>Obtained</label><input type="text" class="form-control" name="UG_6th_ObtainedMarks"></div></div>
                <div class="col-md-2"><div class="form-group"><label>Percentage</label><input type="text" class="form-control" name="UG_6th_Percentage"></div></div>
              </div>
              <div class="row">
                <div class="col-md-3"><div class="form-group"><label>Seat No</label><input type="text" class="form-control" name="UG_6th_SeatNo"></div></div>
              </div>

              <p class="sub-section-title">Post Graduation (PG)</p>
              <div class="row">
                <div class="col-md-3"><div class="form-group"><label>Month / Year <span class="text-danger">*</span></label><input type="text" class="form-control" name="PG_Month_Year" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-3"><div class="form-group"><label>Board / University <span class="text-danger">*</span></label><input type="text" class="form-control" name="PG_BoardName" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-2"><div class="form-group"><label>Total Marks <span class="text-danger">*</span></label><input type="text" class="form-control" name="PG_TotalMarks" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-2"><div class="form-group"><label>Obtained <span class="text-danger">*</span></label><input type="text" class="form-control" name="PG_ObtainedMarks" required><div class="invalid-feedback">Required.</div></div></div>
                <div class="col-md-2"><div class="form-group"><label>Percentage <span class="text-danger">*</span></label><input type="text" class="form-control" name="PG_Percentage" required><div class="invalid-feedback">Required.</div></div></div>
              </div>
              <div class="row">
                <div class="col-md-3"><div class="form-group"><label>Seat No</label><input type="text" class="form-control" name="PG_SeatNo"></div></div>
              </div>
            </div>
          </div>

          <!-- ── 5. Entrance Exams ────────────────────────────────────────── -->
          <div class="card section-card">
            <div class="card-header"><h3>Entrance Exam Scores <small class="text-muted">(all optional)</small></h3></div>
            <div class="card-body">
              <?php
              $exams = [
                ['CMAT','CMAT'],['CLAT','CLAT'],['CAT','CAT'],['MAT','MAT'],['ATMA','ATMA'],
                ['UGCNET','UGC NET'],['SLET','SLET'],['UGCJRF','UGC JRF'],['MPhil','M.Phil'],['Others','Others'],
              ];
              foreach ($exams as [$prefix, $label]): ?>
                <p class="sub-section-title"><?= $label ?></p>
                <div class="row">
                  <div class="col-md-4"><div class="form-group"><label>Month / Year</label><input type="text" class="form-control" name="<?= $prefix ?>_Month_Year"></div></div>
                  <div class="col-md-4"><div class="form-group"><label>Obtained Marks</label><input type="text" class="form-control" name="<?= $prefix ?>_ObtainedMarks"></div></div>
                  <div class="col-md-4"><div class="form-group"><label>Percentage</label><input type="text" class="form-control" name="<?= $prefix ?>_Percentage"></div></div>
                </div>
              <?php endforeach; ?>

              <p class="sub-section-title">GUJCET</p>
              <div class="row">
                <div class="col-md-3"><div class="form-group"><label>Given?</label><input type="text" class="form-control" name="GUJCET_Given" placeholder="Yes / No"></div></div>
                <div class="col-md-3"><div class="form-group"><label>Month / Year</label><input type="text" class="form-control" name="GUJCET_Month_Year"></div></div>
                <div class="col-md-3"><div class="form-group"><label>Obtained Marks</label><input type="text" class="form-control" name="GUJCET_ObtainedMarks"></div></div>
                <div class="col-md-3"><div class="form-group"><label>Percentage</label><input type="text" class="form-control" name="GUJCET_Percentage"></div></div>
              </div>

              <p class="sub-section-title">JEE</p>
              <div class="row">
                <div class="col-md-3"><div class="form-group"><label>Given?</label><input type="text" class="form-control" name="JEE_Given" placeholder="Yes / No"></div></div>
                <div class="col-md-3"><div class="form-group"><label>Month / Year</label><input type="text" class="form-control" name="JEE_Month_Year"></div></div>
                <div class="col-md-3"><div class="form-group"><label>Obtained Marks</label><input type="text" class="form-control" name="JEE_ObtainedMarks"></div></div>
                <div class="col-md-3"><div class="form-group"><label>Percentage</label><input type="text" class="form-control" name="JEE_Percentage"></div></div>
              </div>
            </div>
          </div>

          <!-- ── 6. Document References ──────────────────────────────────── -->
          <div class="card section-card">
            <div class="card-header"><h3>Document References</h3></div>
            <div class="card-body">
              <p class="text-muted small mb-3"><i class="fas fa-info-circle mr-1"></i>Upload relevant documents.</p>
              <div class="row">
                <?php
                $docs = [
                  ['StudentPhoto','Student Photo', true, '.jpg,.jpeg,.png'],
                  ['SSCMarksheet','SSC Marksheet', true, '.pdf,.jpg,.jpeg,.png'],
                  ['HSCMarksheet','HSC Marksheet', true, '.pdf,.jpg,.jpeg,.png'],
                  ['11thMarksheet','11th Marksheet', true, '.pdf,.jpg,.jpeg,.png'],
                  ['UG_5th_7th_Marksheet','UG 5th/7th Marksheet', true, '.pdf,.jpg,.jpeg,.png'],
                  ['UG_6th_Marksheet','UG 6th Marksheet', true, '.pdf,.jpg,.jpeg,.png'],
                  ['PGMarksheet','PG Marksheet', true, '.pdf,.jpg,.jpeg,.png'],
                  ['PhotoIDProof','Photo ID Proof', true, '.pdf,.jpg,.jpeg,.png'],
                  ['ResidentialProof','Residential Proof', true, '.pdf,.jpg,.jpeg,.png'],
                  ['Payment','Payment Reference', true, '.pdf,.jpg,.jpeg,.png'],
                  ['OfflineCode','Offline Code', false, '.pdf,.jpg,.jpeg,.png'],
                  ['EntranceExamScorecard','Entrance Scorecard', false, '.pdf,.jpg,.jpeg,.png'],
                  ['SchoolLeavingCertificate','School Leaving Cert', false, '.pdf,.jpg,.jpeg,.png'],
                  ['FirstAttemptCertificate','First Attempt Cert', false, '.pdf,.jpg,.jpeg,.png'],
                  ['CasteCertificate','Caste Certificate', false, '.pdf,.jpg,.jpeg,.png'],
                  ['NonCreamyLayerCertificateissuedafter1_4_2018forSEBC','Non-Creamy Layer Cert', false, '.pdf,.jpg,.jpeg,.png'],
                  ['PHCertificate','PH Certificate', false, '.pdf,.jpg,.jpeg,.png'],
                  ['EBCCertificate','EBC Certificate', false, '.pdf,.jpg,.jpeg,.png'],
                  ['GradeConversionPhoto','Grade Conversion Photo', false, '.jpg,.jpeg,.png'],
                ];
                foreach ($docs as [$field, $label, $req, $accept]):
                ?>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="doc_<?= $field ?>"><?= $label ?><?= $req ? ' <span class="text-danger">*</span>' : '' ?></label>
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="doc_<?= $field ?>" name="<?= $field ?>" <?= $req ? 'required' : '' ?> accept="<?= $accept ?>">
                        <label class="custom-file-label" for="doc_<?= $field ?>">Choose file</label>
                      </div>
                      <?php if ($req): ?><div class="invalid-feedback"><?= $label ?> is required.</div><?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Submit -->
          <div class="card card-footer bg-white mb-4" style="border-left:4px solid #28a745;">
            <div class="d-flex align-items-center gap-2">
              <button type="submit" class="btn btn-success btn-lg btn-submit" id="btn-submit-single">
                <i class="fas fa-save mr-1"></i>
                <span class="btn-submit-text">Save Student</span>
                <span class="btn-submit-text-saved" style="display:none;">Saved!</span>
                <span class="btn-submit-spinner spinner-border spinner-border-sm ml-1" role="status" style="display:none;"></span>
              </button>
              <a href="<?= urlOf('/students') ?>" class="btn btn-outline-secondary btn-lg ml-2">
                <i class="fas fa-times mr-1"></i> Cancel
              </a>
            </div>
          </div>

        </form>
      </div><!-- /panel-single -->


      <!-- ═══════════════════════════════════════════════════════════════════
           PANEL 2 — BULK CSV UPLOAD
           ═══════════════════════════════════════════════════════════════════ -->
      <div id="panel-bulk" style="display:none;">

        <!-- Step 1: Upload -->
        <div class="card section-card mb-3">
          <div class="card-header"><h3><i class="fas fa-step-forward mr-1 text-secondary"></i> Step 1 — Upload Your CSV File</h3></div>
          <div class="card-body">
            <div class="csv-drop-zone" id="csv-drop-zone" onclick="document.getElementById('csvFile').click()">
              <i class="fas fa-file-csv d-block"></i>
              <p class="mb-1 font-weight-semibold">Click or drag &amp; drop a CSV file here</p>
              <small class="text-muted">Only <strong>.csv</strong> files accepted. Max 5 MB.</small>
              <input type="file" id="csvFile" name="csvFile" accept=".csv" style="display:none;" onchange="onCsvFileSelected(this)">
            </div>
            <div id="csv-file-name" class="mt-2 text-info" style="display:none;"></div>

            <!-- Progress -->
            <div id="csv-progress-wrap" class="mt-3" style="display:none;">
              <div class="progress">
                <div id="csv-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" style="width:0%"></div>
              </div>
              <small class="text-muted mt-1 d-block" id="csv-progress-label">Preparing upload…</small>
            </div>

            <div class="mt-3">
              <button type="button" class="btn btn-info btn-lg" id="btn-upload-csv" onclick="uploadCSV()" disabled>
                <i class="fas fa-upload mr-1"></i>
                <span id="btn-upload-text">Upload &amp; Import</span>
                <span id="btn-upload-spinner" class="spinner-border spinner-border-sm ml-1" style="display:none;"></span>
              </button>
            </div>
          </div>
        </div>

        <!-- Step 3: Results -->
        <div id="csv-results-panel" style="display:none;">
          <div class="card section-card">
            <div class="card-header"><h3><i class="fas fa-step-forward mr-1 text-secondary"></i> Step 2 — Import Results</h3></div>
            <div class="card-body">
              <!-- Summary badges -->
              <div class="row mb-3" id="csv-summary"></div>
              <!-- Errors table -->
              <div id="csv-errors-wrap" style="display:none;">
                <h6 class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i> Rows with Errors (skipped)</h6>
                <div class="table-responsive">
                  <table class="table table-sm table-bordered csv-results-table">
                    <thead class="thead-light">
                      <tr>
                        <th style="width:8%">Row #</th>
                        <th>Validation Errors</th>
                      </tr>
                    </thead>
                    <tbody id="csv-errors-body"></tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /panel-bulk -->

    </section>
  </div>
</div>

<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/students/js/students.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
