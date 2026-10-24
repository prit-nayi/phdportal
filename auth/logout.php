<?php

Authorize::forAnyRoleOrRedirect();

session_start();
session_destroy();
exitWithRedirect("/");
