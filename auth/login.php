<?php
Authorize::forAnonymousOrRedirect();
$title = "Login";
?>
<?php require_once pathOf('/@/includes/header.php') ?>
<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"></h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
          </ol>
        </div>
      </div>
    </div>
  </div>
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 col-12 offset-lg-3 offset-md-3">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Placement Portal Login</h3>
            </div>
            <div class="row p-4">
              <div class="col col-lg-4 offset-lg-4">
                <div id="g_id_onload" data-client_id="<?= Env::getInstance()->googleClientId ?>"
                  data-callback="handleCredentialResponse" data-auto_prompt="false">
                </div>
                <div class="g_id_signin" data-type="standard" data-size="large" data-theme="outline" data-text="sign_in_with"
                  data-shape="rectangular" data-logo_alignment="left">
                </div>
              </div>
            </div>
          </div>
          <div id="message"></div>
        </div>
      </div>
    </div>
  </section>
</div>
<?php require_once pathOf('/@/includes/scripts.php') ?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script src="<?= urlOf('/auth/js/auth.js') ?>"></script>
<?php require_once pathOf('/@/includes/footer.php') ?>