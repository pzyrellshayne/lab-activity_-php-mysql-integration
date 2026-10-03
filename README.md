# CICS-SC Management System

A simple PHP + MySQL web app for the Student Council. It manages two things: the **schedule** of events and the **files** (documents) of the council. The calendar shows the same events in a monthly view. Each part has full CRUD (create, read, update, delete), and a file can be linked to an event.

## Setup (XAMPP)
1. Copy this folder to `C:\xampp\htdocs\cics-sc-simple` (so `index.php` is directly inside it).
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`, click **Import**, and choose `database.sql`.
4. Open `http://localhost/cics-sc-simple/` in your browser.

If your MySQL root user has a password, change it at the top of `config.php`. Requires PHP 7.4 or newer.

## Files
| File | What it does |
|---|---|
| `database.sql` | Creates the database `cics_sc_simple` with the tables `events` and `files` and sample events |
| `config.php` | Database connection and small helper functions |
| `index.php` | Schedule: list, search, filter by status |
| `event_form.php` | Add or edit an event |
| `event_delete.php` | Delete an event |
| `calendar.php` | Month calendar of events with previous/next and jump-to-month; click + on a day to add an event there |
| `files.php` | Files: list, search, filter by category or event |
| `file_form.php` | Upload a file or edit its details |
| `file_download.php` | Download a file |
| `file_delete.php` | Delete a file |
| `header.php`, `footer.php`, `style.css` | Shared layout (Bootstrap 5 from a CDN) |
| `uploads/` | Where uploaded documents are stored |

## Database
`events` (id, title, event_date, venue, status, description) and `files` (id, title, category, original_name, stored_name, size, event_id, uploaded_at). `files.event_id` is a foreign key to `events.id`; deleting an event keeps its files and clears the link.

All queries use PDO prepared statements and all output is escaped with `htmlspecialchars`.
