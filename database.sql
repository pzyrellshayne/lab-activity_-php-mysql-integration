CREATE DATABASE IF NOT EXISTS cics_sc_simple CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cics_sc_simple;

DROP TABLE IF EXISTS files;
DROP TABLE IF EXISTS events;

CREATE TABLE events (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(100) NOT NULL,
  event_date  DATE         NOT NULL,
  venue       VARCHAR(100) NOT NULL DEFAULT '',
  status      ENUM('Upcoming','Done','Cancelled') NOT NULL DEFAULT 'Upcoming',
  description TEXT,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE files (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  title         VARCHAR(100) NOT NULL,
  category      VARCHAR(40)  NOT NULL DEFAULT 'Other',
  original_name VARCHAR(255) NOT NULL,
  stored_name   VARCHAR(80)  NOT NULL,
  size          INT          NOT NULL,
  event_id      INT          NULL,
  uploaded_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_file_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO events (title, event_date, venue, status, description) VALUES
('Weekly officers meeting',                  DATE_ADD(CURDATE(), INTERVAL 2 DAY),  'CICS Student Council Room', 'Upcoming', 'Status updates and review of pending proposals.'),
('ASCEND General Assembly and Acquaintance', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'University Gymnasium',      'Upcoming', 'General assembly and acquaintance program for CICS students.'),
('Orientation for new officers',             DATE_SUB(CURDATE(), INTERVAL 6 DAY),  'CICS Building, Room 204',   'Done',     'Roles, responsibilities, and document workflow.');
