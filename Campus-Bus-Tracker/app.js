// Transit Navigator - Main Application JavaScript

// 🔹 Navigation Function
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
        if (item.textContent.toLowerCase().includes(pageName) || 
            (pageName === 'home' && item.textContent.includes('Home')) ||
            (pageName === 'tracker' && item.textContent.includes('Tracker')) ||
            (pageName === 'saved' && item.textContent.includes('Saved')) ||
            (pageName === 'settings' && item.textContent.includes('Settings'))) {
            item.classList.add('active');
        }
    });

    // When navigating to Saved page, load data from backend
    if (pageName === 'saved') {
        loadSavedRoutes();
        loadRecentTrips();
    }
}

// 🔹 Logout Function (front-end only; backend logout.php could destroy session)
function logout() {
    if (confirm('Are you sure you want to log out?')) {
        // Hide all pages
        document.querySelectorAll('.page').forEach(page => {
            page.classList.remove('active');
        });
        
        // Show login page
        const loginPage = document.getElementById('page-login');
        if (loginPage) {
            loginPage.classList.add('active');
        }
        
        // Hide header and bottom nav
        document.getElementById('mainHeader').style.display = 'none';
        document.getElementById('bottomNav').classList.add('hidden');
    }
}

// 🔹 Auto-refresh bus times (simulated)
function updateBusTimes() {
    // This would connect to a real API in production
    console.log('Bus times updated');
}

// 🔹 Search Routes (hook for backend integration)
function searchRoutes() {
    const destination = document.getElementById('searchInput')?.value || '';
    const from = document.getElementById('fromInput')?.value || '';

    // For now, just log + alert; Member 3 can replace this with a real fetch()
    console.log('Searching routes for:', { from, destination });

    // Example placeholder for backend call:
    // fetch('search_routes.php', {
    //   method: 'POST',
    //   headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    //   body: new URLSearchParams({ from, destination })
    // })
    // .then(res => res.json())
    // .then(data => { /* render results */ });

    alert('Searching for routes... (demo)');
}

// 🔹 Toggle Favorite Route (Save / Remove in DB via PHP)
function toggleFavoriteRoute(routeId) {
    // Simple example: send POST to save_route.php
    fetch('save_route.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ route_id: routeId })
    })
    .then(response => response.text())
    .then(text => {
        console.log('save_route.php response:', text);
        alert('Route ' + routeId + ' saved/updated (demo).');
        // Member 3 can change this to handle "toggled off" as well
    })
    .catch(err => {
        console.error('Error saving route:', err);
        alert('Error saving route. Please try again.');
    });
}

// 🔹 Load Saved Routes from Backend (Member 3 will implement PHP)
function loadSavedRoutes() {
    const container = document.getElementById('savedRoutesContainer');
    if (!container) return;

    // Member 3: implement get_saved_routes.php to return JSON like:
    // [ { route_id, name, description, stats }, ... ]
    fetch('get_saved_routes.php')
        .then(res => {
            if (!res.ok) throw new Error('Network error');
            return res.json();
        })
        .then(routes => {
            console.log('Loaded saved routes:', routes);

            if (!Array.isArray(routes) || routes.length === 0) {
                container.innerHTML = '<p style="color:#888;">No saved routes yet.</p>';
                return;
            }

            container.innerHTML = routes.map(route => `
                <div class="saved-route" data-route-id="${route.route_id}">
                    <div class="saved-route-info">
                        <h3>${route.name}</h3>
                        <p>${route.description || ''}</p>
                        <p style="color: #d4af37; margin-top: 5px;">
                            ${route.stats || ''}
                        </p>
                    </div>
                    <div class="favorite-icon" onclick="toggleFavoriteRoute(${route.route_id})">⭐</div>
                </div>
            `).join('');
        })
        .catch(err => {
            console.error('Error loading saved routes:', err);
            // On error, do nothing and let static fallback remain
        });
}

// 🔹 Load Recent Trips from Backend
function loadRecentTrips() {
    const container = document.getElementById('recentTripsContainer');
    if (!container) return;

    // Member 3: implement get_trip_history.php to return JSON like:
    // [ { time_label, route_name, description, duration }, ... ]
    fetch('get_trip_history.php')
        .then(res => {
            if (!res.ok) throw new Error('Network error');
            return res.json();
        })
        .then(trips => {
            console.log('Loaded recent trips:', trips);

            if (!Array.isArray(trips) || trips.length === 0) {
                container.innerHTML = '<p style="color:#888;">No recent trips yet.</p>';
                return;
            }

            container.innerHTML = trips.map(trip => `
                <div class="route-card">
                    <div class="route-info">
                        <div class="route-destination">${trip.time_label}</div>
                        <div style="margin-top: 8px;"><strong>${trip.route_name}</strong> - ${trip.description || ''}</div>
                        <div style="color: #888; font-size: 13px; margin-top: 5px;">
                            Duration: ${trip.duration || ''}
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(err => {
            console.error('Error loading recent trips:', err);
            // On error, keep static fallback
        });
}

// 🔹 Initialize app
document.addEventListener('DOMContentLoaded', function() {
    console.log('Transit Navigator initialized');
    
    // Set up auto-refresh for bus times (every 30 seconds)
    setInterval(updateBusTimes, 30000);
});