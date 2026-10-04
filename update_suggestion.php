<?php

session_start();

require_once "db.php";


/*
|--------------------------------------------------------------------------
| Admin Authentication Check
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["admin_id"]) ||
    !isset($_SESSION["admin_email"])
) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Only POST Requests Allowed
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$suggestion_id = strtoupper(
    trim($_POST["suggestion_id"] ?? "")
);

$status = trim(
    $_POST["status"] ?? ""
);

$admin_response = trim(
    $_POST["admin_response"] ?? ""
);


/*
|--------------------------------------------------------------------------
| Allowed Statuses
|--------------------------------------------------------------------------
*/

$allowed_statuses = [
    "Pending",
    "Under Review",
    "Accepted",
    "Rejected",
    "Implemented"
];


/*
|--------------------------------------------------------------------------
| Validate Suggestion ID
|--------------------------------------------------------------------------
*/

if (
    $suggestion_id === "" ||
    !preg_match(
        '/^DSB-\d{4}-[A-F0-9]{8}$/',
        $suggestion_id
    )
) {
    header("Location: admin.php?error=invalid");
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate Status
|--------------------------------------------------------------------------
*/

if (
    $status === "" ||
    !in_array($status, $allowed_statuses, true)
) {
    header(
        "Location: admin.php?error=invalid_status"
    );
    exit;
}


/*
|--------------------------------------------------------------------------
| Validate Admin Response
|--------------------------------------------------------------------------
*/

if (mb_strlen($admin_response) > 5000) {

    header(
        "Location: admin.php?error=response_too_long"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Check Suggestion Exists
|--------------------------------------------------------------------------
*/

$check = $conn->prepare(
    "SELECT id
     FROM suggestions
     WHERE suggestion_id = ?
     LIMIT 1"
);

$check->bind_param(
    "s",
    $suggestion_id
);

$check->execute();

$result = $check->get_result();

if ($result->num_rows !== 1) {

    $check->close();

    header(
        "Location: admin.php?error=not_found"
    );

    exit;
}

$check->close();


/*
|--------------------------------------------------------------------------
| Update Suggestion
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "UPDATE suggestions
     SET status = ?,
         admin_response = ?
     WHERE suggestion_id = ?
     LIMIT 1"
);

$stmt->bind_param(
    "sss",
    $status,
    $admin_response,
    $suggestion_id
);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: admin.php?updated=1"
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| Update Failed
|--------------------------------------------------------------------------
*/

$stmt->close();

header(
    "Location: admin.php?error=update_failed"
);

exit;

?>