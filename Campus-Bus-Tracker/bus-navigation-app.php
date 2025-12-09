<?php
// bus-navigation-app.php – main Transit Navigator UI (after login)

session_start();

// Guard: block access if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Values to display in profile/settings
$full_name = $_SESSION['full_name'] ?? 'Transit User';
$email     = $_SESSION['email'] ?? 'unknown@example.com';
$role      = $_SESSION['role'] ?? 'STUDENT';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transit Navigator - Bus Navigation App</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
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

        .page {
            display: none;
            animation: fadeIn 0.3s ease-in;
        }

        .page.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .content {
            padding: 20px;
            min-height: calc(100vh - 180px);
        }

        .search-box {
            position: relative;
            margin-bottom: 15px;
        }

        .search-box input {
            width: 100%;
            padding: 16px 50px 16px 20px;
            background: #1a1a1a;
            border: 2px solid #333;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.2);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 25px;
        }

        .quick-action {
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            padding: 25px 20px;
            border-radius: 16px;
            border: 1px solid #2a2a2a;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .quick-action:hover {
            border-color: #d4af37;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
        }

        .quick-action-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .quick-action-text {
            font-size: 14px;
            font-weight: 600;
            color: #d4af37;
        }

        .route-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .route-card:hover {
            border-color: #d4af37;
            transform: translateX(5px);
        }

        .route-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .route-number {
            background: linear-gradient(135deg, #d4af37 0%, #ffd700 100%);
            color: #000;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 18px;
        }

        .route-time {
            color: #d4af37;
            font-weight: 600;
            font-size: 16px;
        }

        .route-info {
            color: #888;
            font-size: 14px;
            line-height: 1.6;
        }

        .route-destination {
            color: #fff;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .map-container {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
            border-radius: 16px;
            border: 1px solid #2a2a2a;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .map-placeholder {
            text-align: center;
            color: #666;
        }

        .bus-icon {
            position: absolute;
            font-size: 32px;
            animation: moveBus 3s ease-in-out infinite;
        }

        @keyframes moveBus {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(50%, 50%) scale(1.2); }
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }

        .status-on-time {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid #4caf50;
        }

        .status-delayed {
            background: rgba(255, 152, 0, 0.2);
            color: #ff9800;
            border: 1px solid #ff9800;
        }

        .saved-route {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .saved-route-info h3 {
            color: #fff;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .saved-route-info p {
            color: #888;
            font-size: 13px;
        }

        .favorite-icon {
            font-size: 24px;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .favorite-icon:hover {
            transform: scale(1.2);
        }

        .profile-header {
            text-align: center;
            padding: 30px 0;
            border-bottom: 1px solid #2a2a2a;
            margin-bottom: 25px;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #d4af37 0%, #ffd700 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 15px;
        }

        .profile-name {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .profile-email {
            color: #888;
            font-size: 14px;
        }

        .settings-list {
            list-style: none;
        }

        .settings-item {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .settings-item:hover {
            border-color: #d4af37;
            transform: translateX(5px);
        }

        .settings-item-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .settings-icon {
            font-size: 24px;
        }

        .settings-text h4 {
            font-size: 15px;
            margin-bottom: 3px;
        }

        .settings-text p {
            font-size: 12px;
            color: #888;
        }

        .settings-arrow {
            color: #d4af37;
            font-size: 20px;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            max-width: 428px;
            width: 100%;
            background: linear-gradient(180deg, rgba(0,0,0,0.95) 0%, #000000 100%);
            border-top: 2px solid #d4af37;
            display: flex;
            justify-content: space-around;
            padding: 15px 0 20px;
            box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.5);
        }

        .nav-item {
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            padding: 5px 15px;
        }

        .nav-item:hover {
            transform: translateY(-3px);
        }

        .nav-icon {
            font-size: 24px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }

        .nav-label {
            font-size: 11px;
            color: #888;
            font-weight: 500;
            transition: all 0.3s;
        }

        .nav-item.active .nav-icon {
            filter: drop-shadow(0 0 10px #d4af37);
        }

        .nav-item.active .nav-label {
            color: #d4af37;
            font-weight: 700;
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

        .live-update {
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid #d4af37;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .live-update-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .pulse-dot {
            width: 10px;
            height: 10px;
            background: #d4af37;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.5); opacity: 0.5; }
        }

        .update-time {
            font-size: 12px;
            color: #888;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #2a2a2a;
            color: #d4af37;
        }

        .bottom-nav.hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="header" id="mainHeader">
            <h1>Transit Navigator</h1>
            <div class="subtitle">YOUR SMART BUS COMPANION</div>
        </div>

        <!-- PAGE: HOME -->
        <div class="page active" id="page-home">
            <div class="content">
                <div class="search-section">
                    <h2 class="section-title">Where to?</h2>
                    <div class="search-box">
                        <input type="text" placeholder="Enter destination or bus number..." id="searchInput">
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="From current location" id="fromInput">
                    </div>
                    <button class="btn-primary" onclick="alert('Searching for routes...')">Find Routes</button>
                </div>

                <div class="quick-actions">
                    <div class="quick-action" onclick="navigateTo('tracker')">
                        <div class="quick-action-icon">🚌</div>
                        <div class="quick-action-text">Live Tracking</div>
                    </div>
                    <div class="quick-action" onclick="navigateTo('saved')">
                        <div class="quick-action-icon">⭐</div>
                        <div class="quick-action-text">Saved Routes</div>
                    </div>
                    <div class="quick-action" onclick="alert('Schedule feature coming soon!')">
                        <div class="quick-action-icon">📅</div>
                        <div class="quick-action-text">Schedule</div>
                    </div>
                    <div class="quick-action" onclick="alert('Nearby stops feature!')">
                        <div class="quick-action-icon">📍</div>
                        <div class="quick-action-text">Nearby Stops</div>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <h3 class="section-title">Buses Near You</h3>
                    <div class="route-card">
                        <div class="route-header">
                            <div class="route-number">Route 15</div>
                            <div class="route-time">2 min</div>
                        </div>
                        <div class="route-info">
                            <div class="route-destination">Downtown Terminal</div>
                            <div>Next stop: Main St & 5th Ave</div>
                            <span class="status-badge status-on-time">● On Time</span>
                        </div>
                    </div>
                    <div class="route-card">
                        <div class="route-header">
                            <div class="route-number">Route 42</div>
                            <div class="route-time">7 min</div>
                        </div>
                        <div class="route-info">
                            <div class="route-destination">University Campus</div>
                            <div>Next stop: Central Station</div>
                            <span class="status-badge status-on-time">● On Time</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PAGE: BUS TRACKER -->
        <div class="page" id="page-tracker">
            <div class="content">
                <h2 class="section-title">Real-Time Bus Tracker</h2>
                
                <div class="live-update">
                    <div class="live-update-header">
                        <div class="pulse-dot"></div>
                        <strong>Live Updates</strong>
                    </div>
                    <div>Tracking 3 buses near you</div>
                    <div class="update-time">Updated 5 seconds ago</div>
                </div>

                <div class="map-container">
                    <div class="bus-icon">🚌</div>
                    <div class="map-placeholder">
                        <div>Live GPS Map</div>
                        <div style="font-size: 12px;">Real-time tracking active</div>
                    </div>
                </div>

                <h3 class="section-title" style="margin-top: 25px;">Buses Near You</h3>

                <div class="route-card">
                    <div class="route-header">
                        <div class="route-number">Bus #15</div>
                        <div class="route-time">2 min away</div>
                    </div>
                    <div class="route-info">
                        <div class="route-destination">📍 Currently at Oak Street</div>
                        <div style="margin-top: 8px;">Direction: Downtown Terminal</div>
                        <div style="margin-top: 8px; color: #d4af37;">Speed: 25 mph | Distance: 0.3 miles</div>
                        <span class="status-badge status-on-time">● On Time</span>
                    </div>
                </div>

                <div class="route-card">
                    <div class="route-header">
                        <div class="route-number">Bus #42</div>
                        <div class="route-time">7 min away</div>
                    </div>
                    <div class="route-info">
                        <div class="route-destination">📍 Currently at Central Station</div>
                        <div style="margin-top: 8px;">Direction: University Campus</div>
                        <div style="margin-top: 8px; color: #d4af37;">Speed: 30 mph | Distance: 1.2 miles</div>
                        <span class="status-badge status-on-time">● On Time</span>
                    </div>
                </div>

                <div class="route-card">
                    <div class="route-header">
                        <div class="route-number">Bus #88</div>
                        <div class="route-time">12 min away</div>
                    </div>
                    <div class="route-info">
                        <div class="route-destination">📍 Currently at Park Avenue</div>
                        <div style="margin-top: 8px;">Direction: Express Downtown</div>
                        <div style="margin-top: 8px; color: #d4af37;">Speed: 35 mph | Distance: 2.1 miles</div>
                        <span class="status-badge status-delayed">● Delayed 3 min</span>
                    </div>
                </div>

                <button class="btn-primary" onclick="alert('🔔 Notifications enabled!')">Enable Arrival Notifications</button>
            </div>
        </div>

        <!-- PAGE: SAVED ROUTES -->
        <div class="page" id="page-saved">
            <div class="content">
                <h2 class="section-title">My Saved Routes</h2>
                <p style="color: #888; margin-bottom: 25px; font-size: 14px;">Quick access to your favorite routes</p>

                <div class="saved-route">
                    <div class="saved-route-info">
                        <h3>Route 15 - Downtown</h3>
                        <p>Main St → Downtown Terminal</p>
                        <p style="color: #d4af37; margin-top: 5px;">⏱️ Avg. 25 min | 🚏 8 stops</p>
                    </div>
                    <div class="favorite-icon" onclick="alert('Removed from favorites')">⭐</div>
                </div>

                <div class="saved-route">
                    <div class="saved-route-info">
                        <h3>Route 42 - University</h3>
                        <p>Central → University Campus</p>
                        <p style="color: #d4af37; margin-top: 5px;">⏱️ Avg. 35 min | 🚏 12 stops</p>
                    </div>
                    <div class="favorite-icon" onclick="alert('Removed from favorites')">⭐</div>
                </div>

                <div class="saved-route">
                    <div class="saved-route-info">
                        <h3>Route 88 - Express</h3>
                        <p>Express Downtown (Limited Stops)</p>
                        <p style="color: #d4af37; margin-top: 5px;">⏱️ Avg. 18 min | 🚏 4 stops</p>
                    </div>
                    <div class="favorite-icon" onclick="alert('Removed from favorites')">⭐</div>
                </div>

                <h3 class="section-title" style="margin-top: 35px;">Recent Trips</h3>

                <div class="route-card">
                    <div class="route-info">
                        <div class="route-destination">🕒 Today, 2:30 PM</div>
                        <div style="margin-top: 8px;"><strong>Route 15</strong> - Main St to Downtown</div>
                        <div style="color: #888; font-size: 13px; margin-top: 5px;">Duration: 23 minutes</div>
                    </div>
                </div>

                <div class="route-card">
                    <div class="route-info">
                        <div class="route-destination">🕒 Yesterday, 8:15 AM</div>
                        <div style="margin-top: 8px;"><strong>Route 42</strong> - Home to University</div>
                        <div style="color: #888; font-size: 13px; margin-top: 5px;">Duration: 32 minutes</div>
                    </div>
                </div>

                <button class="btn-primary" onclick="alert('Export feature coming soon!')">Export Trip History</button>
            </div>
        </div>

        <!-- PAGE: SETTINGS -->
        <div class="page" id="page-settings">
            <div class="content">
                <div class="profile-header">
                    <div class="profile-avatar">👤</div>
                    <div class="profile-name">
                        <?php echo htmlspecialchars($full_name); ?>
                    </div>
                    <div class="profile-email">
                        <?php echo htmlspecialchars($email); ?>
                    </div>
                    <div style="color:#888; font-size:13px; margin-top:4px;">
                        Role: <?php echo htmlspecialchars($role); ?>
                    </div>
                </div>

                <!-- SUPER VISIBLE LOGOUT BUTTON -->
                <div style="margin-bottom: 25px; text-align: center;">
                    <button
                        class="btn-primary"
                        style="width: 80%; background: transparent; border: 2px solid #d4af37; color: #d4af37; margin-top: 10px;"
                        onclick="window.location.href='logout.php'">
                        Log Out
                    </button>
                </div>

                <h3 class="section-title">Account Settings</h3>
                <ul class="settings-list">
                    <li class="settings-item" onclick="alert('Edit profile')">
                        <div class="settings-item-left">
                            <div class="settings-icon">👤</div>
                            <div class="settings-text">
                                <h4>Edit Profile</h4>
                                <p>Update personal information</p>
                            </div>
                        </div>
                        <div class="settings-arrow">›</div>
                    </li>
                    <li class="settings-item" onclick="alert('Payment methods')">
                        <div class="settings-item-left">
                            <div class="settings-icon">💳</div>
                            <div class="settings-text">
                                <h4>Payment Methods</h4>
                                <p>Manage cards and passes</p>
                            </div>
                        </div>
                        <div class="settings-arrow">›</div>
                    </li>
                </ul>

                <h3 class="section-title">Preferences</h3>
                <ul class="settings-list">
                    <li class="settings-item" onclick="alert('Notifications: Enabled')">
                        <div class="settings-item-left">
                            <div class="settings-icon">🔔</div>
                            <div class="settings-text">
                                <h4>Notifications</h4>
                                <p>Push alerts for bus arrivals</p>
                            </div>
                        </div>
                        <div class="settings-arrow" style="color: #4caf50;">ON</div>
                    </li>
                    <li class="settings-item" onclick="alert('Location: Always')">
                        <div class="settings-item-left">
                            <div class="settings-icon">📍</div>
                            <div class="settings-text">
                                <h4>Location Services</h4>
                                <p>GPS tracking for live updates</p>
                            </div>
                        </div>
                        <div class="settings-arrow" style="color: #4caf50;">ON</div>
                    </li>
                    <li class="settings-item" onclick="alert('Theme settings')">
                        <div class="settings-item-left">
                            <div class="settings-icon">🎨</div>
                            <div class="settings-text">
                                <h4>Theme</h4>
                                <p>Black, Gold & White</p>
                            </div>
                        </div>
                        <div class="settings-arrow">›</div>
                    </li>
                    <li class="settings-item" onclick="alert('Language: English')">
                        <div class="settings-item-left">
                            <div class="settings-icon">🌐</div>
                            <div class="settings-text">
                                <h4>Language</h4>
                                <p>English (US)</p>
                            </div>
                        </div>
                        <div class="settings-arrow">›</div>
                    </li>
                </ul>

                <h3 class="section-title">Support</h3>
                <ul class="settings-list">
                    <li class="settings-item" onclick="alert('Help Center')">
                        <div class="settings-item-left">
                            <div class="settings-icon">❓</div>
                            <div class="settings-text">
                                <h4>Help Center</h4>
                                <p>FAQs and support articles</p>
                            </div>
                        </div>
                        <div class="settings-arrow">›</div>
                    </li>
                    <li class="settings-item" onclick="alert('Contact: support@transitnav.com')">
                        <div class="settings-item-left">
                            <div class="settings-icon">💬</div>
                            <div class="settings-text">
                                <h4>Contact Support</h4>
                                <p>Get help from our team</p>
                            </div>
                        </div>
                        <div class="settings-arrow">›</div>
                    </li>
                    <li class="settings-item" onclick="alert('Version 1.0.0')">
                        <div class="settings-item-left">
                            <div class="settings-icon">ℹ️</div>
                            <div class="settings-text">
                                <h4>About</h4>
                                <p>App version 1.0.0</p>
                            </div>
                        </div>
                        <div class="settings-arrow">›</div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- BOTTOM NAVIGATION -->
        <div class="bottom-nav" id="bottomNav">
            <div class="nav-item active" onclick="navigateTo('home')">
                <div class="nav-icon">🏠</div>
                <div class="nav-label">Home</div>
            </div>
            <div class="nav-item" onclick="navigateTo('tracker')">
                <div class="nav-icon">🚌</div>
                <div class="nav-label">Tracker</div>
            </div>
            <div class="nav-item" onclick="navigateTo('saved')">
                <div class="nav-icon">⭐</div>
                <div class="nav-label">Saved</div>
            </div>
            <div class="nav-item" onclick="navigateTo('settings')">
                <div class="nav-icon">⚙️</div>
                <div class="nav-label">Settings</div>
            </div>
        </div>
    </div>

    <script>
        // Simple page navigation – home / tracker / saved / settings
        function navigateTo(pageName) {
            // Hide all pages
            document.querySelectorAll('.page').forEach(page => {
                page.classList.remove('active');
            });
            
            // Show selected page
            const target = document.getElementById('page-' + pageName);
            if (target) {
                target.classList.add('active');
            }

            // Update nav active state
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });

            const navItems = document.querySelectorAll('.nav-item');
            navItems.forEach((item) => {
                if ((pageName === 'home' && item.textContent.includes('Home')) ||
                    (pageName === 'tracker' && item.textContent.includes('Tracker')) ||
                    (pageName === 'saved' && item.textContent.includes('Saved')) ||
                    (pageName === 'settings' && item.textContent.includes('Settings'))) {
                    item.classList.add('active');
                }
            });
        }
    </script>
</body>
</html>
