<?php

session_start();

require_once "db.php";

$error = "";

$email = "";


/*
|--------------------------------------------------------------------------
| Admin Login
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | Validate Email
    |--------------------------------------------------------------------------
    */

    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif ($password === "") {

        $error = "Please enter your password.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Find Admin
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT id, email, password_hash
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
            | Verify Password
            |--------------------------------------------------------------------------
            */

            if (
                password_verify(
                    $password,
                    $admin["password_hash"]
                )
            ) {

                /*
                |--------------------------------------------------------------------------
                | Regenerate Session ID
                |--------------------------------------------------------------------------
                */

                session_regenerate_id(true);

                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_email"] = $admin["email"];


                $stmt->close();

                header("Location: admin.php");
                exit;

            } else {

                $error = "Invalid email or password.";
            }

        } else {

            $error = "Invalid email or password.";
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
        Admin Login | Digital Suggestion Box
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
                🔐
            </div>

            <span class="auth-label">
                ADMIN ACCESS
            </span>

            <h1>
                Welcome Back
            </h1>

            <p class="auth-description">
                Sign in to manage student suggestions and feedback.
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

                    <div class="form-label-row">

                        <label for="password">
                            Password
                        </label>

                        <a
                            href="forgot-password.php"
                            class="forgot-link"
                        >
                            Forgot Password?
                        </a>

                    </div>


                    <div class="password-field">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
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


                <button
                    type="submit"
                    class="primary-button"
                >
                    Login
                    <span>→</span>
                </button>

            </form>


            <div class="auth-divider">
                <span>OR</span>
            </div>


            <div class="signup-prompt">

                <span>
                    Don't have an admin account?
                </span>

                <a href="register.php">
                    Create Account
                </a>

            </div>


            <div class="privacy-note auth-privacy">

                <span>🔒</span>

                <p>
                    Your administrator credentials are securely
                    protected using password hashing.
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
        button.setAttribute("aria-label", "Hide password");

    } else {

        input.type = "password";
        button.textContent = "👁";
        button.setAttribute("aria-label", "Show password");
    }
}

</script>

</body>

</html>