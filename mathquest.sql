-- ============================================================
--  MathQuest Database Schema
--  Import via: mysql -u root -p mathquest < mathquest.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS mathquest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mathquest;

-- ── Users ────────────────────────────────────────────────────
CREATE TABLE users (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  username     VARCHAR(50)  NOT NULL UNIQUE,
  password     VARCHAR(255) NOT NULL,          -- bcrypt hash
  full_name    VARCHAR(100) NOT NULL,
  role         ENUM('student','admin') NOT NULL DEFAULT 'student',
  avatar       VARCHAR(10)  DEFAULT '👨‍🎓',
  class_name   VARCHAR(50)  DEFAULT 'Grade 6A',
  created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
  last_login   DATETIME     DEFAULT NULL
);

-- ── Student stats ─────────────────────────────────────────────
CREATE TABLE student_stats (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT NOT NULL UNIQUE,
  xp              INT DEFAULT 0,
  level           INT DEFAULT 1,
  streak          INT DEFAULT 0,
  longest_streak  INT DEFAULT 0,
  problems_solved INT DEFAULT 0,
  problems_correct INT DEFAULT 0,
  last_active     DATE DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Problem attempts ──────────────────────────────────────────
CREATE TABLE attempts (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NOT NULL,
  subject      VARCHAR(50) NOT NULL,
  difficulty   ENUM('easy','medium','hard') NOT NULL,
  question     TEXT NOT NULL,
  user_answer  VARCHAR(255),
  correct_answer VARCHAR(255),
  is_correct   TINYINT(1) DEFAULT 0,
  xp_earned    INT DEFAULT 0,
  hint_used    TINYINT(1) DEFAULT 0,
  time_taken   INT DEFAULT 0,       -- seconds
  attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Assignments ───────────────────────────────────────────────
CREATE TABLE assignments (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  title        VARCHAR(150) NOT NULL,
  subject      VARCHAR(50)  NOT NULL,
  difficulty   ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
  num_problems INT DEFAULT 10,
  due_date     DATE,
  created_by   INT NOT NULL,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

-- ── Assignment submissions ────────────────────────────────────
CREATE TABLE submissions (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  assignment_id   INT NOT NULL,
  user_id         INT NOT NULL,
  score           INT DEFAULT 0,       -- percentage
  problems_done   INT DEFAULT 0,
  submitted_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_sub (assignment_id, user_id),
  FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)       REFERENCES users(id)       ON DELETE CASCADE
);

-- ── Course content ────────────────────────────────────────────
CREATE TABLE content (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  subject      VARCHAR(50) NOT NULL,
  title        VARCHAR(150) NOT NULL,
  type         ENUM('Lesson','Practice Set','Quiz','Video') NOT NULL DEFAULT 'Lesson',
  icon         VARCHAR(10) DEFAULT '📖',
  description  TEXT,
  created_by   INT NOT NULL,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

-- ── Badges ───────────────────────────────────────────────────
CREATE TABLE badges (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  slug  VARCHAR(50) UNIQUE NOT NULL,
  name  VARCHAR(100) NOT NULL,
  icon  VARCHAR(10),
  description VARCHAR(200)
);

CREATE TABLE user_badges (
  user_id    INT NOT NULL,
  badge_id   INT NOT NULL,
  earned_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, badge_id),
  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
  FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
);

-- ── Sessions (optional server-side sessions) ──────────────────
-- PHP's default file sessions are fine; this table is optional.
-- Uncomment if you want DB-backed sessions.
-- CREATE TABLE sessions (
--   id         VARCHAR(128) PRIMARY KEY,
--   user_id    INT NOT NULL,
--   data       TEXT,
--   expires_at DATETIME,
--   FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
-- );

-- ============================================================
--  Seed data
-- ============================================================

-- Badges
INSERT INTO badges (slug, name, icon, description) VALUES
('first_win',    'First Win',     '🥇', 'Solved your first problem'),
('sharpshooter', 'Sharpshooter',  '🎯', '90%+ accuracy over 10 problems'),
('lightning',    'Lightning',     '⚡', 'Solved a problem in under 30 seconds'),
('champion',     'Champion',      '🏆', 'Reached Level 10'),
('streak',       'Streak Master', '🔥', '7-day login streak'),
('scholar',      'Scholar',       '📚', 'Solved 100 problems'),
('dragon',       'Dragon Slayer', '🐉', 'Completed Algebra Castle quest'),
('wizard',       'Math Wizard',   '⚗',  'Mastered all 5 subjects');

-- Default admin account  (password: admin123)
INSERT INTO users (username, password, full_name, role, avatar) VALUES
('admin',   '$2y$12$YKD5P.eJm7Gs1qw6zVq8r.hX3YkNwZmQ9Lv2eU7TpAaS4Bx0CdKly', 'Mrs. Williams', 'admin', '👩‍🏫'),
('teacher', '$2y$12$YKD5P.eJm7Gs1qw6zVq8r.hX3YkNwZmQ9Lv2eU7TpAaS4Bx0CdKly', 'Mr. Smith',     'admin', '👨‍🏫');

-- Default student accounts  (password: student123)
INSERT INTO users (username, password, full_name, role, avatar, class_name) VALUES
('student', '$2y$12$3Xv9mN2kLpQ8Yw7jRt4uOOYq5sZhBdKlVnFaEiGcHwMxPbsTuA6ei', 'Alex M.',  'student', '👨‍🎓', 'Grade 6A'),
('sarah',   '$2y$12$3Xv9mN2kLpQ8Yw7jRt4uOOYq5sZhBdKlVnFaEiGcHwMxPbsTuA6ei', 'Sarah J.', 'student', '👩‍🎓', 'Grade 6A'),
('james',   '$2y$12$3Xv9mN2kLpQ8Yw7jRt4uOOYq5sZhBdKlVnFaEiGcHwMxPbsTuA6ei', 'James K.', 'student', '🧑‍🎓', 'Grade 6A');

-- Seed student stats
INSERT INTO student_stats (user_id, xp, level, streak, problems_solved, problems_correct, last_active)
SELECT id, 1250, 7, 6, 45, 41, CURDATE() FROM users WHERE username = 'student';

INSERT INTO student_stats (user_id, xp, level, streak, problems_solved, problems_correct, last_active)
SELECT id, 2340, 12, 9, 156, 153, CURDATE() FROM users WHERE username = 'sarah';

INSERT INTO student_stats (user_id, xp, level, streak, problems_solved, problems_correct, last_active)
SELECT id, 1680, 9, 4, 124, 114, CURDATE() FROM users WHERE username = 'james';

-- Seed first badge for demo student
INSERT INTO user_badges (user_id, badge_id)
SELECT u.id, b.id FROM users u, badges b
WHERE u.username = 'student' AND b.slug IN ('first_win','sharpshooter','lightning','champion','streak','scholar');

-- Default content
INSERT INTO content (subject, title, type, icon, created_by) VALUES
('Algebra',    'Intro to Variables',       'Lesson',       '📖', 1),
('Algebra',    'Solving Linear Equations', 'Practice Set', '🧮', 1),
('Algebra',    'Week 1 Quiz',              'Quiz',         '📝', 1),
('Arithmetic', 'Order of Operations',      'Lesson',       '📖', 1),
('Arithmetic', 'Multiplication Mastery',   'Practice Set', '🧮', 1),
('Geometry',   'Area & Perimeter',         'Lesson',       '📖', 1),
('Geometry',   'Angles & Lines',           'Lesson',       '📖', 1),
('Geometry',   'Shapes Quiz',              'Quiz',         '📝', 1);

-- Default assignments
INSERT INTO assignments (title, subject, difficulty, num_problems, due_date, created_by) VALUES
('Algebra Basics',     'Algebra',    'easy',   10, DATE_ADD(CURDATE(), INTERVAL 5 DAY),  1),
('Fraction Challenge', 'Fractions',  'medium', 15, DATE_ADD(CURDATE(), INTERVAL 9 DAY),  1),
('Geometry Quiz',      'Geometry',   'medium', 10, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1);
