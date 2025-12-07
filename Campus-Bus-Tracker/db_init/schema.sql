-- ============================================
-- RESET DATABASE (DROPS IN FK-SAFE ORDER)
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS alerts;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS bus_capacity_log;
DROP TABLE IF EXISTS trip_stop_times;
DROP TABLE IF EXISTS saved_routes;
DROP TABLE IF EXISTS trip_history;
DROP TABLE IF EXISTS trips;
DROP TABLE IF EXISTS bus_stops;
DROP TABLE IF EXISTS buses;
DROP TABLE IF EXISTS routes;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- TABLE: users
-- ============================================
CREATE TABLE users (
    user_id     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(100),
    role        ENUM('STUDENT','DRIVER','ADMIN') NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- TABLE: buses
-- ============================================
CREATE TABLE buses (
    bus_id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bus_number  VARCHAR(20) NOT NULL,
    capacity    INT NOT NULL,
    active      TINYINT(1) DEFAULT 1
);

-- ============================================
-- TABLE: routes
-- ============================================
CREATE TABLE routes (
    route_id        INT AUTO_INCREMENT PRIMARY KEY,
    route_name      VARCHAR(100) NOT NULL,
    start_point     VARCHAR(100),
    end_point       VARCHAR(100),
    estimated_time  INT
);

-- ============================================
-- TABLE: bus_stops
-- ============================================
CREATE TABLE bus_stops (
    stop_id              INT AUTO_INCREMENT PRIMARY KEY,
    stop_name            VARCHAR(100) NOT NULL,
    location_description VARCHAR(255),
    latitude             DECIMAL(9,6),
    longitude            DECIMAL(9,6)
);

-- ============================================
-- TABLE: trips  (uses buses + routes)
-- ============================================
CREATE TABLE trips (
    trip_id      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bus_id       BIGINT UNSIGNED NOT NULL,
    route_id     INT NOT NULL,
    start_time   DATETIME NOT NULL,
    end_time     DATETIME,
    is_emergency TINYINT(1) DEFAULT 0,
    CONSTRAINT fk_trips_bus   FOREIGN KEY (bus_id)   REFERENCES buses(bus_id),
    CONSTRAINT fk_trips_route FOREIGN KEY (route_id) REFERENCES routes(route_id)
);

-- ============================================
-- TABLE: trip_stop_times  (uses trips + bus_stops)
-- ============================================
CREATE TABLE trip_stop_times (
    trip_stop_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trip_id           BIGINT UNSIGNED NOT NULL,
    stop_id           INT NOT NULL,
    stop_sequence     INT NOT NULL,
    scheduled_arrival DATETIME,
    estimated_arrival DATETIME,
    message_sent      TINYINT(1) DEFAULT 0,
    CONSTRAINT fk_tst_trip FOREIGN KEY (trip_id) REFERENCES trips(trip_id),
    CONSTRAINT fk_tst_stop FOREIGN KEY (stop_id) REFERENCES bus_stops(stop_id)
);

-- ============================================
-- TABLE: alerts  (uses users + trip_stop_times)
-- ============================================
CREATE TABLE alerts (
    alert_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       BIGINT UNSIGNED,
    trip_stop_id  INT UNSIGNED,
    alert_type    VARCHAR(30) NOT NULL,
    message_text  TEXT NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at       TIMESTAMP NULL,
    CONSTRAINT fk_alert_user FOREIGN KEY (user_id)      REFERENCES users(user_id),
    CONSTRAINT fk_alert_tst  FOREIGN KEY (trip_stop_id) REFERENCES trip_stop_times(trip_stop_id)
);

-- ============================================
-- TABLE: feedback  (uses users)
-- ============================================
CREATE TABLE feedback (
    feedback_id  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      BIGINT UNSIGNED,
    message_text TEXT NOT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    handled      TINYINT(1) DEFAULT 0,
    CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- ============================================
-- TABLE: bus_capacity_log  (uses buses)
-- ============================================
CREATE TABLE bus_capacity_log (
    capacity_log_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bus_id              BIGINT UNSIGNED NOT NULL,
    log_time            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    seats_available     INT,
    passengers_on_board INT,
    CONSTRAINT fk_bcl_bus FOREIGN KEY (bus_id) REFERENCES buses(bus_id)
);

-- ============================================
-- TABLE: saved_routes  (uses users + routes)
-- ============================================
CREATE TABLE saved_routes (
    saved_id   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NOT NULL,
    route_id   INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sr_user  FOREIGN KEY (user_id)  REFERENCES users(user_id),
    CONSTRAINT fk_sr_route FOREIGN KEY (route_id) REFERENCES routes(route_id),
    CONSTRAINT uniq_user_route UNIQUE (user_id, route_id)
);

-- ============================================
-- TABLE: trip_history  (uses users + routes)
-- ============================================
CREATE TABLE trip_history (
    trip_id   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id   BIGINT UNSIGNED NOT NULL,
    route_id  INT NOT NULL,
    trip_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes     VARCHAR(255),
    CONSTRAINT fk_th_user  FOREIGN KEY (user_id)  REFERENCES users(user_id),
    CONSTRAINT fk_th_route FOREIGN KEY (route_id) REFERENCES routes(route_id)
);

-- ============================================
-- INSERT DATA: users
-- ============================================
INSERT INTO users (full_name, email, password, role) VALUES
('Jim Johnson',   'jjohnson2@umbc.edu', 'password123', 'STUDENT'),
('Bob Jones',     'bjones@umbc.edu',    'password456', 'STUDENT'),
('Charlie Davis', 'cdavis@umbc.edu',    'password789', 'DRIVER'),
('Diana Clark',   'dclark8@umbc.edu',   'admin123',    'ADMIN'),
('Ethan Brown',   'ebrown3@umbc.edu',   'password999', 'STUDENT'),
('Frank Wilson',  'fwilson6@umbc.edu',  'driverpass',  'DRIVER'),
('Grace Lee',     'glee1@umbc.edu',     'adminpass1',  'ADMIN'),
('Hannah Kim',    'hkim4@umbc.edu',     'studentpw8',  'STUDENT');

-- ============================================
-- INSERT DATA: buses
-- ============================================
INSERT INTO buses (bus_number, capacity, active) VALUES
('BUS-101', 40, 1),
('BUS-102', 23, 1),
('BUS-103', 52, 0),
('BUS-104', 40, 1),
('BUS-105', 52, 1);

-- ============================================
-- INSERT DATA: routes
-- ============================================
INSERT INTO routes (route_name, start_point, end_point, estimated_time) VALUES
('North Loop',       'Walker Apartments', 'Library',              25),
('South Loop',       'Library',           'Patapsco Hall',        30),
('Downtown Express', 'Campus Center',     'Downtown Baltimore',   40),
('Stadium Shuttle',  'Admin Building',    'Stadium',              15);

-- ============================================
-- INSERT DATA: bus_stops
-- ============================================
INSERT INTO bus_stops (stop_name, location_description, latitude, longitude) VALUES
('Walker Apartments', 'Entrance of WA',               39.258718, -76.714887),
('Library',           'Road behind library',          39.256561, -76.711511),
('Patapsco Hall',     'Next to Patapsco Hall',        39.255121, -76.707364),
('PAHB',              'Performing Arts & Humanities', 39.251373, -76.712127),
('RAC',               'Retriever Activities Center',  39.252405, -76.712003);

-- ============================================
-- INSERT DATA: trips
-- (assumes users/buses/routes have IDs starting at 1)
-- ============================================
INSERT INTO trips (bus_id, route_id, start_time, end_time, is_emergency) VALUES
(1, 1, '2025-09-01 08:00:00', '2025-09-01 08:30:00', 0),
(1, 1, '2025-09-01 09:00:00', '2025-09-01 09:30:00', 0),
(2, 2, '2025-09-01 08:15:00', '2025-09-01 08:45:00', 0),
(3, 3, '2025-09-01 17:00:00', '2025-09-01 17:40:00', 0),
(4, 4, '2025-09-01 19:00:00', '2025-09-01 19:20:00', 1);

-- ============================================
-- INSERT DATA: trip_stop_times
-- ============================================
INSERT INTO trip_stop_times (trip_id, stop_id, stop_sequence, scheduled_arrival, estimated_arrival, message_sent) VALUES
(1, 1, 1, '2025-09-01 08:05:00', '2025-09-01 08:07:00', 0),
(1, 2, 2, '2025-09-01 08:10:00', '2025-09-01 08:12:00', 0),
(2, 3, 1, '2025-09-01 09:10:00', '2025-09-01 09:12:00', 0),
(3, 4, 1, '2025-09-01 17:10:00', '2025-09-01 17:11:00', 1),
(4, 5, 1, '2025-09-01 19:10:00', '2025-09-01 19:12:00', 0);

-- ============================================
-- INSERT DATA: alerts
-- ============================================
INSERT INTO alerts (user_id, trip_stop_id, alert_type, message_text) VALUES
(1, 1, 'ARRIVAL', 'Bus arriving at Walker Apartments'),
(2, 2, 'DELAY',   'Bus delayed at Library'),
(3, 3, 'DELAY',   'Bus late at Patapsco'),
(4, 4, 'ARRIVAL', 'Bus arriving at PAHB'),
(5, 5, 'ARRIVAL', 'Bus approaching RAC');

-- ============================================
-- INSERT DATA: feedback
-- ============================================
INSERT INTO feedback (user_id, message_text, handled) VALUES
(4, 'The 9 o’clock bus is ugly',           0),
(2, 'Can the bus come 10 minutes early?',  0),
(3, 'This bus is always late',             1),
(2, 'Add a new stop at Humphreys?',        1),
(5, 'This system sucks, an app is better', 0);

-- ============================================
-- INSERT DATA: bus_capacity_log
-- ============================================
INSERT INTO bus_capacity_log (bus_id, log_time, seats_available, passengers_on_board) VALUES
(2, '2025-11-09 10:30:00', 15, 5),
(1, '2025-11-09 04:00:00', 20, 3),
(4, '2025-11-09 02:15:00', 20, 10),
(5, '2025-11-09 12:50:00', 10, 18),
(3, '2025-11-09 14:15:00', 15, 17);

-- ============================================
-- INSERT DATA: saved_routes
-- ============================================
INSERT INTO saved_routes (user_id, route_id) VALUES
(1, 1),
(1, 2),
(2, 3),
(3, 4);

-- ============================================
-- INSERT DATA: trip_history
-- ============================================
INSERT INTO trip_history (user_id, route_id, trip_date, notes) VALUES
(1, 1, '2025-09-01 08:35:00', 'Morning commute'),
(1, 2, '2025-09-01 12:30:00', 'Went to Patapsco'),
(2, 3, '2025-09-01 17:15:00', 'Evening express'),
(3, 4, '2025-09-01 19:25:00', 'Game shuttle');
