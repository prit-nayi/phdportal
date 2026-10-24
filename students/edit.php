<?php

$title = "Edit Student";
Authorize::forRolesOrRedirect([Role::ADMIN]);

if (!getHasAll(['id'])) {
  exitWithRedirect('/students');
}

$id  = (int) $_GET['id'];
$db  = Database::getInstance();
$rec = $db->get("SELECT * FROM `phdStudents` WHERE `id` = ?", [$id]);
if (!$rec) {
  exitWithRedirect('/students');
}

$batches = $db->getAll("SELECT `id`, `name`, `enrollmentYear` FROM `phdBatches` ORDER BY `enrollmentYear` DESC, `name` ASC");

// Helper: pre-fill a text input value
function v(object $rec, string $field): string
{
  return htmlspecialchars($rec->$field ?? '', ENT_QUOTES);
}

require_once pathOf('/@/includes/header.php');
?>
<div class="content-wrapper">
  <div class="container">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row">&nbsp;</div>
        <div class="row mb-2">
          <div class="col-sm-6"><h1>Edit Student</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="<?= urlOf('/') ?>">Home</a></li>
              <li class="breadcrumb-item"><a href="<?= urlOf('/students') ?>">Students</a></li>
              <li class="breadcrumb-item active">Edit</li>
            </ol>
          </div>
        </div>
        <div class="row">&nbsp;</div>
      </div>
    </section>
    <section class="content">

      <div id="form-alert" style="display:none;"></div>

      <div class="row">
        <div class="col">
          <form id="student-form" onsubmit="editStudent(<?= $id ?>, event);" enctype="multipart/form-data" novalidate>

            <!-- ── Section 1: Application Info ─────────────────────────────── -->
            <div class="card card-outline card-info">
              <div class="card-header"><h3 class="card-title">Application Info</h3></div>
              <div class="card-body">
                <div class="row mt-2">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="batchId">Batch <span class="text-danger">*</span></label>
                      <select class="form-control" id="batchId" name="batchId" required>
                        <option value="">— Select Batch —</option>
                        <?php foreach ($batches as $b): ?>
                          <option value="<?= (int)$b->id ?>" <?= (int)$rec->batchId === (int)$b->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b->name, ENT_QUOTES) ?> (<?= (int)$b->enrollmentYear ?>)
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="ApplicationNo">Application No <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="ApplicationNo" name="ApplicationNo" required value="<?= v($rec,'ApplicationNo') ?>">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="ApplicationDate">Application Date <span class="text-danger">*</span></label>
                      <input type="date" class="form-control" id="ApplicationDate" name="ApplicationDate" required value="<?= v($rec,'ApplicationDate') ?>">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="ProgramName">Program Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="ProgramName" name="ProgramName" required value="<?= v($rec,'ProgramName') ?>">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="InstituteName">Institute Name</label>
                      <input type="text" class="form-control" id="InstituteName" name="InstituteName" value="<?= v($rec,'InstituteName') ?>">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="Status">Status <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="Status" name="Status" required value="<?= v($rec,'Status') ?>">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="Graduate">Graduate</label>
                      <input type="text" class="form-control" id="Graduate" name="Graduate" value="<?= v($rec,'Graduate') ?>">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="AreaofResearch">Area of Research <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="AreaofResearch" name="AreaofResearch" required value="<?= v($rec,'AreaofResearch') ?>">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="isEligible">Eligible? <span class="text-danger">*</span></label>
                      <select class="form-control" id="isEligible" name="isEligible" required>
                        <option value="">— Select —</option>
                        <option value="1" <?= $rec->isEligible ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= !$rec->isEligible ? 'selected' : '' ?>>No</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Status flags -->
                <div class="row mt-2">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="isActive">Active?</label>
                      <select class="form-control" id="isActive" name="isActive">
                        <option value="1" <?= $rec->isActive ? 'selected' : '' ?>>Yes</option>
                        <option value="0" <?= !$rec->isActive ? 'selected' : '' ?>>No</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="isCancelled">Cancelled?</label>
                      <select class="form-control" id="isCancelled" name="isCancelled">
                        <option value="0" <?= !$rec->isCancelled ? 'selected' : '' ?>>No</option>
                        <option value="1" <?= $rec->isCancelled ? 'selected' : '' ?>>Yes</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="isTransferred">Transferred?</label>
                      <select class="form-control" id="isTransferred" name="isTransferred">
                        <option value="0" <?= !$rec->isTransferred ? 'selected' : '' ?>>No</option>
                        <option value="1" <?= $rec->isTransferred ? 'selected' : '' ?>>Yes</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="cancelledOrTransferredRemarks">Remarks</label>
                      <input type="text" class="form-control" id="cancelledOrTransferredRemarks" name="cancelledOrTransferredRemarks" value="<?= v($rec,'cancelledOrTransferredRemarks') ?>">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="cancelledOn">Cancelled On</label>
                      <input type="date" class="form-control" id="cancelledOn" name="cancelledOn" value="<?= v($rec,'cancelledOn') ?>">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="transferredOn">Transferred On</label>
                      <input type="date" class="form-control" id="transferredOn" name="transferredOn" value="<?= v($rec,'transferredOn') ?>">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- ── Section 2: Personal Details ─────────────────────────────── -->
            <div class="card card-outline card-info">
              <div class="card-header"><h3 class="card-title">Personal Details</h3></div>
              <div class="card-body">
                <div class="row mt-2">
                  <div class="col-md-4"><div class="form-group"><label for="StudentName">First Name <span class="text-danger">*</span></label><input type="text" class="form-control" id="StudentName" name="StudentName" required value="<?= v($rec,'StudentName') ?>"></div></div>
                  <div class="col-md-4"><div class="form-group"><label for="Surname">Surname <span class="text-danger">*</span></label><input type="text" class="form-control" id="Surname" name="Surname" required value="<?= v($rec,'Surname') ?>"></div></div>
                  <div class="col-md-4"><div class="form-group"><label for="BirthDateStudent">Date of Birth <span class="text-danger">*</span></label><input type="date" class="form-control" id="BirthDateStudent" name="BirthDateStudent" required value="<?= v($rec,'BirthDateStudent') ?>"></div></div>
                </div>
                <div class="row">
                  <div class="col-md-4"><div class="form-group"><label for="FathersName">Father's Name <span class="text-danger">*</span></label><input type="text" class="form-control" id="FathersName" name="FathersName" required value="<?= v($rec,'FathersName') ?>"></div></div>
                  <div class="col-md-4"><div class="form-group"><label for="MothersName">Mother's Name <span class="text-danger">*</span></label><input type="text" class="form-control" id="MothersName" name="MothersName" required value="<?= v($rec,'MothersName') ?>"></div></div>
                  <div class="col-md-4"><div class="form-group"><label for="NameofLocalGuardian">Guardian Name</label><input type="text" class="form-control" id="NameofLocalGuardian" name="NameofLocalGuardian" value="<?= v($rec,'NameofLocalGuardian') ?>"></div></div>
                </div>
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="Sex">Gender <span class="text-danger">*</span></label>
                      <select class="form-control" id="Sex" name="Sex" required>
                        <option value="">— Select —</option>
                        <?php foreach (['Male','Female','Other'] as $opt): ?>
                          <option value="<?= $opt ?>" <?= v($rec,'Sex') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3"><div class="form-group"><label for="Religion">Religion <span class="text-danger">*</span></label><input type="text" class="form-control" id="Religion" name="Religion" required value="<?= v($rec,'Religion') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label for="Caste">Caste <span class="text-danger">*</span></label><input type="text" class="form-control" id="Caste" name="Caste" required value="<?= v($rec,'Caste') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label for="Category">Category <span class="text-danger">*</span></label><input type="text" class="form-control" id="Category" name="Category" required value="<?= v($rec,'Category') ?>"></div></div>
                </div>
                <div class="row">
                  <div class="col-md-3"><div class="form-group"><label for="Minority">Minority <span class="text-danger">*</span></label><input type="text" class="form-control" id="Minority" name="Minority" required value="<?= v($rec,'Minority') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label for="BloodGroup">Blood Group</label><input type="text" class="form-control" id="BloodGroup" name="BloodGroup" value="<?= v($rec,'BloodGroup') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label for="Height_inCms">Height (cm)</label><input type="text" class="form-control" id="Height_inCms" name="Height_inCms" value="<?= v($rec,'Height_inCms') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label for="Weight_inKg">Weight (kg)</label><input type="text" class="form-control" id="Weight_inKg" name="Weight_inKg" value="<?= v($rec,'Weight_inKg') ?>"></div></div>
                </div>
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="NRI">NRI <span class="text-danger">*</span></label>
                      <select class="form-control" id="NRI" name="NRI" required>
                        <option value="">— Select —</option>
                        <?php foreach (['Yes','No'] as $opt): ?>
                          <option value="<?= $opt ?>" <?= v($rec,'NRI') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="English">English Medium <span class="text-danger">*</span></label>
                      <select class="form-control" id="English" name="English" required>
                        <option value="">— Select —</option>
                        <?php foreach (['Yes','No'] as $opt): ?>
                          <option value="<?= $opt ?>" <?= v($rec,'English') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3"><div class="form-group"><label for="HSCStream">HSC Stream <span class="text-danger">*</span></label><input type="text" class="form-control" id="HSCStream" name="HSCStream" required value="<?= v($rec,'HSCStream') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label for="Activities">Extra Activities</label><input type="text" class="form-control" id="Activities" name="Activities" value="<?= v($rec,'Activities') ?>"></div></div>
                </div>
                <div class="row">
                  <div class="col-md-6"><div class="form-group"><label for="PANNoStudent">PAN No (Student)</label><input type="text" class="form-control" id="PANNoStudent" name="PANNoStudent" value="<?= v($rec,'PANNoStudent') ?>"></div></div>
                  <div class="col-md-6"><div class="form-group"><label for="PANNoParent">PAN No (Parent)</label><input type="text" class="form-control" id="PANNoParent" name="PANNoParent" value="<?= v($rec,'PANNoParent') ?>"></div></div>
                </div>
                <div class="row">
                  <div class="col-md-6"><div class="form-group"><label for="AadharNoStudent">Aadhar No (Student)</label><input type="text" class="form-control" id="AadharNoStudent" name="AadharNoStudent" value="<?= v($rec,'AadharNoStudent') ?>"></div></div>
                  <div class="col-md-6"><div class="form-group"><label for="AadharNoParent">Aadhar No (Parent)</label><input type="text" class="form-control" id="AadharNoParent" name="AadharNoParent" value="<?= v($rec,'AadharNoParent') ?>"></div></div>
                </div>
              </div>
            </div>

            <!-- ── Section 3: Contact Details ──────────────────────────────── -->
            <div class="card card-outline card-info">
              <div class="card-header"><h3 class="card-title">Contact Details</h3></div>
              <div class="card-body">
                <div class="row mt-2">
                  <div class="col-md-4"><div class="form-group"><label for="MobileStudent">Mobile (Student) <span class="text-danger">*</span></label><input type="text" class="form-control" id="MobileStudent" name="MobileStudent" required value="<?= v($rec,'MobileStudent') ?>"></div></div>
                  <div class="col-md-4"><div class="form-group"><label for="MobileParent">Mobile (Parent) <span class="text-danger">*</span></label><input type="text" class="form-control" id="MobileParent" name="MobileParent" required value="<?= v($rec,'MobileParent') ?>"></div></div>
                  <div class="col-md-4"><div class="form-group"><label for="MobileGuardian">Mobile (Guardian)</label><input type="text" class="form-control" id="MobileGuardian" name="MobileGuardian" value="<?= v($rec,'MobileGuardian') ?>"></div></div>
                </div>
                <div class="row">
                  <div class="col-md-4"><div class="form-group"><label for="EmailStudent">Email (Student) <span class="text-danger">*</span></label><input type="email" class="form-control" id="EmailStudent" name="EmailStudent" required value="<?= v($rec,'EmailStudent') ?>"></div></div>
                  <div class="col-md-4"><div class="form-group"><label for="EmailParent">Email (Parent)</label><input type="email" class="form-control" id="EmailParent" name="EmailParent" value="<?= v($rec,'EmailParent') ?>"></div></div>
                  <div class="col-md-4"><div class="form-group"><label for="EmailGuardian">Email (Guardian)</label><input type="email" class="form-control" id="EmailGuardian" name="EmailGuardian" value="<?= v($rec,'EmailGuardian') ?>"></div></div>
                </div>
                <div class="row">
                  <div class="col-md-4"><div class="form-group"><label for="LocalPhone">Local Phone <span class="text-danger">*</span></label><input type="text" class="form-control" id="LocalPhone" name="LocalPhone" required value="<?= v($rec,'LocalPhone') ?>"></div></div>
                </div>
                <h6 class="mt-3 mb-2 text-muted">Permanent Address</h6>
                <div class="row">
                  <div class="col-md-6"><div class="form-group"><label for="PermanentAddress">Address <span class="text-danger">*</span></label><textarea class="form-control" id="PermanentAddress" name="PermanentAddress" rows="2" required><?= v($rec,'PermanentAddress') ?></textarea></div></div>
                  <div class="col-md-3"><div class="form-group"><label for="PermanentCity">City <span class="text-danger">*</span></label><input type="text" class="form-control" id="PermanentCity" name="PermanentCity" required value="<?= v($rec,'PermanentCity') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label for="PermanentPinCode">Pin Code <span class="text-danger">*</span></label><input type="text" class="form-control" id="PermanentPinCode" name="PermanentPinCode" required value="<?= v($rec,'PermanentPinCode') ?>"></div></div>
                </div>
                <h6 class="mt-3 mb-2 text-muted">Local Address</h6>
                <div class="row">
                  <div class="col-md-6"><div class="form-group"><label for="LocalAddress">Address <span class="text-danger">*</span></label><textarea class="form-control" id="LocalAddress" name="LocalAddress" rows="2" required><?= v($rec,'LocalAddress') ?></textarea></div></div>
                  <div class="col-md-3"><div class="form-group"><label for="LocalCity">City <span class="text-danger">*</span></label><input type="text" class="form-control" id="LocalCity" name="LocalCity" required value="<?= v($rec,'LocalCity') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label for="LocalPinCode">Pin Code <span class="text-danger">*</span></label><input type="text" class="form-control" id="LocalPinCode" name="LocalPinCode" required value="<?= v($rec,'LocalPinCode') ?>"></div></div>
                </div>
              </div>
            </div>

            <!-- ── Section 4: Academic Records ─────────────────────────────── -->
            <div class="card card-outline card-info">
              <div class="card-header"><h3 class="card-title">Academic Records</h3></div>
              <div class="card-body">
                <?php
                $acadRows = [
                  ['SSC (10th)',          'SSC',         true,  ['Month_Year','BoardName','TotalMarks','ObtainedMarks','Percentage'], null],
                  ['HSC (12th)',          'HSC',         true,  ['Month_Year','BoardName','TotalMarks','ObtainedMarks','Percentage','SeatNo'], null],
                  ['11th',               '11th',        false, ['Month_Year','BoardName','TotalMarks','ObtainedMarks','Percentage'], null],
                  ['UG 5th/7th Sem',     'UG_5th_7th',  true,  ['Month_Year','BoardName','TotalMarks','ObtainedMarks','Percentage','SeatNo'], null],
                  ['UG 6th Sem',         'UG_6th',      false, ['Month_Year','BoardName','TotalMarks','ObtainedMarks','Percentage','SeatNo'], null],
                  ['Post Graduation (PG)','PG',          true,  ['Month_Year','BoardName','TotalMarks','ObtainedMarks','Percentage','SeatNo'], null],
                ];
                $requiredSubs = ['Month_Year','BoardName','TotalMarks','ObtainedMarks','Percentage'];
                foreach ($acadRows as [$label, $prefix, $isRequired, $subs, $_]): ?>
                  <h6 class="mt-3 mb-2 text-info"><?= $label ?></h6>
                  <div class="row">
                    <?php foreach ($subs as $sub):
                      $fieldName = $prefix . '_' . $sub;
                      $req       = $isRequired && in_array($sub, $requiredSubs);
                      $val       = v($rec, $fieldName);
                    ?>
                      <div class="col-md-2">
                        <div class="form-group">
                          <label><?= $sub ?><?= $req ? ' <span class="text-danger">*</span>' : '' ?></label>
                          <input type="text" class="form-control" name="<?= $fieldName ?>" <?= $req ? 'required' : '' ?> value="<?= $val ?>">
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- ── Section 5: Entrance Exams ───────────────────────────────── -->
            <div class="card card-outline card-info">
              <div class="card-header"><h3 class="card-title">Entrance Exam Scores (Optional)</h3></div>
              <div class="card-body">
                <?php
                $exams = [
                  ['CMAT','CMAT'],['CLAT','CLAT'],['CAT','CAT'],['MAT','MAT'],['ATMA','ATMA'],
                  ['UGCNET','UGC NET'],['SLET','SLET'],['UGCJRF','UGC JRF'],['MPhil','M.Phil'],['Others','Others'],
                ];
                foreach ($exams as [$prefix, $label]): ?>
                  <h6 class="mt-3 mb-2 text-info"><?= $label ?></h6>
                  <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>Month/Year</label><input type="text" class="form-control" name="<?= $prefix ?>_Month_Year" value="<?= v($rec,$prefix.'_Month_Year') ?>"></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Obtained Marks</label><input type="text" class="form-control" name="<?= $prefix ?>_ObtainedMarks" value="<?= v($rec,$prefix.'_ObtainedMarks') ?>"></div></div>
                    <div class="col-md-4"><div class="form-group"><label>Percentage</label><input type="text" class="form-control" name="<?= $prefix ?>_Percentage" value="<?= v($rec,$prefix.'_Percentage') ?>"></div></div>
                  </div>
                <?php endforeach; ?>
                <h6 class="mt-3 mb-2 text-info">GUJCET</h6>
                <div class="row">
                  <div class="col-md-3"><div class="form-group"><label>Given?</label><input type="text" class="form-control" name="GUJCET_Given" value="<?= v($rec,'GUJCET_Given') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label>Month/Year</label><input type="text" class="form-control" name="GUJCET_Month_Year" value="<?= v($rec,'GUJCET_Month_Year') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label>Obtained Marks</label><input type="text" class="form-control" name="GUJCET_ObtainedMarks" value="<?= v($rec,'GUJCET_ObtainedMarks') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label>Percentage</label><input type="text" class="form-control" name="GUJCET_Percentage" value="<?= v($rec,'GUJCET_Percentage') ?>"></div></div>
                </div>
                <h6 class="mt-3 mb-2 text-info">JEE</h6>
                <div class="row">
                  <div class="col-md-3"><div class="form-group"><label>Given?</label><input type="text" class="form-control" name="JEE_Given" value="<?= v($rec,'JEE_Given') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label>Month/Year</label><input type="text" class="form-control" name="JEE_Month_Year" value="<?= v($rec,'JEE_Month_Year') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label>Obtained Marks</label><input type="text" class="form-control" name="JEE_ObtainedMarks" value="<?= v($rec,'JEE_ObtainedMarks') ?>"></div></div>
                  <div class="col-md-3"><div class="form-group"><label>Percentage</label><input type="text" class="form-control" name="JEE_Percentage" value="<?= v($rec,'JEE_Percentage') ?>"></div></div>
                </div>
              </div>
            </div>

            <!-- ── Section 6: Documents ─────────────────────────────────────── -->
            <div class="card card-outline card-info">
              <div class="card-header"><h3 class="card-title">Document References</h3></div>
              <div class="card-body">
                <div class="row mt-2">
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
                    $existingFile = v($rec, $field);
                  ?>
                    <div class="col-md-4 mb-3">
                      <div class="form-group mb-0">
                        <label for="<?= $field ?>"><?= $label ?><?= $req ? ' <span class="text-danger">*</span>' : '' ?></label>
                        <?php if ($existingFile): ?>
                          <div class="mb-1">
                            <a href="<?= urlOf('/' . $existingFile) ?>" target="_blank" class="badge badge-info"><i class="fas fa-external-link-alt mr-1"></i> View Current</a>
                          </div>
                        <?php endif; ?>
                        <div class="custom-file">
                          <input type="file" class="custom-file-input" id="<?= $field ?>" name="<?= $field ?>" accept="<?= $accept ?>">
                          <label class="custom-file-label" for="<?= $field ?>">Choose file to replace...</label>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <!-- Audit -->
            <div class="row mb-3">
              <div class="col">
                <small class="text-muted">
                  <i class="fas fa-calendar-plus mr-1"></i>Created: <?= date('d M Y, h:i A', strtotime($rec->createdAt)) ?>
                  &nbsp;&nbsp;
                  <i class="fas fa-calendar-check mr-1"></i>Last Updated: <?= date('d M Y, h:i A', strtotime($rec->updatedAt)) ?>
                </small>
              </div>
            </div>

            <!-- ── Submit ──────────────────────────────────────────────────── -->
            <div class="card-footer bg-white border-top">
              <button type="submit" class="btn btn-success btn-submit">
                <span class="btn-submit-text">Submit</span>
                <span class="btn-submit-text-saved" style="display:none;">Updated!</span>
                <div class="btn-submit-spinner spinner-border spinner-border-sm" role="status" style="display:none;">
                  <span class="sr-only">Loading...</span>
                </div>
              </button>
            </div>

          </form>
        </div>
      </div>
    </section>
  </div>
</div>
<?php require_once pathOf('/@/includes/scripts.php'); ?>
<script src="<?= urlOf('/students/js/students.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php'); ?>
