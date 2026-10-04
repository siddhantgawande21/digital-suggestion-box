<?php

session_start();

require_once "db.php";

$error = "";


/*
|--------------------------------------------------------------------------
| Verify College Code
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $college_code = trim($_POST["college_code"] ?? "");

    if ($college_code === "") {

        $error = "Please enter your college code.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id FROM college_code
             WHERE code = ? AND is_active = TRUE
             LIMIT 1"
        );

        $stmt->bind_param("s", $college_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            /*
            |--------------------------------------------------------------
            | Verification successful
            |--------------------------------------------------------------
            | Store only temporary verification status.
            | The college code itself is NOT stored with the suggestion.
            */

            $_SESSION["student_verified"] = true;

            $stmt->close();

            header("Location: submit.php");
            exit;

        } else {

            $error = "Sorry, the college code is incorrect. Please try again.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Verification | Digital Suggestion Box</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

    <main class="auth-page">

        <div class="auth-container">

            <a href="index.php" class="back-home">
                ← Back to Home
            </a>

            <div class="auth-card">

                <div class="auth-icon">
                    🎓
                </div>

                <span class="auth-label">
                    STUDENT ACCESS
                </span>

                <h1>
                    Verify Your College
                </h1>

                <p class="auth-description">
                    Enter the college code provided by your institution
                    to access the Digital Suggestion Box.
                </p>


                <?php if ($error !== ""): ?>

                    <div class="error-message">
                        <span>⚠</span>
                        <span>
                            <?= htmlspecialchars($error) ?>
                        </span>
                    </div>

                <?php endif; ?>


                <form method="POST" action="">

                    <div class="form-group">

                        <label for="college_code">
                            College Code
                        </label>

                        <input
                            type="text"
                            id="college_code"
                            name="college_code"
                            placeholder="Enter your college code"
                            maxlength="100"
                            autocomplete="off"
                            required
                        >

                    </div>

                    <button type="submit" class="primary-button">
                        Verify & Continue
                        <span>→</span>
                    </button>

                </form>


                <div class="privacy-note auth-privacy">

                    <span>🔒</span>

                    <p>
                        Your college code is used only for verification.
                        It is never stored with your suggestion.
                    </p>

                </div>

            </div>

        </div>

    </main>

    <script src="js/script.js"></script>

</body>

</html>