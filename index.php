<?php
require_once 'functions.php';

checkRememberMe();

if (isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
