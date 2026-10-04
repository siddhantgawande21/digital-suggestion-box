<?php

session_start();

require_once "db.php";

$error = "";
$success = "";

$token = trim($_GET["token"] ?? $_POST["token"] ?? "");

$admin_id = null;


/*
|--------------------------------------------------------------------------
| Validate Reset Token
|--------------------------------------------------------------------------
*/

if ($token === "" || !preg_match('/^[a-f0-9]{64}$/', $token)) {

    $error = "This password reset link is invalid or expired.";

} else {

    $token_hash = hash("sha256", $token);

    $stmt = $conn->prepare(
        "SELECT id
         FROM admin
         WHERE reset_token_hash = ?
         AND reset_token_expires_at IS NOT NULL
         AND reset_token_expires_at > NOW()
         LIMIT 1"
    );

    $stmt->bind_param("s", $token_hash);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $admin = $result->fetch_assoc();

        $admin_id = $admin["id"];

    } else {

        $error = "This password reset link is invalid or expired.";
    }

    $stmt->close();
}


/*
|--------------------------------------------------------------------------
| Password Validation Function
|--------------------------------------------------------------------------
*/

function isValidPassword($password)
{
    return (
        strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[^A-Za-z0-9]/', $password)
    );
}


/*
|--------------------------------------------------------------------------
| Reset Password
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    $admin_id !== null &&
    $error === ""
) {

    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | Validate New Password
    |--------------------------------------------------------------------------
    */

    if ($password === "") {

        $error = "Please enter a new password.";

    } elseif (!isValidPassword($password)) {

        $error =
            "Password must contain at least 8 characters, " .
            "1 uppercase letter, 1 lowercase letter, 1 number, " .
            "and 1 special character.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Secure Password Hash
        |--------------------------------------------------------------------------
        */

        $password_hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        if ($password_hash === false) {

            $error =
                "Unable to reset your password. Please try again.";

        } else {

            /*
            |--------------------------------------------------------------------------
            | Update Password + Invalidate Reset Token
            |--------------------------------------------------------------------------
            */

            $update = $conn->prepare(
                "UPDATE admin
                 SET password_hash = ?,
                     reset_token_hash = NULL,
                     reset_token_expires_at = NULL
                 WHERE id = ?"
            );

            $update->bind_param(
                "si",
                $password_hash,
                $admin_id
            );


            if ($update->execute()) {

                $success =
                    "Your password has been reset successfully. " .
                    "You can now login with your new password.";

                $admin_id = null;

            } else {

                $error =
                    "Unable to reset your password. Please try again.";
            }

            $update->close();
        }
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
        Reset Password | Digital Suggestion Box
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
                🔐
            </div>

            <span class="auth-label">
                PASSWORD RESET
            </span>

            <h1>
                Create New Password
            </h1>


            <?php if ($success !== ""): ?>

                <div class="success-message">

                    <span>✓</span>

                    <span>
                        <?= htmlspecialchars($success) ?>
                    </span>

                </div>


                <a
                    href="login.php"
                    class="primary-button"
                >
                    Go to Login
                    <span>→</span>
                </a>


            <?php else: ?>


                <p class="auth-description">
                    Create a new secure password for your
                    administrator account.
                </p>


                <?php if ($error !== ""): ?>

                    <div class="error-message">

                        <span>⚠</span>

                        <span>
                            <?= htmlspecialchars($error) ?>
                        </span>

                    </div>

                <?php endif; ?>


                <?php if ($admin_id !== null): ?>

                    <form method="POST" action="">

                        <input
                            type="hidden"
                            name="token"
                            value="<?= htmlspecialchars($token) ?>"
                        >


                        <div class="form-group">

                            <label for="password">
                                New Password
                            </label>

                            <div class="password-field">

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Create a strong password"
                                    autocomplete="new-password"
                                    required
                                >

                                <button
                                    type="button"
                                    class="password-toggle"
                                    onclick="togglePassword('password', this)"
                                    aria-label="Show password"
                                >
                                    👁
                                </button>

                            </div>

                        </div>


                        <div class="password-requirements">

                            <span>Password requirements:</span>

                            <ul>

                                <li>At least 8 characters</li>

                                <li>1 uppercase letter</li>

                                <li>1 lowercase letter</li>

                                <li>1 number</li>

                                <li>1 special character</li>

                            </ul>

                        </div>


                        <div class="form-group">

                            <label for="confirm_password">
                                Confirm New Password
                            </label>

                            <div class="password-field">

                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    placeholder="Confirm your new password"
                                    autocomplete="new-password"
                                    required
                                >

                                <button
                                    type="button"
                                    class="password-toggle"
                                    onclick="togglePassword('confirm_password', this)"
                                    aria-label="Show password"
                                >
                                    👁
                                </button>

                            </div>

                        </div>


                        <button
                            type="submit"
                            class="primary-button"
                        >
                            Reset Password
                            <span>→</span>
                        </button>

                    </form>

                <?php endif; ?>


            <?php endif; ?>


            <div class="privacy-note auth-privacy">

                <span>🔒</span>

                <p>
                    Your new password is securely hashed.
                    The reset link becomes invalid after use.
                </p>

            </div>

        </div>

    </div>

</main>


<script src="js/script.js"></script>

<script>

function togglePassword(inputId, button) {

    const input = document.getElementById(inputId);

    if (!input) {
        return;
    }

    if (input.type === "password") {

        input.type = "text";

        button.textContent = "🙈";

        button.setAttribute(
            "aria-label",
            "Hide password"
        );

    } else {

        input.type = "password";

        button.textContent = "👁";

        button.setAttribute(
            "aria-label",
            "Show password"
        );
    }
}

</script>

</body>

</html>