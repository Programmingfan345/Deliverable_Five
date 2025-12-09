<?php
session_start();
require 'db.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'STUDENT';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    } elseif (!in_array($role, ['STUDENT', 'DRIVER', 'ADMIN'], true)) {
        $errors[] = 'Invalid role selected.';
    } else {
        // check if email already exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $errors[] = 'An account with that email already exists.';
        } else {
            // SCHOOL PROJECT ONLY: store password as plain text
            $stmt = $conn->prepare("
                INSERT INTO users (full_name, email, password, role)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("ssss", $name, $email, $password, $role);

            if ($stmt->execute()) {
                header('Location: index.php?signup=ok');
                exit;
            } else {
                $errors[] = 'Could not create account. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transit Navigator - Sign Up</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen,
                Ubuntu, Cantarell, sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            overflow-x: hidden;
        }
        .app-container {
            max-width: 428px;
            margin: 0 auto;
            background: #000000;
            min-height: 100vh;
            position: relative;
            box-shadow: 0 0 50px rgba(212, 175, 55, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            padding: 20px 20px 15px;
            border-bottom: 2px solid #d4af37;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #d4af37 0%, #ffd700 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 12px;
            color: #888;
            letter-spacing: 1px;
        }

        .content {
            padding: 20px;
            min-height: calc(100vh - 80px);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"],
        select {
            width: 100%;
            padding: 16px 20px;
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
        }
        input:focus,
        select:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.2);
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #d4af37;
            font-size: 14px;
            font-weight: 600;
        }

        .btn-primary {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #d4af37 0%, #ffd700 100%);
            color: #000;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.4);
        }

        .error-banner {
            background: rgba(139, 0, 0, 0.2);
            border: 1px solid #ff4d4f;
            color: #ffd2d2;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 18px;
        }

        a.link {
            color: #d4af37;
            text-decoration: none;
        }
        a.link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="app-container">
    <div class="header">
        <h1>Transit Navigator</h1>
        <div class="subtitle">CREATE YOUR ACCOUNT</div>
    </div>

    <div class="content">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 60px; margin-bottom: 10px;">🚌</div>
            <h2 style="color:#d4af37;">Sign Up</h2>
        </div>

        <?php if ($errors): ?>
            <div class="error-banner">
                <?php foreach ($errors as $e): ?>
                    <div><?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="signup.php">
            <div style="margin-bottom: 15px;">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name"
                       placeholder="Enter your name" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       placeholder="Enter your email" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Create a password" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="STUDENT">Student</option>
                    <option value="DRIVER">Driver</option>
                    <option value="ADMIN">Admin</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">Create Account</button>

            <div style="text-align:center; margin-top:20px;">
                <a class="link" href="index.php">Back to Login</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
