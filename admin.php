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
| Dashboard Filters
|--------------------------------------------------------------------------
*/

$status_filter = trim($_GET["status"] ?? "");
$category_filter = trim($_GET["category"] ?? "");
$search = trim($_GET["search"] ?? "");


/*
|--------------------------------------------------------------------------
| Allowed Values
|--------------------------------------------------------------------------
*/

$allowed_statuses = [
    "Pending",
    "Under Review",
    "Accepted",
    "Rejected",
    "Implemented"
];

$allowed_categories = [
    "Academic",
    "Infrastructure",
    "Faculty",
    "Administration",
    "Student Services",
    "Campus Life",
    "Other"
];


/*
|--------------------------------------------------------------------------
| Build Suggestion Query
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        suggestion_id,
        category,
        suggestion,
        status,
        admin_response,
        created_at,
        updated_at
    FROM suggestions
    WHERE 1=1
";

$params = [];
$types = "";


/*
|--------------------------------------------------------------------------
| Status Filter
|--------------------------------------------------------------------------
*/

if (
    $status_filter !== "" &&
    in_array($status_filter, $allowed_statuses, true)
) {

    $sql .= " AND status = ?";

    $params[] = $status_filter;
    $types .= "s";
}


/*
|--------------------------------------------------------------------------
| Category Filter
|--------------------------------------------------------------------------
*/

if (
    $category_filter !== "" &&
    in_array($category_filter, $allowed_categories, true)
) {

    $sql .= " AND category = ?";

    $params[] = $category_filter;
    $types .= "s";
}


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

if ($search !== "") {

    $sql .= "
        AND (
            suggestion_id LIKE ?
            OR suggestion LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ss";
}


/*
|--------------------------------------------------------------------------
| Latest Suggestions First
|--------------------------------------------------------------------------
*/

$sql .= " ORDER BY created_at DESC";


$stmt = $conn->prepare($sql);


/*
|--------------------------------------------------------------------------
| Bind Dynamic Parameters
|--------------------------------------------------------------------------
*/

if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}


$stmt->execute();

$result = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$stats_query = "
    SELECT
        COUNT(*) AS total,
        SUM(status = 'Pending') AS pending,
        SUM(status = 'Under Review') AS under_review,
        SUM(status = 'Accepted') AS accepted,
        SUM(status = 'Implemented') AS implemented,
        SUM(status = 'Rejected') AS rejected
    FROM suggestions
";

$stats_result = $conn->query($stats_query);

$stats = $stats_result->fetch_assoc();


/*
|--------------------------------------------------------------------------
| Helper Function
|--------------------------------------------------------------------------
*/

function getStatusClass($status)
{
    return "status-" . strtolower(
        str_replace(" ", "-", $status)
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Admin Dashboard | Digital Suggestion Box
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<main class="admin-page">

    <div class="admin-container">


        <!-- =====================================================
             HEADER
        ====================================================== -->

        <header class="admin-header">

            <div class="admin-brand">

                <div class="brand-icon">
                    💡
                </div>

                <div>

                    <span class="admin-brand-label">
                        DIGITAL SUGGESTION BOX
                    </span>

                    <h1>
                        Admin Dashboard
                    </h1>

                </div>

            </div>


            <div class="admin-header-right">

                <div class="admin-user">

                    <span class="admin-user-icon">
                        👤
                    </span>

                    <div>

                        <span>
                            Administrator
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $_SESSION["admin_email"]
                            ) ?>
                        </strong>

                    </div>

                </div>


                <a
                    href="logout.php"
                    class="logout-button"
                >
                    Logout
                </a>

            </div>

        </header>


        <!-- =====================================================
             WELCOME
        ====================================================== -->

        <section class="admin-welcome">

            <div>

                <span class="auth-label">
                    MANAGEMENT PANEL
                </span>

                <h2>
                    Manage Student Suggestions
                </h2>

                <p>
                    Review anonymous feedback, update its status,
                    and provide responses to students.
                </p>

            </div>

            <div class="anonymous-badge">
                🔒 Anonymous Submissions
            </div>

        </section>


        <!-- =====================================================
             STATISTICS
        ====================================================== -->

        <section class="stats-grid">

            <div class="stat-card">

                <span class="stat-icon">
                    📋
                </span>

                <div>

                    <span>
                        Total Suggestions
                    </span>

                    <strong>
                        <?= (int) $stats["total"] ?>
                    </strong>

                </div>

            </div>


            <div class="stat-card">

                <span class="stat-icon">
                    🕐
                </span>

                <div>

                    <span>
                        Pending
                    </span>

                    <strong>
                        <?= (int) $stats["pending"] ?>
                    </strong>

                </div>

            </div>


            <div class="stat-card">

                <span class="stat-icon">
                    🔍
                </span>

                <div>

                    <span>
                        Under Review
                    </span>

                    <strong>
                        <?= (int) $stats["under_review"] ?>
                    </strong>

                </div>

            </div>


            <div class="stat-card">

                <span class="stat-icon">
                    ✓
                </span>

                <div>

                    <span>
                        Implemented
                    </span>

                    <strong>
                        <?= (int) $stats["implemented"] ?>
                    </strong>

                </div>

            </div>

        </section>


        <!-- =====================================================
             FILTERS
        ====================================================== -->

        <section class="admin-filter-card">

            <form
                method="GET"
                action=""
                class="filter-form"
            >

                <div class="filter-search">

                    <label for="search">
                        Search
                    </label>

                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Search by Suggestion ID or text..."
                    >

                </div>


                <div class="filter-field">

                    <label for="status">
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
                    >

                        <option value="">
                            All Statuses
                        </option>

                        <?php foreach ($allowed_statuses as $status): ?>

                            <option
                                value="<?= htmlspecialchars($status) ?>"
                                <?= $status_filter === $status
                                    ? "selected"
                                    : "" ?>
                            >
                                <?= htmlspecialchars($status) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="filter-field">

                    <label for="category">
                        Category
                    </label>

                    <select
                        id="category"
                        name="category"
                    >

                        <option value="">
                            All Categories
                        </option>

                        <?php foreach ($allowed_categories as $category): ?>

                            <option
                                value="<?= htmlspecialchars($category) ?>"
                                <?= $category_filter === $category
                                    ? "selected"
                                    : "" ?>
                            >
                                <?= htmlspecialchars($category) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <button
                    type="submit"
                    class="filter-button"
                >
                    Apply Filters
                </button>


                <?php if (
                    $search !== "" ||
                    $status_filter !== "" ||
                    $category_filter !== ""
                ): ?>

                    <a
                        href="admin.php"
                        class="clear-filter-button"
                    >
                        Clear
                    </a>

                <?php endif; ?>

            </form>

        </section>


        <!-- =====================================================
             SUGGESTIONS
        ====================================================== -->

        <section class="suggestions-section">

            <div class="section-heading">

                <div>

                    <span class="auth-label">
                        SUBMISSIONS
                    </span>

                    <h2>
                        Student Suggestions
                    </h2>

                </div>

                <span class="result-count">
                    <?= $result->num_rows ?> result(s)
                </span>

            </div>


            <?php if ($result->num_rows > 0): ?>

                <div class="suggestions-list">

                    <?php while (
                        $row = $result->fetch_assoc()
                    ): ?>

                        <article class="admin-suggestion-card">


                            <!-- Suggestion Header -->

                            <div class="suggestion-card-top">

                                <div>

                                    <span class="suggestion-id-label">
                                        SUGGESTION ID
                                    </span>

                                    <strong class="suggestion-id">
                                        <?= htmlspecialchars(
                                            $row["suggestion_id"]
                                        ) ?>
                                    </strong>

                                </div>


                                <span
                                    class="status-badge <?= getStatusClass(
                                        $row["status"]
                                    ) ?>"
                                >
                                    <?= htmlspecialchars(
                                        $row["status"]
                                    ) ?>
                                </span>

                            </div>


                            <!-- Suggestion Meta -->

                            <div class="suggestion-meta">

                                <span>
                                    📁
                                    <?= htmlspecialchars(
                                        $row["category"]
                                    ) ?>
                                </span>

                                <span>
                                    🕐
                                    <?= date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $row["created_at"]
                                        )
                                    ) ?>
                                </span>

                            </div>


                            <!-- Suggestion Text -->

                            <div class="admin-suggestion-content">

                                <h3>
                                    Student Suggestion
                                </h3>

                                <p>
                                    <?= nl2br(
                                        htmlspecialchars(
                                            $row["suggestion"]
                                        )
                                    ) ?>
                                </p>

                            </div>


                            <!-- Admin Response -->

                            <div class="existing-response">

                                <h3>
                                    Admin Response
                                </h3>

                                <?php if (
                                    !empty(
                                        $row["admin_response"]
                                    )
                                ): ?>

                                    <p>
                                        <?= nl2br(
                                            htmlspecialchars(
                                                $row["admin_response"]
                                            )
                                        ) ?>
                                    </p>

                                <?php else: ?>

                                    <p class="no-response">
                                        No response added yet.
                                    </p>

                                <?php endif; ?>

                            </div>


                            <!-- Update Form -->

                            <form
                                method="POST"
                                action="update_suggestion.php"
                                class="update-suggestion-form"
                            >

                                <input
                                    type="hidden"
                                    name="suggestion_id"
                                    value="<?= htmlspecialchars(
                                        $row["suggestion_id"]
                                    ) ?>"
                                >


                                <div class="update-fields">

                                    <div class="filter-field">

                                        <label>
                                            Update Status
                                        </label>

                                        <select
                                            name="status"
                                            required
                                        >

                                            <?php foreach (
                                                $allowed_statuses
                                                as $status
                                            ): ?>

                                                <option
                                                    value="<?= htmlspecialchars(
                                                        $status
                                                    ) ?>"
                                                    <?= $row["status"] === $status
                                                        ? "selected"
                                                        : "" ?>
                                                >
                                                    <?= htmlspecialchars(
                                                        $status
                                                    ) ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>


                                    <div class="response-field">

                                        <label>
                                            Admin Response
                                        </label>

                                        <textarea
                                            name="admin_response"
                                            rows="3"
                                            maxlength="5000"
                                            placeholder="Write a response for the student..."
                                        ><?= htmlspecialchars(
                                            $row["admin_response"] ?? ""
                                        ) ?></textarea>

                                    </div>

                                </div>


                                <div class="update-form-bottom">

                                    <span>
                                        Last updated:
                                        <?= date(
                                            "d M Y, h:i A",
                                            strtotime(
                                                $row["updated_at"]
                                            )
                                        ) ?>
                                    </span>

                                    <button
                                        type="submit"
                                        class="update-button"
                                    >
                                        Save Changes
                                        <span>✓</span>
                                    </button>

                                </div>

                            </form>

                        </article>

                    <?php endwhile; ?>

                </div>


            <?php else: ?>

                <div class="empty-state">

                    <div class="empty-icon">
                        📭
                    </div>

                    <h3>
                        No Suggestions Found
                    </h3>

                    <p>
                        There are no suggestions matching
                        your current filters.
                    </p>

                    <?php if (
                        $search !== "" ||
                        $status_filter !== "" ||
                        $category_filter !== ""
                    ): ?>

                        <a
                            href="admin.php"
                            class="secondary-button"
                        >
                            Clear Filters
                        </a>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

        </section>


        <!-- =====================================================
             PRIVACY NOTICE
        ====================================================== -->

        <div class="admin-privacy-note">

            <span>
                🔒
            </span>

            <p>
                All student suggestions are anonymous.
                No student identity information is collected,
                displayed, or stored in this system.
            </p>

        </div>

    </div>

</main>


<script src="js/script.js"></script>

</body>

</html>