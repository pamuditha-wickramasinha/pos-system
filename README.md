# POS System (Laravel)

Laravel re-platform of the legacy CodeIgniter 3 POS/inventory system. Same functionality and UI, rebuilt on current Laravel with proper migrations, Eloquent models, and best practices.

## Requirements

- PHP 8.2+
- MySQL/MariaDB (XAMPP)
- Composer

## Setup

1. Install dependencies:

   ```
   composer install
   ```

2. Copy the environment file and generate an app key:

   ```
   copy .env.example .env
   php artisan key:generate
   ```

3. Configure the database connections in `.env`:

   - `DB_*` — the new database this app runs on (create an empty schema, e.g. `laravel_pos`, first).
   - `LEGACY_DB_*` — read-only connection to the existing CodeIgniter app's database (only needed if importing legacy data).

4. Run migrations:

   ```
   php artisan migrate
   ```

5. Populate the database with one of the following:

   - **Fresh install** (demo admin user, no legacy data):

     ```
     php artisan db:seed
     ```

     Default login: `admin` / `admin123`.

   - **Migrate real data from the legacy CodeIgniter app**:

     ```
     php artisan legacy:import
     ```

     This truncates the target database and re-imports everything (users, roles/permissions, lookups, customers, suppliers, items, purchases, sales, returns, expenses, held invoices, SMS config) from the `legacy` connection, preserving original record IDs. Existing users can log in with their original CodeIgniter password immediately — it's verified against the legacy MD5 hash on first login and transparently rehashed to bcrypt.

6. Link the public storage disk (item images, uploaded logos):

   ```
   php artisan storage:link
   ```

7. Serve the app:

   ```
   php artisan serve
   ```

## Testing

```
php artisan test
```

Feature tests cover authentication (including the legacy-password fallback and rehash-on-login flow) and the core POS sale flow (stock deduction, payment status recomputation).

## Notes

- The `theme/` frontend assets are the original app's assets, reused as-is in `public/theme` — pages are Blade views wired to the same JS with JSON/plain-text AJAX responses.
- Database backups (Users > DB Backup) shell out to `mysqldump`; the binary path is auto-detected but can be adjusted in `UserController::findMysqldump()` if XAMPP is installed somewhere other than `C:\xampp`.
- Telegram "new item added" notifications (POS quick-add) are sent server-side. Configure `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHAT_ID` in `.env` to enable them; notifications are silently skipped if unset.
