<?php
session_start();
require 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);

$userInfo    = [];
$savedRoutes = [];
$tripHistory = [];

if ($isLoggedIn) {
    $uid = $_SESSION['user_id'];

    // User info
    $stmt = $conn->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $userInfo = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Saved routes
    $sql = "
        SELECT 
            r.route_id,
            r.route_name AS name,
            CONCAT(COALESCE(r.start_point, 'Unknown'), ' → ', COALESCE(r.end_point, 'Unknown')) AS description,
            CONCAT('Avg ', COALESCE(r.estimated_time, 0), ' min') AS stats
        FROM saved_routes sr
        JOIN routes r ON sr.route_id = r.route_id
        WHERE sr.user_id = ?
        ORDER BY sr.created_at DESC
    ";
    $sr = $conn->prepare($sql);
    $sr->bind_param("i", $uid);
    $sr->execute();
    $savedRoutes = $sr->get_result()->fetch_all(MYSQLI_ASSOC);
    $sr->close();

    // Trip history
    $sql = "
        SELECT 
            th.trip_date,
            r.route_name,
            CONCAT(COALESCE(r.start_point, 'Unknown'), ' → ', COALESCE(r.end_point, 'Unknown')) AS description,
            CONCAT(COALESCE(r.estimated_time, 0), ' minutes') AS duration
        FROM trip_history th
        JOIN routes r ON th.route_id = r.route_id
        WHERE th.user_id = ?
        ORDER BY th.trip_date DESC
        LIMIT 10
    ";
    $th = $conn->prepare($sql);
    $th->bind_param("i", $uid);
    $th->execute();
    $tripHistory = $th->get_result()->fetch_all(MYSQLI_ASSOC);
    $th->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transit Navigator - Bus Navigation App</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="app-container">
        <!-- HEADER (hidden on login if not logged in yet) -->
        <div class="header" id="mainHeader" style="<?php echo $isLoggedIn ? '' : 'display:none;'; ?>">
            <h1>Transit Navigator</h1>
            <div class="subtitle">YOUR SMART BUS COMPANION</div>
        </div>

        <!-- PAGE 1: LOGIN -->
        <div class="page page-login <?php echo $isLoggedIn ? '' : 'active'; ?>" id="page-login">
            <div class="content" style="display: flex; flex-direction: column; justify-content: center; min-height: 100vh;">

                <div style="text-align: center; margin-bottom: 50px;">
                    <div style="font-size: 80px; margin-bottom: 20px;">🚌</div>
                    <h1 style="font-size: 32px; margin-bottom: 10px; background: linear-gradient(135deg, #d4af37 0%, #ffd700 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        Transit Navigator
                    </h1>
                    <p style="color: #888;">Your Smart Bus Companion</p>
                </div>

                <!-- ⭐ REAL LOGIN FORM ⭐ -->
                <form action="login.php" method="POST" style="width: 100%;">

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; color: #d4af37; font-size: 14px; font-weight: 600;">
                            Email or Username
                        </label>

                        <input 
                            type="text" 
                            name="email"
                            placeholder="Enter your email" 
                            required
                            style="width: 100%; padding: 16px 20px; background: #1a1a1a; border: 2px solid #333; border-radius: 12px; color: #fff; font-size: 16px;">
                    </div>

                    <div style="margin-bottom: 30px;">
                        <label style="display: block; margin-bottom: 8px; color: #d4af37; font-size: 14px; font-weight: 600;">
                            Password
                        </label>

                        <input 
                            type="password" 
                            name="password"
                            placeholder="Enter your password" 
                            required
                            style="width: 100%; padding: 16px 20px; background: #1a1a1a; border: 2px solid #333; border-radius: 12px; color: #fff; font-size: 16px;">
                    </div>

                    <button class="btn-primary" type="submit" style="margin-top: 0;">Log In</button>
                </form>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="#" style="color: #d4af37; text-decoration: none; font-size: 14px;">Forgot Password?</a>
                </div>

                <div style="text-align: center; margin-top: 30px; padding-top: 30px; border-top: 1px solid #2a2a2a;">
                    <p style="color: #888; font-size: 14px; margin-bottom: 15px;">Don't have an account?</p>

                    <button class="btn-primary" 
                        onclick="alert('Sign up feature coming soon!')" 
                        style="background: transparent; border: 2px solid #d4af37; color: #d4af37;">
                        Sign Up
                    </button>
                </div>

            </div>
        </div>

        <!-- PAGE 2: HOME -->
        <div class="page <?php echo $isLoggedIn ? 'active' : ''; ?>" id="page-home">
            <div class="content">
                <div class="search-section">
                    <h2 class="section-title">Where to?</h2>
                    <div class="search-box">
                        <input type="text" placeholder="Enter destination or bus number..." id="searchInput">
                    </div>
                    <div class="search-box">
                        <input type="text" placeholder="From current location" id="fromInput">
                    </div>
                    <!-- Call searchRoutes() instead of just alert -->
                    <button class="btn-primary" onclick="searchRoutes()">Find Routes</button>
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

        <!-- PAGE 3: BUS TRACKER -->
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

        <!-- PAGE 4: SAVED ROUTES -->
        <div class="page" id="page-saved">
            <div class="content">
                <h2 class="section-title">My Saved Routes</h2>
                <p style="color: #888; margin-bottom: 25px; font-size: 14px;">Quick access to your favorite routes</p>

                <!-- Container for dynamic saved routes from DB -->
                <div id="savedRoutesContainer">
                    <!-- Static fallback examples (can be replaced by JS once backend is wired) -->
                    <div class="saved-route" data-route-id="15">
                        <div class="saved-route-info">
                            <h3>Route 15 - Downtown</h3>
                            <p>Main St → Downtown Terminal</p>
                            <p style="color: #d4af37; margin-top: 5px;">⏱️ Avg. 25 min | 🚏 8 stops</p>
                        </div>
                        <div class="favorite-icon" onclick="toggleFavoriteRoute(15)">⭐</div>
                    </div>

                    <div class="saved-route" data-route-id="42">
                        <div class="saved-route-info">
                            <h3>Route 42 - University</h3>
                            <p>Central → University Campus</p>
                            <p style="color: #d4af37; margin-top: 5px;">⏱️ Avg. 35 min | 🚏 12 stops</p>
                        </div>
                        <div class="favorite-icon" onclick="toggleFavoriteRoute(42)">⭐</div>
                    </div>

                    <div class="saved-route" data-route-id="88">
                        <div class="saved-route-info">
                            <h3>Route 88 - Express</h3>
                            <p>Express Downtown (Limited Stops)</p>
                            <p style="color: #d4af37; margin-top: 5px;">⏱️ Avg. 18 min | 🚏 4 stops</p>
                        </div>
                        <div class="favorite-icon" onclick="toggleFavoriteRoute(88)">⭐</div>
                    </div>
                </div>

                <h3 class="section-title" style="margin-top: 35px;">Recent Trips</h3>

                <!-- Container for dynamic recent trips -->
                <div id="recentTripsContainer">
                    <!-- Static fallback examples -->
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
                </div>

                <button class="btn-primary" onclick="alert('Export feature coming soon!')">Export Trip History</button>
            </div>
        </div>

        <!-- PAGE 5: SETTINGS -->
        <div class="page" id="page-settings">
            <div class="content">
                <div class="profile-header">
                    <div class="profile-avatar">👤</div>
                    <div class="profile-name">Isaac Walker</div>
                    <div class="profile-email">isaac.walker@email.com</div>
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

                <button class="btn-primary" onclick="logout()" style="background: transparent; border: 2px solid #d4af37; color: #d4af37; margin-top: 30px;">Log Out</button>
            </div>
        </div>

        <!-- BOTTOM NAVIGATION -->
        <div class="bottom-nav <?php echo $isLoggedIn ? '' : 'hidden'; ?>" id="bottomNav">
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
    
    <?php if ($isLoggedIn): ?>
<script>
    const USER         = <?= json_encode($userInfo); ?>;
    const SAVED_ROUTES = <?= json_encode($savedRoutes); ?>;
    const TRIP_HISTORY = <?= json_encode($tripHistory); ?>;
</script>
<?php endif; ?>

    <script src="app.js"></script>
</body>
</html>
