<?php
// logout.php

function logout() {
    session_start();
    session_unset();
    session_destroy();

    // Clear session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    header("Location: /AgeOgram/auth/login.php?logout=1");
    exit();
}

// Run logout if this file is accessed directly
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    logout();
}
?>
