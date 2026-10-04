/* =========================================================
   DIGITAL SUGGESTION BOX
   Main JavaScript
   ========================================================= */


/* =========================================================
   1. PAGE LOAD ANIMATION
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    document.body.classList.add("page-loaded");

});


/* =========================================================
   2. PREVENT DOUBLE FORM SUBMISSION
   ========================================================= */

document.addEventListener("submit", function (event) {

    const form = event.target;

    if (!form || form.dataset.submitting === "true") {
        return;
    }

    /*
    | Do not interfere with GET forms.
    */
    if (form.method.toLowerCase() === "get") {
        return;
    }

    form.dataset.submitting = "true";

    const submitButton = form.querySelector(
        'button[type="submit"]'
    );

    if (submitButton) {

        submitButton.disabled = true;

        submitButton.style.opacity = "0.7";

        submitButton.style.cursor = "not-allowed";
    }

});


/* =========================================================
   3. AUTO-UPPERCASE COLLEGE CODE
   ========================================================= */

const collegeCodeInput =
    document.getElementById("college_code");

if (collegeCodeInput) {

    collegeCodeInput.addEventListener(
        "input",
        function () {

            this.value = this.value.toUpperCase();

        }
    );

}


/* =========================================================
   4. AUTO-UPPERCASE SUGGESTION ID
   ========================================================= */

const suggestionIdInput =
    document.getElementById("suggestion_id");

if (suggestionIdInput) {

    suggestionIdInput.addEventListener(
        "input",
        function () {

            this.value = this.value.toUpperCase();

        }
    );

}


/* =========================================================
   5. CONFIRM BEFORE LOGOUT
   ========================================================= */

const logoutButton =
    document.querySelector(".logout-button");

if (logoutButton) {

    logoutButton.addEventListener(
        "click",
        function (event) {

            const confirmed = window.confirm(
                "Are you sure you want to logout?"
            );

            if (!confirmed) {
                event.preventDefault();
            }

        }
    );

}


/* =========================================================
   6. CONFIRM SUGGESTION UPDATE
   ========================================================= */

const updateForms =
    document.querySelectorAll(
        ".update-suggestion-form"
    );

updateForms.forEach(function (form) {

    form.addEventListener(
        "submit",
        function (event) {

            const confirmed = window.confirm(
                "Are you sure you want to save these changes?"
            );

            if (!confirmed) {
                event.preventDefault();

                form.dataset.submitting = "false";

                const button =
                    form.querySelector(
                        'button[type="submit"]'
                    );

                if (button) {

                    button.disabled = false;

                    button.style.opacity = "1";

                    button.style.cursor = "pointer";
                }
            }

        }
    );

});


/* =========================================================
   7. REMOVE DISABLED STATE AFTER BACK BUTTON
   ========================================================= */

window.addEventListener(
    "pageshow",
    function () {

        const forms =
            document.querySelectorAll("form");

        forms.forEach(function (form) {

            form.dataset.submitting = "false";

            const button =
                form.querySelector(
                    'button[type="submit"]'
                );

            if (button) {

                button.disabled = false;

                button.style.opacity = "";

                button.style.cursor = "";
            }

        });

    }
);