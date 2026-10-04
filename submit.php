<?php

session_start();

require_once "db.php";


/*
|--------------------------------------------------------------------------
| Allowed Suggestion Categories
|--------------------------------------------------------------------------
*/

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
| Student Verification Check
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["student_verified"]) ||
    $_SESSION["student_verified"] !== true
) {
    header("Location: college-code.php");
    exit;
}


$error = "";
$success = false;
$suggestion_id = "";


/*
|--------------------------------------------------------------------------
| Submit Suggestion
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $category = trim($_POST["category"] ?? "");
    $suggestion = trim($_POST["suggestion"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | Validate Input
    |--------------------------------------------------------------------------
    */

    if (
        $category === "" ||
        !in_array($category, $allowed_categories, true)
    ) {

        $error = "Please select a valid suggestion category.";

    } elseif ($suggestion === "") {

        $error = "Please enter your suggestion.";

    } elseif (mb_strlen($suggestion) < 10) {

        $error = "Suggestion must contain at least 10 characters.";

    } elseif (mb_strlen($suggestion) > 5000) {

        $error = "Suggestion must not exceed 5000 characters.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Generate Unique Suggestion ID
        |--------------------------------------------------------------------------
        */

        do {

            $suggestion_id =
                "DSB-" .
                date("Y") .
                "-" .
                strtoupper(bin2hex(random_bytes(4)));

            $check = $conn->prepare(
                "SELECT id
                 FROM suggestions
                 WHERE suggestion_id = ?
                 LIMIT 1"
            );

            $check->bind_param("s", $suggestion_id);
            $check->execute();

            $result = $check->get_result();

            $exists = $result->num_rows > 0;

            $check->close();

        } while ($exists);


        /*
        |--------------------------------------------------------------------------
        | Insert Anonymous Suggestion
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "INSERT INTO suggestions
            (suggestion_id, category, suggestion)
            VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "sss",
            $suggestion_id,
            $category,
            $suggestion
        );


        if ($stmt->execute()) {

            $success = true;

            unset($_SESSION["student_verified"]);

        } else {

            $error = "Something went wrong. Please try again.";
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
        <?= $success ? "Suggestion Submitted" : "Submit Suggestion" ?>
        | Digital Suggestion Box
    </title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<main class="suggestion-page">

    <div class="suggestion-container">


        <?php if ($success): ?>

            <!-- =====================================================
                 SUCCESS PAGE
            ====================================================== -->

            <div class="success-card">

                <div class="success-icon">
                    ✓
                </div>

                <span class="auth-label">
                    SUBMISSION SUCCESSFUL
                </span>

                <h1>
                    Suggestion Submitted
                </h1>

                <p>
                    Thank you for helping improve our college.
                    Your suggestion has been submitted anonymously.
                </p>


                <div class="suggestion-id-box">

                    <span>
                        YOUR SUGGESTION ID
                    </span>

                    <strong>
                        <?= htmlspecialchars($suggestion_id) ?>
                    </strong>

                </div>


                <div class="important-note">

                    <span>⚠</span>

                    <p>
                        Save this Suggestion ID.
                        You will need it to check your suggestion status later.
                    </p>

                </div>


                <div class="success-actions">

                    <a
                        href="status.php"
                        class="primary-button"
                    >
                        🔎 Check Suggestion Status
                        <span>→</span>
                    </a>

                    <a
                        href="index.php"
                        class="secondary-button"
                    >
                        Back to Home
                    </a>

                </div>

            </div>


        <?php else: ?>

            <!-- =====================================================
                 SUBMISSION FORM
            ====================================================== -->

            <a href="index.php" class="back-home">
                ← Back to Home
            </a>


            <div class="suggestion-card">

                <div class="suggestion-header">

                    <div class="auth-icon">
                        💡
                    </div>

                    <div>

                        <span class="auth-label">
                            ANONYMOUS FEEDBACK
                        </span>

                        <h1>
                            Share Your Suggestion
                        </h1>

                    </div>

                </div>


                <p class="auth-description">
                    Your feedback helps us understand what is working
                    and what needs improvement.
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

                        <label for="category">
                            Suggestion Category
                        </label>

                        <select
                            id="category"
                            name="category"
                            required
                        >

                            <option value="">
                                Select a category
                            </option>

                            <?php foreach ($allowed_categories as $item): ?>

                                <option
                                    value="<?= htmlspecialchars($item) ?>"
                                    <?= (
                                        isset($_POST["category"]) &&
                                        $_POST["category"] === $item
                                    ) ? "selected" : "" ?>
                                >
                                    <?= htmlspecialchars($item) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="suggestion">
                            Your Suggestion
                        </label>

                        <textarea
                            id="suggestion"
                            name="suggestion"
                            rows="8"
                            maxlength="5000"
                            placeholder="Write your suggestion here..."
                            required
                        ><?= htmlspecialchars($_POST["suggestion"] ?? "") ?></textarea>

                        <div class="character-count">
                            <span id="characterCount">0</span> / 5000
                        </div>

                    </div>


                    <div class="privacy-note">

                        <span>🔒</span>

                        <p>
                            Your suggestion is completely anonymous.
                            We do not collect your name, email, phone number,
                            roll number, student ID, or college code.
                        </p>

                    </div>


                    <button
                        type="submit"
                        class="primary-button submit-button"
                    >
                        Submit Anonymously
                        <span>→</span>
                    </button>

                </form>


                <!-- =================================================
                     TRACK SUGGESTION OPTION
                ================================================== -->

                <div class="track-divider">
                    <span>OR</span>
                </div>


                <div class="track-suggestion-box">

                    <div class="track-icon">
                        🔎
                    </div>

                    <div class="track-content">

                        <h3>
                            Already submitted a suggestion?
                        </h3>

                        <p>
                            Track your suggestion using your
                            Suggestion ID.
                        </p>

                    </div>

                    <a
                        href="status.php"
                        class="secondary-button"
                    >
                        Track Your Suggestion
                        <span>→</span>
                    </a>

                </div>

            </div>

        <?php endif; ?>

    </div>

</main>


<script src="js/script.js"></script>

<script>

const suggestionBox = document.getElementById("suggestion");
const characterCount = document.getElementById("characterCount");

if (suggestionBox && characterCount) {

    function updateCharacterCount() {

        characterCount.textContent =
            suggestionBox.value.length;
    }

    suggestionBox.addEventListener(
        "input",
        updateCharacterCount
    );

    updateCharacterCount();
}

</script>

</body>

</html>