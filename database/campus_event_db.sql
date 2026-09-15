-- =====================================================
-- Campus Event Registration System
-- Database: campus_event_db
-- =====================================================


-- =====================================================
-- TABLE: admins
-- Stores administrator accounts for system management
-- =====================================================
CREATE TABLE IF NOT EXISTS admins (
    id         INT(11)      NOT NULL AUTO_INCREMENT,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: events
-- Stores all campus events
-- =====================================================
CREATE TABLE IF NOT EXISTS events (
    id              INT(11)       NOT NULL AUTO_INCREMENT,
    event_name      VARCHAR(150)  NOT NULL,
    description     TEXT          NOT NULL,
    event_date      DATE          NOT NULL,
    event_time      TIME          NOT NULL,
    venue           VARCHAR(200)  NOT NULL,
    organizer       VARCHAR(150)  NOT NULL,
    max_participants INT(11)      NOT NULL DEFAULT 100,
    status          ENUM('upcoming','ongoing','completed','cancelled') NOT NULL DEFAULT 'upcoming',
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_status (status),
    INDEX idx_event_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: registrations
-- Stores participant registrations for events
-- Foreign key: event_id references events(id)
-- =====================================================
CREATE TABLE IF NOT EXISTS registrations (
    id               INT(11)      NOT NULL AUTO_INCREMENT,
    participant_name VARCHAR(100) NOT NULL,
    email            VARCHAR(150) NOT NULL,
    roll_no          VARCHAR(30)  NOT NULL,
    department       VARCHAR(50)  NOT NULL,
    year             VARCHAR(20)  NOT NULL,
    event_id         INT(11)      NOT NULL,
    registered_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_event_id (event_id),
    INDEX idx_roll_no (roll_no),
    CONSTRAINT fk_event
        FOREIGN KEY (event_id)
        REFERENCES events(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- SAMPLE DATA: admins
-- =====================================================

INSERT INTO admins (username, password)
VALUES ('admin', '$2y$10$GZo4SI1F/olhjFV6VXyL3uIaAD5h6qjByoOBSuWoKXNr3hnQ9ibo.')
ON DUPLICATE KEY UPDATE username=username;

-- =====================================================
-- SAMPLE DATA: events
-- =====================================================
INSERT INTO events
    (event_name, description, event_date, event_time, venue, organizer, max_participants, status)
VALUES
(
    'Tech Fest 2026',
    'A grand annual technical festival featuring competitions, workshops, project exhibitions, and expert guest lectures from the industry. Students from all departments are encouraged to participate and showcase their technical skills.',
    '2026-10-15',
    '09:00:00',
    'Main Auditorium & Tech Block',
    'CSE Department',
    200,
    'upcoming'
),
(
    'Hackathon 2026',
    '24-hour intensive hackathon where teams of 2–4 students solve real-world problems using technology. Prizes worth ₹50,000 are up for grabs. Themes include Smart Campus, Healthcare, and FinTech.',
    '2026-10-22',
    '08:00:00',
    'Computer Lab Block – Floors 2 & 3',
    'CSE AIML Department',
    120,
    'upcoming'
),
(
    'Coding Competition',
    'Individual competitive coding event with three rounds: basics, data structures, and advanced algorithms. Judged on correctness, efficiency, and time. Open to all years.',
    '2026-10-05',
    '10:00:00',
    'Computer Lab – Room 301',
    'IT Department',
    80,
    'upcoming'
),
(
    'Project Exhibition',
    'Annual undergraduate project showcase where students present their final-year and mini-projects to industry experts and faculty. Best projects win cash prizes and internship opportunities.',
    '2026-11-10',
    '10:00:00',
    'Main Hall & Gallery Area',
    'All Departments',
    250,
    'upcoming'
),
(
    'Volleyball Tournament',
    'Inter-department volleyball tournament with knockout rounds. Each department may field one team of 6 players. Medals and certificates will be awarded to top 3 teams.',
    '2026-10-18',
    '07:30:00',
    'Sports Ground – Court 1',
    'Physical Education Dept.',
    60,
    'upcoming'
),
(
    'Cultural Fest – Utsav 2026',
    'Annual cultural extravaganza featuring classical dance, street play, singing, stand-up comedy, and fashion show. Open to all students. Come celebrate talent and diversity!',
    '2026-11-28',
    '05:00:00',
    'Open Air Amphitheatre',
    'Student Council',
    300,
    'upcoming'
);

-- =====================================================
-- SAMPLE DATA: registrations (dummy participants)
-- =====================================================
INSERT INTO registrations
    (participant_name, email, roll_no, department, year, event_id)
VALUES
('Aarav Sharma',   'aarav.sharma@college.edu',   'CSE2023001', 'CSE',        'Third Year',  1),
('Priya Patel',    'priya.patel@college.edu',    'IT2024012',  'IT',          'Second Year', 1),
('Rohan Mehta',    'rohan.mehta@college.edu',    'AIML2023007','CSE AIML',   'Third Year',  2),
('Sneha Desai',    'sneha.desai@college.edu',    'CSE2022034', 'CSE',        'Final Year',  3),
('Arjun Nair',     'arjun.nair@college.edu',     'MECH2024021','Mechanical', 'Second Year', 5),
('Kavya Reddy',    'kavya.reddy@college.edu',    'CSE2025003', 'CSE',        'First Year',  6),
('Vivek Joshi',    'vivek.joshi@college.edu',    'IT2023019',  'IT',          'Third Year',  2),
('Ananya Singh',   'ananya.singh@college.edu',   'ETC2022008', 'E&TC',       'Final Year',  4);
