<?php
$env = Env::getInstance();
$organization = "PhD Portal"
  ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($title) ? $title . ' - ' . $organization : $organization ?></title>
  <link rel="shortcut icon" href="<?= urlOf('/@/assets/img/logo.png') ?>" type="image/x-icon">
  <link rel="stylesheet" href="<?= urlOf('/@/assets/css/template/fonts.css') ?>">
  <link rel="stylesheet" href="<?= urlOf('/@/assets/css/template/ionicons.min.css') ?>">
  <link rel="stylesheet" href="<?= urlOf('/@/assets/plugins/fontawesome-free/css/all.min.css') ?>">
  <link rel="stylesheet"
    href="<?= urlOf('/@/assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') ?>">
  <link rel="stylesheet" href="<?= urlOf('/@/assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
  <link rel="stylesheet" href="<?= urlOf('/@/assets/css/template/adminlte.min.css') ?>">
  <link rel="stylesheet" href="<?= urlOf('/@/assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') ?>">
  <link rel="stylesheet" href="<?= urlOf('/@/assets/plugins/daterangepicker/daterangepicker.css') ?>">
  <link rel="stylesheet" href="<?= urlOf('/@/assets/plugins/bootstrap-table/css/bootstrap-table.min.css') ?>">
  <link rel="stylesheet" href="<?= urlOf('/@/assets/plugins/bootstrap-table/css/bootstrap-table-reorder-rows.css') ?>">
  <link rel="stylesheet" href="<?= urlOf('/@/assets/plugins/summernote/summernote-bs4.min.css') ?>">
  <link rel="stylesheet" href="<?= urlOf('/@/assets/css/app.css') ?>?v=<?= time() ?>">

  <script src="<?= urlOf('/@/assets/js/app.js') ?>"></script>
  <script>
    window.__APP__ = {
      baseUrl: "<?= htmlspecialchars($env->baseUrl, ENT_QUOTES) ?>",
    };
  </script>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">
    <?php if (isset($_SESSION['role'])): ?>
      <nav class="main-header navbar navbar-expand navbar-white navbar-light d-sm-none">
        <!-- Left navbar links -->
        <ul class="navbar-nav d-sm-none">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
          </li>
        </ul>
      </nav>
      <aside class="main-sidebar sidebar-dark-primary elevation-4" id="sidebar">
        <div class="sidebar" style="height: 100% !important">
          <div class="user-panel mt-3 pb-3 mb-3 text-center">
            <div class="row">
              <div class="col">
                <a href="<?= urlOf('/') ?>">
                  <img src="<?= urlOf('/@/assets/img/logo.png') ?>" alt="Logo" class="brand-image elevation-3"
                    id="brand-logo">
                  <span class="brand-text font-weight-light"></span>
                </a>
              </div>
            </div>
          </div>
          <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center justify-content-center">
            <div class="info">
              <div class="d-block text-white">Welcome, <?= $_SESSION['name'] ?></div>
            </div>
          </div>
          <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
              <?php if ($_SESSION['role']->value == 'Admin'): ?>
                <li class="nav-header">STAFF MANAGEMENT</li>
                <li class="nav-item">
                  <a href="<?= urlOf('/staff') ?>" class="nav-link <?= isUrlActive(urlOf('/staff')) ?>">
                    <i class="nav-icon fas fa-chalkboard-teacher"></i>
                    <p>Staff List</p>
                  </a>
                </li>

                <li class="nav-header">PHD MANAGEMENT</li>

                <li class="nav-item">
                    <a href="<?= urlOf('/batches') ?>" class="nav-link <?= isUrlActive(urlOf('/batches')) ?>">
                    <i class="nav-icon fas fa-layer-group"></i>
                    <p>Batches</p>
                  </a>
                </li>
                
                <li class="nav-item">
                  <a href="<?= urlOf('/students') ?>" class="nav-link <?= isUrlActive(urlOf('/students')) ?>">
                    <i class="nav-icon fas fa-user-graduate"></i>
                    <p>Students</p>
                  </a>
                </li>
                
                <li class="nav-item">
                  <a href="<?= urlOf('/re-registrations') ?>"
                    class="nav-link <?= isUrlActive(urlOf('/re-registrations')) ?>">
                    <i class="nav-icon fas fa-redo-alt"></i>
                    <p>Re-Registrations</p>
                  </a>
                </li>
                
                <li class="nav-item">
                  <a href="<?= urlOf('/admission-exams') ?>" class="nav-link <?= isUrlActive(urlOf('/admission-exams')) ?>">
                    <i class="nav-icon fas fa-file-signature"></i>
                    <p>Admission Exams</p>
                  </a>
                </li>
                
                <li class="nav-item">
                  <a href="<?= urlOf('/guides') ?>" class="nav-link <?= isUrlActive(urlOf('/guides')) ?>">
                    <i class="nav-icon fas fa-user-tie"></i>
                    <p>Guides</p>
                  </a>
                </li>
                
                <li class="nav-item">
                  <a href="<?= urlOf('/guide-allocation-presentations') ?>" class="nav-link <?= isUrlActive(urlOf('/guide-allocation-presentations')) ?>">
                    <i class="nav-icon fas fa-chalkboard"></i>
                    <p>Presentation Schedule</p>
                  </a>
                </li>
                
                <li class="nav-item">
                  <a href="<?= urlOf('/fee-payments') ?>" class="nav-link <?= isUrlActive(urlOf('/fee-payments')) ?>">
                    <i class="nav-icon fas fa-receipt"></i>
                    <p>Fee Payments</p>
                  </a>
                </li>
            
            
              
                <li class="nav-header">PHD MANAGEMENT</li>
                <li class="nav-item">
                    <a href="<?= urlOf('/batches') ?>" class="nav-link <?= isUrlActive(urlOf('/batches')) ?>">
                    <i class="nav-icon fas fa-layer-group"></i>
                    <p>Batches</p>
                  </a>
                </li>
                
                <li class="nav-item">
                  <a href="<?= urlOf('/students') ?>" class="nav-link <?= isUrlActive(urlOf('/students')) ?>">
                    <i class="nav-icon fas fa-user-graduate"></i>
                    <p>Students</p>
                  </a>
                </li>
                
                <li class="nav-item">
                  <a href="<?= urlOf('/re-registrations') ?>" class="nav-link <?= isUrlActive(urlOf('/re-registrations')) ?>">
                    <i class="nav-icon fas fa-redo-alt"></i>
                    <p>Re-Registrations</p>
                  </a>
                </li>
                
                <li class="nav-item">
                  <a href="<?= urlOf('/admission-exams') ?>" class="nav-link <?= isUrlActive(urlOf('/admission-exams')) ?>">
                    <i class="nav-icon fas fa-file-signature"></i>
                    <p>Admission Exams</p>
                </a>
            </li>

            <li class="nav-item">
              <a href="<?= urlOf('/fee-payments') ?>" class="nav-link <?= isUrlActive(urlOf('/fee-payments')) ?>">
                <i class="nav-icon fas fa-receipt"></i>
                <p>Fee Payments</p>
              </a>
            </li>
            
            
            <li class="nav-item">
                <a href="<?= urlOf('/admission-exam-results') ?>" class="nav-link <?= isUrlActive(urlOf('/admission-exam-results')) ?>">
                    <i class="nav-icon fas fa-poll"></i>
                    <p>Exam Results</p>
                </a>
            </li>

           
              <li class="nav-header">RDC MANAGEMENT</li>
                <li class="nav-item">
                  <a href="<?= urlOf('/rdc') ?>" class="nav-link <?= isUrlActive(urlOf('/rdc')) ?>">
                    <i class="nav-icon fas fa-clipboard-list"></i>
                    <p>RDC List</p>
                  </a>
                </li>
              <?php endif; ?>
              <?php if ($_SESSION['role']->value === 'Student'): ?>
                <li class="nav-item">
                  <a href="<?= urlOf('/dashboard') ?>" class="nav-link <?= isUrlActive(urlOf('/dashboard')) ?>">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                  </a>
                </li>
              <?php endif; ?>
              <li class="nav-header">ACTIONS</li>
              <li class="nav-item">
                <a href="<?= urlOf('/auth/logout') ?>" class="nav-link">
                  <i class="nav-icon fas fa-sign-out-alt"></i>
                  <p>Log out</p>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </aside>
    <?php else: ?>
      <aside class="main-sidebar sidebar-dark-primary elevation-4" id="sidebar">
        <div class="sidebar">
          <div class="user-panel mt-3 pb-3 mb-3 text-center">
            <div class="row">
              <div class="col">
                <a href="<?= urlOf('/') ?>" class="text-center">
                  <img src="<?= urlOf('/@/assets/img/logo.png') ?>" alt="Logo" class="brand-image elevation-3"
                    id="brand-logo">
                  <span class="brand-text font-weight-light"></span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </aside>
    <?php endif; ?>