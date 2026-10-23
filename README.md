Disclaimer : This has been vibecoded.
# Fixed by Waveform

A community log of bugs fixed after being discussed on Waveform’s “Did They Even Test This?” segment.

Built with procedural PHP, MySQL, HTML, and CSS. Includes public listings, search, registration, login, and contribution management with soft deletion and restoration.

The database structure is provided separately in `database/schema.sql`. Local database settings belong in `config.php`, which is excluded from Git.

## Account setup and migration

Registration requires only a username and a nonempty password. Passwords have no application-level length or complexity rules and are stored as hashes.

For a new database, import `database/schema.sql`. For an existing database using the old name/email fields, back up the database and run `database/migrate_username_login.sql` once instead. Existing accounts use their previous email address as their username, with the same password. The migration removes the old display-name column; contributions and ownership are preserved.

After updating, reload the login or registration page before submitting. The app uses its own session cookie and retains CSRF protection. If sessions still fail, ensure cookies are enabled, use the same hostname throughout, and check that PHP can write to its configured `session.save_path` directory.
