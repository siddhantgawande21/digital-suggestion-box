<?php

require_once "db.php";

$suggestion = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $suggestion_id = strtoupper(trim($_POST["suggestion_id"] ?? ""));

    if ($suggestion_id === "") {

        $error = "Please enter your Suggestion ID.";

    } elseif (!preg_match('/^DSB-\d{4}-[A-F0-9]{8}$/', $suggestion_id)) {

        $error = "Please enter a valid Suggestion ID.";

    } else {

        $stmt = $conn->prepare(
            "SELECT
                suggestion_id,
                category,
                suggestion,
                status,
                admin_response,
                created_at,
                updated_at
             FROM suggestions
             WHERE suggestion_id = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $suggestion_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $suggestion = $result->fetch_assoc();

        } else {

            $error = "Suggestion ID not found. Please check your ID and try again.";
        }

        $stmt->close();
    }
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
        Check Suggestion Status | Digital Suggestion Box
    </title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<main class="status-page">

    <div class="status-container">

        <a href="index.php" class="back-home">
            ← Back to Home
        </a>


        <div class="status-card">

            <div class="auth-icon">
                🔎
            </div>

            <span class="auth-label">
                SUGGESTION TRACKER
            </span>

            <h1>
                Check Your Status
            </h1>

            <p class="auth-description">
                Enter your Suggestion ID to view the latest
                status and response from the administration.
            </p>


            <form method="POST" action="">

                <div class="form-group">

                    <label for="suggestion_id">
                        Suggestion ID
                    </label>

                    <input
                        type="text"
                        id="suggestion_id"
                        name="suggestion_id"
                        placeholder="Example: DSB-2026-A1B2C3D4"
                        maxlength="30"
                        autocomplete="off"
                        required
                    >

                </div>


                <?php if ($error !== ""): ?>

                    <div class="error-message">

                        <span>⚠</span>

                        <span>
                            <?= htmlspecialchars($error) ?>
                        </span>

                    </div>

                <?php endif; ?>


                <button
                    type="submit"
                    class="primary-button"
                >
                    Check Status
                    <span>→</span>
                </button>

            </form>

        </div>


        <?php if ($suggestion !== null): ?>

            <div class="status-result">

                <div class="status-result-header">

                    <div>

                        <span class="auth-label">
                            SUGGESTION DETAILS
                        </span>

                        <h2>
                            <?= htmlspecialchars(
                                $suggestion["suggestion_id"]
                            ) ?>
                        </h2>

                    </div>

                    <span
                        class="status-badge status-<?=
                            strtolower(
                                str_replace(
                                    " ",
                                    "-",
                                    $suggestion["status"]
                                )
                            )
                        ?>"
                    >
                        <?= htmlspecialchars($suggestion["status"]) ?>
                    </span>

                </div>


                <div class="status-details">

                    <div class="detail-item">

                        <span>
                            Category
                        </span>

                        <strong>
                            <?= htmlspecialchars(
                                $suggestion["category"]
                            ) ?>
                        </strong>

                    </div>


                    <div class="detail-item">

                        <span>
                            Submitted Date
                        </span>

                        <strong>
                            <?= date(
                                "d M Y, h:i A",
                                strtotime($suggestion["created_at"])
                            ) ?>
                        </strong>

                    </div>


                    <div class="detail-item">

                        <span>
                            Last Updated
                        </span>

                        <strong>
                            <?= date(
                                "d M Y, h:i A",
                                strtotime($suggestion["updated_at"])
                            ) ?>
                        </strong>

                    </div>

                </div>


                <div class="suggestion-content">

                    <h3>
                        Your Suggestion
                    </h3>

                    <div class="suggestion-text">
                        <?= nl2br(
                            htmlspecialchars(
                                $suggestion["suggestion"]
                            )
                        ) ?>
                    </div>

                </div>


                <div class="admin-response">

                    <h3>
                        Admin Response
                    </h3>

                    <?php if (
                        !empty($suggestion["admin_response"])
                    ): ?>

                        <p>
                            <?= nl2br(
                                htmlspecialchars(
                                    $suggestion["admin_response"]
                                )
                            ) ?>
                        </p>

                    <?php else: ?>

                        <p class="no-response">
                            No response has been added yet.
                        </p>

                    <?php endif; ?>

                </div>


                <div class="privacy-note">

                    <span>
                        🔒
                    </span>

                    <p>
                        Your suggestion remains anonymous.
                        This page only uses your Suggestion ID
                        to retrieve its status.
                    </p>

                </div>

            </div>

        <?php endif; ?>

    </div>

</main>


<script src="js/script.js"></script>

</body>

</html>