<?php

session_start();

require_once "db.php";

$error = "";
$success = "";
$email = "";


/*
|--------------------------------------------------------------------------
| Forgot Password
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");

    if ($email === "") {

        $error = "Please enter your email address.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Find Admin Account
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT id
             FROM admin
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $admin = $result->fetch_assoc();

            /*
            |--------------------------------------------------------------------------
            | Generate Secure Reset Token
            |--------------------------------------------------------------------------
            */

            $reset_token = bin2hex(random_bytes(32));

            $reset_token_hash = hash(
                "sha256",
                $reset_token
            );

            /*
            | Token remains valid for 30 minutes.
            */
            $expires_at = date(
                "Y-m-d H:i:s",
                time() + (30 * 60)
            );


            /*
            |--------------------------------------------------------------------------
            | Store Hashed Token
            |--------------------------------------------------------------------------
            */

            $update = $conn->prepare(
                "UPDATE admin
                 SET reset_token_hash = ?,
                     reset_token_expires_at = ?
                 WHERE id = ?"
            );

            $update->bind_param(
                "ssi",
                $reset_token_hash,
                $expires_at,
                $admin["id"]
            );

            if ($update->execute()) {

                /*
                |--------------------------------------------------------------------------
                | Local XAMPP Development Reset Link
                |--------------------------------------------------------------------------
                |
                | For this local hackathon project, the reset link is displayed
                | directly. No external email service is required.
                |
                */

                $reset_link =
                    "http://localhost/digital-suggestion-box/reset-password.php?token=" .
                    urlencode($reset_token);

                $success =
                    "Reset link generated successfully.";

            } else {

                $error =
                    "Unable to generate reset link. Please try again.";
            }

            $update->close();

        } else {

            /*
            |--------------------------------------------------------------------------
            | Do not reveal whether an email exists
            |--------------------------------------------------------------------------
            */

            $success =
                "If an account exists for this email, a password reset link has been generated.";
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
        Forgot Password | Digital Suggestion Box
    </title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<main class="auth-page">

    <div class="auth-container">

        <a href="login.php" class="back-home">
            ← Back to Login
        </a>


        <div class="auth-card">

            <div class="auth-icon">
                🔑
            </div>

            <span class="auth-label">
                PASSWORD RECOVERY
            </span>

            <h1>
                Forgot Password?
            </h1>

            <p class="auth-description">
                Enter your registered admin email address
                to generate a secure password reset link.
            </p>


            <?php if ($error !== ""): ?>

                <div class="error-message">

                    <span>⚠</span>

                    <span>
                        <?= htmlspecialchars($error) ?>
                    </span>

                </div>

            <?php endif; ?>


            <?php if ($success !== ""): ?>

                <div class="success-message">

                    <span>✓</span>

                    <span>
                        <?= htmlspecialchars($success) ?>
                    </span>

                </div>

            <?php endif; ?>


            <form method="POST" action="">

                <div class="form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@example.com"
                        value="<?= htmlspecialchars($email) ?>"
                        maxlength="255"
                        autocomplete="email"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="primary-button"
                >
                    Generate Reset Link
                    <span>→</span>
                </button>

            </form>


            <?php if (
                isset($reset_link) &&
                $reset_link !== ""
            ): ?>

                <div class="reset-link-box">

                    <span>
                        PASSWORD RESET LINK
                    </span>

                    <a
                        href="<?= htmlspecialchars($reset_link) ?>"
                    >
                        Reset Your Password →
                    </a>

                    <small>
                        This link expires in 30 minutes.
                    </small>

                </div>

            <?php endif; ?>


            <div class="auth-divider">
                <span>OR</span>
            </div>


            <div class="signup-prompt">

                <span>
                    Remember your password?
                </span>

                <a href="login.php">
                    Login
                </a>

            </div>


            <div class="privacy-note auth-privacy">

                <span>🔒</span>

                <p>
                    Reset tokens are stored securely as hashes
                    and expire automatically after 30 minutes.
                </p>

            </div>

        </div>

    </div>

</main>


<script src="js/script.js"></script>

</body>

</html>