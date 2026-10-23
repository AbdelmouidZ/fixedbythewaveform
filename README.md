# Fixed by Waveform

A small, framework-free PHP/MySQL community log for bugs fixed after a Waveform “Did They Even Test This?” mention. Public listings, search, pagination, account registration/login, contribution creation/editing, and reversible removal. Contributors manage only their own entries. No JavaScript or build step.

## Run in XAMPP

1. Start Apache and MySQL.
2. Import `database/schema.sql` through phpMyAdmin or `mysql -u root -p < database/schema.sql`.
3. Copy `config.example.php` to `config.php` and enter your MySQL credentials. If XAMPP uses another port or socket, update the host, port, or socket settings.
4. Open `http://localhost/fixedbywaveform/` and register an account.

Requires PHP 8.1+ with mysqli (with mysqlnd) and mbstring, and MySQL 5.7+/MariaDB 10.2+. The SQL schema is separate and contains no fabricated fix records. The first account is an ordinary contributor; there is no administrator role.

## Behavior

Every entry requires a product, original bug, resolution, podcast source, evidence source, and mention/fix dates. Fix dates cannot precede the podcast mention or be in the future. Entries appear immediately and are labeled community reports, not independently verified fixes. A later fix does not prove the podcast caused it.

Remove asks for confirmation, then updates `fixes.active = 0`. Public queries exclude removed entries. Restore sets `active = 1`. There are no SQL DELETE statements. All modifying requests use POST and CSRF tokens; edits, removal and restore check the signed-in owner. Passwords use PHP password hashing, SQL uses prepared statements, and rendered values are HTML-escaped.

## Deployment

Use HTTPS. Keep `config.php` untracked and protect configuration, includes, SQL, and documentation from web access (the supplied `.htaccess` does this with Apache 2.4 and AllowOverride enabled; configure equivalent rules on other servers). Use a database account scoped to this database with SELECT, INSERT and UPDATE privileges. Session-based login throttling is basic; add server-level rate limits before exposing public registration at scale. Email verification, password recovery, moderation, and an audit history are outside this version.

## Context

Inspiration: https://www.youtube.com/watch?v=RQqlvCVQ-ZE . The exact video was unavailable during implementation; no episode-specific claims or actual fixes are invented. Waveform’s recurring segment is the context for this independent, unaffiliated community project.

## Procedural PHP

Database access uses procedural `mysqli_*` calls and prepared statements. Shared functions in `includes/bootstrap.php` handle the database, sessions, CSRF tokens, escaping, and validation. Pages use arrays and ordinary loops; there are no classes, object method calls, or frameworks. Connection settings are individual array values in `config.php`; if upgrading from the PDO version, replace the old `dsn` setting using `config.example.php` as a guide. The SQL schema is unchanged.
