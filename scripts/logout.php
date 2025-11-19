<?php
session_start();
session_destroy();
// Redirect to main login page (one level up)
header('Location: ../login.html');
exit();
?>