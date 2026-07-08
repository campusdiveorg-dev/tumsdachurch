-- TUMSDA Church Website — MySQL Schema
-- Run once on a fresh database: mysql -u root -p tumsda < database/schema_mysql.sql

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ─────────────────────────────────────────────
-- USERS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)    NOT NULL,
    email       VARCHAR(150)    NOT NULL,
    password_hash VARCHAR(255)  NOT NULL,
    role        ENUM('admin','member') NOT NULL DEFAULT 'member',
    is_active   TINYINT(1)      NOT NULL DEFAULT 1,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- DEPARTMENTS & MINISTRIES
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS departments_ministries (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
    type                ENUM('department', 'ministry') NOT NULL,
    name                VARCHAR(150)    NOT NULL,
    description         TEXT,
    scripture_quote     TEXT,
    scripture_reference VARCHAR(100),
    external_link       VARCHAR(255),
    sort_order          INT             NOT NULL DEFAULT 0,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- LEADERSHIP
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS leadership (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)    NOT NULL,
    position    VARCHAR(150)    NOT NULL,
    photo_path  VARCHAR(255),
    statement   TEXT,
    sort_order  INT             NOT NULL DEFAULT 0,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- SERMONS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sermons (
    id           INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200)    NOT NULL,
    youtube_url  VARCHAR(255)    NOT NULL,
    description  TEXT,
    is_featured  TINYINT(1)      NOT NULL DEFAULT 0,
    published_at DATE,
    created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- EVENTS (Church Calendar)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS events (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200)    NOT NULL,
    event_date  DATE            NOT NULL,
    facilitator VARCHAR(150),
    description TEXT,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- WEEKLY MEETINGS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS weekly_meetings (
    id           INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
    day_of_week  ENUM('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
    time_range   VARCHAR(50)     NOT NULL,   -- e.g. "4:00–6:00 PM"
    program_name VARCHAR(150)    NOT NULL,
    sort_order   INT             NOT NULL DEFAULT 0,
    created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- RESOURCES
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS resources (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150)    NOT NULL,
    description TEXT,
    icon_path   VARCHAR(255),
    link_url    VARCHAR(255)    NOT NULL,
    category    VARCHAR(100),
    sort_order  INT             NOT NULL DEFAULT 0,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- MISSIONS (Evangelism)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS missions (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200)    NOT NULL,   -- e.g. "Balaga Mission 2026"
    theme_text  VARCHAR(200),              -- e.g. "Jesus is Coming"
    theme_verse VARCHAR(100),              -- e.g. "Revelation 22:12"
    theme_song  VARCHAR(100),              -- e.g. "SDAH 442"
    start_date  DATE,
    end_date    DATE,
    description TEXT,
    is_upcoming TINYINT(1)      NOT NULL DEFAULT 0,
    sort_order  INT             NOT NULL DEFAULT 0,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- PAYMENTS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS payments (
    id                     BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id                BIGINT UNSIGNED,                        -- NULL = anonymous/guest
    phone_number           VARCHAR(20)      NOT NULL,
    amount                 DECIMAL(10,2)    NOT NULL,
    purpose                ENUM('tithe','offering','mission_support','other') NOT NULL,
    status                 ENUM('pending','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
    mpesa_receipt_number   VARCHAR(50),
    checkout_request_id    VARCHAR(100),
    merchant_request_id    VARCHAR(100),
    raw_callback_payload   JSON,
    created_at             DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at             DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_checkout_request (checkout_request_id),
    CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- AUDIT LOG
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_log (
    id          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED,
    action      VARCHAR(100)     NOT NULL,   -- e.g. 'create', 'update', 'delete'
    entity      VARCHAR(100)     NOT NULL,   -- e.g. 'leadership', 'events'
    entity_id   BIGINT UNSIGNED,
    created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- SEED DATA — existing content migration
-- ─────────────────────────────────────────────

-- Departments & Ministries (from departments.php and ministries.php)
INSERT INTO departments_ministries (type, name, description, scripture_quote, scripture_reference, external_link, sort_order) VALUES
('department', 'Sabbath School',      'Home for all, a school where every believer is nurtured to grow as a disciple and to disciple others. Sabbath School provides a platform for studying God''s Word and growing in fellowship.',   'Let the word of Christ dwell in you richly in all wisdom, teaching and admonishing one another…',                           'Colossians 3:16',      NULL,                               1),
('department', 'Jewels',              'Precious gems of the church, representing the freshmen and all members who are nurtured to embrace Christ-like values. The Jewels Department is committed to shaping character and servant leadership.', 'Let no one despise your youth, but be an example to the believers in word, in conduct, in love, in spirit, in faith, in purity.','1 Timothy 4:12',      NULL,                               2),
('department', 'Adventist Men Organisation (AMO)', 'The muscle of the church, a brotherhood for spiritual growth, mentorship, and leadership in service.', 'Be watchful, stand firm in the faith, act like men, be strong.',                                               '1 Corinthians 16:13',  'https://tumsdaamo.netlify.app',    3),
('department', 'Adventist Ladies Organisation (ALO)', 'A family of beautiful hearts; women called to godliness, mentorship, and service.',                   'Charm is deceitful and beauty is passing, but a woman who fears the Lord, she shall be praised.',              'Proverbs 31:30',       NULL,                               4),
('department', 'Music',               'TUMSDA is home to maestros who not only understand music but offer it as heavenly ministry.',                         'Sing to Him a new song; play skilfully with a shout of joy.',                                                  'Psalm 33:3',           'https://youtube.com/@tumsdachurchchoir', 5),
('department', 'Communication',       'The voice of the church, tasked with telling the story of God''s work among His people.',                             'The Lord gave the word; great was the company of those who proclaimed it.',                                    'Psalm 68:11',          'https://www.youtube.com/@tumsda_church', 6),
('department', 'Deaconry',            'The hands and feet of the church, devoted to service, order, and hospitality.',                                       'For those who have served well as deacons obtain for themselves a good standing and great boldness in the faith.','1 Timothy 3:13',      NULL,                               7),
('department', 'Treasury',            'The storehouse of God''s blessings, entrusted with the faithful management of tithes, offerings, and financial stewardship.', 'Bring all the tithes into the storehouse, that there may be food in My house, and try Me now in this,',       'Malachi 3:10',         NULL,                               8),
('department', 'Medical Missionary',  'A department devoted to Christ''s method of ministry; meeting people''s physical, emotional, and spiritual needs.',   'Beloved, I pray that you may prosper in all things and be in health, just as your soul prospers.',             '3 John 1:2',           NULL,                               9),
('department', 'Publishing',          'Carrying forward the pen of inspiration, ensuring that truth-filled literature reaches families, campuses, and communities.', 'Write the vision and make it plain on tablets, that he may run who reads it.',                                'Habakkuk 2:2',         NULL,                              10),
('ministry',   'The Bible and Bible Alone (BBA)', 'A Bible study ministry whose main objective is learning, unlearning, and relearning Biblical truths and doctrines about Christ.', 'These were more fair-minded than those in Thessalonica, in that they received the word with all readiness, and searched the Scriptures daily to find out what was so.', 'Acts 17:11', NULL, 1),
('ministry',   'Amazing Grace',                   'A Bible study ministry devoted to instilling light and wisdom through prayerful study and deep discussion of the Scriptures and the Spirit of Prophecy.', 'But grow in the grace and knowledge of our Lord and Savior Jesus Christ. To Him be the glory both now and forever. Amen.', '2 Peter 3:18', NULL, 2),
('ministry',   'Hymns of Praise (HOP)',           'A singing group grounded in ministry through music, focusing on hymns that lift hearts to God and strengthen faith.',               'Let everything that has breath praise the Lord. Praise the Lord!',                                             'Psalm 150:6',  NULL, 3);

-- Leadership (from leadership.php)
INSERT INTO leadership (name, position, photo_path, statement, sort_order) VALUES
('Elder Cephas Mukaria',  'Chairperson, 1st Elder',                              'assets/img/Cephas.jpg',          'This is a faithful saying that in TUMSDA, hearts are transformed, edified, and lives forever changed by Christ''s radiant light.',  1),
('Elder Gibson Kiprono',  'Assistant Chairperson, Personal Ministries, 2nd Elder','assets/img/jpg/Gibson.jpg',      'Just as Jesus''s gentle arms, TUMSDA is a sweet haven of rest!',                                                                   2),
('Elder Daniel Muchoge',  'Assistant Chairperson, Planning, 3rd Elder',           'assets/img/jpg/Daniel.jpg',      'TUMSDA, a home of watchmen and light bearers!',                                                                                    3);

-- Weekly Meetings (from about.php)
INSERT INTO weekly_meetings (day_of_week, time_range, program_name, sort_order) VALUES
('Sunday',    '4:00–6:00 PM',  'Medical Missionary Training',  1),
('Sunday',    '4:00–6:00 PM',  'Church Choir Training',        2),
('Sunday',    '6:00–8:00 PM',  'HOP Training',                 3),
('Monday',    '4:30–5:30 PM',  'Jewels'' Meeting',             4),
('Monday',    '5:30–6:30 PM',  'BBA Ministry',                 5),
('Monday',    '6:30–7:30 PM',  'Amazing Grace Ministry',       6),
('Tuesday',   '4:30–5:30 PM',  'Evangelism',                   7),
('Tuesday',   '5:30–6:30 PM',  'BBA Ministry',                 8),
('Tuesday',   '6:30–7:30 PM',  'Amazing Grace Ministry',       9),
('Wednesday', '4:30–5:30 PM',  'AMO / ALO Meetings',           10),
('Wednesday', '5:30–6:30 PM',  'BBA Ministry',                 11),
('Wednesday', '6:30–7:30 PM',  'Amazing Grace Ministry',       12),
('Thursday',  '4:30–5:30 PM',  'Church Choir Training',        13),
('Thursday',  '5:30–6:30 PM',  'BBA Training',                 14),
('Thursday',  '6:30–7:30 PM',  'HOP Training',                 15),
('Friday',    '5:30–7:30 PM',  'Vespers',                      16),
('Saturday',  '8:00 AM–6:00 PM','Sabbath Program',             17);

-- Events (from about.php calendar)
INSERT INTO events (title, event_date, facilitator) VALUES
('Second Coming Sabbath',                     '2025-09-06', 'Drezzillah Ellidah'),
('Prayer and Fasting Sabbath',                '2025-09-13', 'Derrick Onyango'),
('Holy Communion',                            '2025-09-20', 'Ziwani SDA'),
('True Education Sabbath',                    '2025-09-27', 'Lovine Lucas'),
('Jewels'' Week (Sep 28 – Oct 4)',            '2025-09-28', 'Jewel''s Leader'),
('Jewels'' Sabbath',                          '2025-10-04', 'Amos Wangwe'),
('ALUMNI Week (Oct 5 – Oct 11)',              '2025-10-05', 'ALUMNI'),
('ALUMNI Sabbath',                            '2025-10-11', 'ALUMNI'),
('Sanctuary Sabbath',                         '2025-10-18', 'Hosea Chesigor'),
('CUCASO',                                    '2025-10-25', 'CUCASO'),
('Music Sabbath',                             '2025-11-01', 'Justice Nyaga'),
('Publishing Sabbath',                        '2025-11-08', 'Barack K''Owili'),
('Country Living Sabbath',                    '2025-11-15', 'Kinya Mogaka'),
('ALO Sabbath',                               '2025-11-22', 'Ravine Owiti'),
('Medical Missionary Training Week',          '2025-11-23', 'Medical Missionary Dept.'),
('Medical Missionary Sabbath',                '2025-11-29', 'Jim Ayodo'),
('Sabbath School Emphasis',                   '2025-12-06', 'Caleb Omariba'),
('Sealing Sabbath',                           '2025-12-13', 'Warren Asher'),
('Holy Communion',                            '2025-12-20', 'Ziwani SDA'),
('Challa Mission 2025 (Dec 21 – Jan 4)',      '2025-12-21', 'Mission Committee');

-- Mission
INSERT INTO missions (title, theme_text, theme_verse, theme_song, start_date, end_date, description, is_upcoming, sort_order) VALUES
('Balaga Mission 2026', 'Jesus is Coming', 'Revelation 22:12', 'SDAH 442', '2026-12-21', '2027-01-04',
 'As part of our annual tradition, TUMSDA will be heading out for a two-week evangelistic mission in Balaga, bringing the everlasting gospel to new communities and providing spiritual and practical aid where needed.',
 1, 1);
