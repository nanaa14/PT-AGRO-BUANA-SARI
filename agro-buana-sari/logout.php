<?php
session_start(); unset($_SESSION['user_id'],$_SESSION['user_nama'],$_SESSION['user_email']); session_regenerate_id(true); header('Location: index.php'); exit;
?>
