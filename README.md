# smart-municipality (login)

## Run locally (XAMPP)

1. Copy configuration:
   - Copy `config/config.example.php` to `config/config.php`
2. Edit `config/config.php` with your MySQL credentials and database name.
3. Put this project under your XAMPP `htdocs` and open:
   - Recommended (no spaces): `http://localhost/page-login/index.php?route=login`
   - If your folder name contains a space, Apache may 404 on `%20`. Rename the folder or create a no-space alias (e.g. a Windows junction named `page-login`).

## Database

- Import `config/schema.sql` into your MySQL database.

If login/signup succeeds but you can never log in, your DB may be truncating password hashes.
Make sure the password column is big enough:

```sql
ALTER TABLE utilisateur MODIFY mdp VARCHAR(255) NOT NULL;
```
