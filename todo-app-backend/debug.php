<?php
session_start();

// $_SESSION['user'] = 1;
if (!is_writable(session_save_path())) {
    echo 'Session path "' . session_save_path() . '" is not writable for PHP!';
} else {
    if (session_status() === PHP_SESSION_NONE) {
        echo 'Session has not started or has been closed.';
    } elseif (session_status() === PHP_SESSION_DISABLED) {
        echo 'Sessions are disabled.';
    } elseif (session_status() === PHP_SESSION_ACTIVE) {
        echo 'Session is active and user_id is set.';

        var_dump($_SESSION['user']);
        error_log($_SESSION['user']);
    }
}
