<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_level = intval($_POST['user_level']);
    $bio = sanitizeInput($_POST['bio']);

    // Validation
    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (!in_array($user_level, [1, 2])) {
        $errors[] = "Invalid user level selected.";
    }

    // Check if username or email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or email already exists.";
        }
    }

    // Handle profile picture upload
    $profile_picture = 'default.jpg';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];

        if (in_array($file_type, $allowed_types)) {
            $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $profile_picture = uniqid() . '.' . $extension;
            $upload_path = 'assets/images/' . $profile_picture;

            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                $errors[] = "Failed to upload profile picture.";
                $profile_picture = 'default.jpg';
            }
        } else {
            $errors[] = "Invalid file type for profile picture. Please use JPEG, PNG, or GIF.";
        }
    }

    // Insert user if no errors
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, user_level, bio, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash, $user_level, $bio, $profile_picture]);

            $success = true;

            // Auto-login the user
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_level'] = $user_level;

            // Redirect after a delay
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 2000);
            </script>";

        } catch (PDOException $e) {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SocialHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">    <style>
        .register-container {
            background: linear-gradient(135deg, #6ea8fe 0%, #ffd43b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }

        .password-strength.weak {
            color: #dc3545;
        }

        .password-strength.medium {
            color: #fd7e14;
        }

        .password-strength.strong {
            color: #198754;
        }

        .profile-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #dee2e6;
            margin: 0 auto;
            display: block;
        }
    </style>
</head>
<body>
<div class="register-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="register-card p-5">
                    <div class="text-center mb-4">
                        <a href="index.php" class="text-decoration-none">
                            <h2 class="text-primary">
                                <i class="fas fa-share-alt"></i> SocialHub
                            </h2>
                        </a>
                        <p class="text-muted">Create your account and join the community</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> Registration successful! Redirecting to home page...
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="registerForm" novalidate>
                        <!-- Profile Picture -->
                        <div class="mb-4 text-center">
                            <img src="assets/images/default.jpg" alt="Profile Preview"
                                 class="profile-preview mb-3" id="profilePreview">
                            <div>
                                <label for="profile_picture" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-camera"></i> Choose Profile Picture
                                </label>
                                <input type="file" class="d-none" id="profile_picture" name="profile_picture"
                                       accept="image/*" onchange="previewImage(this, 'profilePreview')">
                            </div>
                            <small class="text-muted">Optional - JPEG, PNG, or GIF (max 2MB)</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        <i class="fas fa-user"></i> Username *
                                    </label>
                                    <input type="text" class="form-control" id="username" name="username"
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                           required minlength="3" maxlength="50">
                                    <div class="invalid-feedback">
                                        Please choose a username (3-50 characters).
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope"></i> Email Address *
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">
                                        Please enter a valid email address.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock"></i> Password *
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password"
                                               required minlength="8" oninput="updatePasswordStrength(this, document.getElementById('passwordStrength'))">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password')">
                                            <i class="fas fa-eye" id="passwordToggleIcon"></i>
                                        </button>
                                    </div>
                                    <div id="passwordStrength" class="password-strength"></div>
                                    <div class="invalid-feedback">
                                        Password must be at least 8 characters long.
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock"></i> Confirm Password *
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                           required minlength="8" oninput="validatePasswordMatch()">
                                    <div class="invalid-feedback">
                                        Passwords do not match.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="user_level" class="form-label">
                                <i class="fas fa-shield-alt"></i> Account Type *
                            </label>
                            <select class="form-select" id="user_level" name="user_level" required>
                                <option value="">Choose account type...</option>
                                <option value="1" <?php echo (($_POST['user_level'] ?? '') == '1') ? 'selected' : ''; ?>>
                                    Regular User - Can create posts and comments
                                </option>
                                <option value="2" <?php echo (($_POST['user_level'] ?? '') == '2') ? 'selected' : ''; ?>>
                                    Moderator - Can create, edit, and delete posts/comments
                                </option>
                            </select>
                            <div class="invalid-feedback">
                                Please select an account type.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="bio" class="form-label">
                                <i class="fas fa-info-circle"></i> Bio (Optional)
                            </label>
                            <textarea class="form-control" id="bio" name="bio" rows="3" maxlength="500"
                                      placeholder="Tell us a bit about yourself..."
                                      oninput="autoResizeTextarea(this)"><?php echo htmlspecialchars($_POST['bio'] ?? ''); ?></textarea>
                            <div class="form-text">
                                <span id="bioCounter">500 characters remaining</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-primary">Terms of Service</a>
                                    and <a href="#" class="text-primary">Privacy Policy</a> *
                                </label>
                                <div class="invalid-feedback">
                                    You must agree to the terms and conditions.
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Already have an account?
                            <a href="index.php" class="text-primary text-decoration-none">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script>
    // Toggle password visibility
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(inputId + 'ToggleIcon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Validate password match
    function validatePasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const confirmInput = document.getElementById('confirm_password');

        if (confirmPassword && password !== confirmPassword) {
            confirmInput.classList.add('is-invalid');
            confirmInput.classList.remove('is-valid');
        } else if (confirmPassword) {
            confirmInput.classList.remove('is-invalid');
            confirmInput.classList.add('is-valid');
        }
    }

    // Initialize character counter
    initCharacterCounter('bio', 'bioCounter', 500);

    // Form validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const form = this;
        const inputs = form.querySelectorAll('input[required], select[required]');
        let isValid = true;

        // Check all required fields
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            }
        });

        // Additional validations
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (password !== confirmPassword) {
            document.getElementById('confirm_password').classList.add('is-invalid');
            isValid = false;
        }

        const terms = document.getElementById('terms');
        if (!terms.checked) {
            terms.classList.add('is-invalid');
            isValid = false;
        } else {
            terms.classList.remove('is-invalid');
        }

        if (isValid) {
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<div class="loading"></div> Creating Account...';
            submitBtn.disabled = true;

            // Submit the form
            form.submit();
        }

        form.classList.add('was-validated');
    });

    // Real-time validation
    document.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
</script>
</body>
</html>