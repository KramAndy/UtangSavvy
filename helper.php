<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to set a Toastr notification
function setToastr($type, $message, $title = '') {
    $_SESSION['toastr'] = [
        'type' => $type,   // success, error, info, warning
        'message' => $message,
        'title' => $title
    ];
}

// Function to display Toastr (place this near </body> in your HTML)
function showToastr() {
    if (isset($_SESSION['toastr'])) {
        $toast = $_SESSION['toastr'];
        echo "
        <script>
            toastr.options = {
                'closeButton': true,
                'progressBar': true,
                'positionClass': 'toast-top-right',
                'timeOut': '4000'
            };
            toastr['{$toast['type']}']('{$toast['message']}', '{$toast['title']}');
        </script>";
        unset($_SESSION['toastr']);
    }
}
?>
