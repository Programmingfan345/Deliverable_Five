<?php
session_start();
// If already logged in, go straight to app
if (isset($_SESSION['user_id'])) {
    header('Location: bus-navigation-app.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transit Navigator - Login</title>
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
        input[type="text"] {
            width: 100%;
            padding: 16px 20px;
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
        }
        input:focus {
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

        .success-banner {
            background: rgba(76, 175, 80, 0.18);
            border: 1px solid #4caf50;
            color: #c8f7d4;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
<div class="app-container">
    <div class="header">
        <h1>Transit Navigator</h1>
        <div class="subtitle">YOUR SMART BUS COMPANION</div>
    </div>

    <div class="content">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="font-size: 80px; margin-bottom: 20px;">🚌</div>
            <h1 style="font-size: 32px; margin-bottom: 10px;
                       background: linear-gradient(135deg, #d4af37 0%, #ffd700 100%);
                       -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                Transit Navigator
            </h1>
            <p style="color: #888;">Your Smart Bus Companion</p>
        </div>

        <?php if (isset($_GET['signup']) && $_GET['signup'] === 'ok'): ?>
            <div class="success-banner">
                Account created successfully. Please log in.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
            <div class="error-banner">
                Invalid email or password. Please try again.
            </div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <div style="margin-bottom: 20px;">
                <label for="email">Email or Username</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >
            </div>

            <div style="margin-bottom: 10px;">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <button type="submit" class="btn-primary">Log In</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="#" style="color: #d4af37; text-decoration: none; font-size: 14px;">
                Forgot Password?
            </a>
        </div>

        <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 1px solid #2a2a2a;">
            <p style="color: #888; font-size: 14px; margin-bottom: 15px;">Don't have an account?</p>
            <button
                class="btn-primary"
                type="button"
                onclick="window.location.href='signup.php';"
                style="background: transparent; border: 2px solid #d4af37; color: #d4af37;"
            >
                Sign Up
            </button>
        </div>
    </div>
</div>
</body>
</html>
