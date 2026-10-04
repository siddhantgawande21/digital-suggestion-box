<?php

session_start();

require_once "db.php";

$error = "";
$success = "";

$email = "";


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
| Admin Registration
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | Validate Email
    |--------------------------------------------------------------------------
    */

    if ($email === "") {

        $error = "Please enter your email address.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (strlen($email) > 255) {

        $error = "Email address is too long.";

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Password
    |--------------------------------------------------------------------------
    */

    elseif ($password === "") {

        $error = "Please enter a password.";

    } elseif (!isValidPassword($password)) {

        $error =
            "Password must contain at least 8 characters, " .
            "1 uppercase letter, 1 lowercase letter, 1 number, " .
            "and 1 special character.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    }


    /*
    |--------------------------------------------------------------------------
    | Check Existing Email
    |--------------------------------------------------------------------------
    */

    else {

        $stmt = $conn->prepare(
            "SELECT id
             FROM admin
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $error = "An admin account with this email already exists.";

            $stmt->close();

        } else {

            $stmt->close();


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

                $error = "Unable to create your account. Please try again.";

            } else {

                /*
                |--------------------------------------------------------------------------
                | Insert Admin Account
                |--------------------------------------------------------------------------
                */

                $stmt = $conn->prepare(
                    "INSERT INTO admin
                    (email, password_hash)
                    VALUES (?, ?)"
                );

                $stmt->bind_param(
                    "ss",
                    $email,
                    $password_hash
                );


                if ($stmt->execute()) {

                    $success =
                        "Account created successfully. " .
                        "You can now login with your credentials.";

                    $email = "";

                } else {

                    if ($conn->errno === 1062) {

                        $error =
                            "An admin account with this email already exists.";

                    } else {

                        $error =
                            "Something went wrong while creating your account.";
                    }
                }

                $stmt->close();
            }
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
        Create Admin Account | Digital Suggestion Box
    </title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<main class="auth-page">

    <div class="auth-container">

        <a href="index.php" class="back-home">
            ← Back to Home
        </a>


        <div class="auth-card">

            <div class="auth-icon admin-auth-icon">
                👤
            </div>

            <span class="auth-label">
                ADMIN REGISTRATION
            </span>

            <h1>
                Create Account
            </h1>

            <p class="auth-description">
                Create an administrator account to manage
                student suggestions.
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


                <div class="form-group">

                    <label for="password">
                        Create Password
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
                        Confirm Password
                    </label>

                    <div class="password-field">

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Confirm your password"
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
                    Create Account
                    <span>→</span>
                </button>

            </form>


            <div class="auth-divider">
                <span>OR</span>
            </div>


            <div class="signup-prompt">

                <span>
                    Already have an account?
                </span>

                <a href="login.php">
                    Login
                </a>

            </div>


            <div class="privacy-note auth-privacy">

                <span>🔒</span>

                <p>
                    Your password is securely hashed before
                    being stored in the database.
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