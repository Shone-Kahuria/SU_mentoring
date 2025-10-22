# ⚠️ IMPORTANT: Database Credentials Security

## The Problem
Team members were committing their database passwords to Git because credentials were hardcoded in `includes/db.php`.

## The Solution ✅
We now use environment variables stored in `includes/.env.php` which is:
- ✅ Listed in `.gitignore` (never committed to Git)
- ✅ Each team member has their own copy with their own password
- ✅ Database connection now reads from this file

## For All Team Members

### Setup (One Time)
1. Make sure `includes/.env.php` exists
2. Open `includes/.env.php` in your editor
3. Update line 14 with YOUR MySQL password:
   ```php
   define('DB_PASS', 'YOUR_MYSQL_PASSWORD_HERE');
   ```
4. Save the file
5. **DO NOT commit this file!** (Git will automatically ignore it)

### How It Works
- `includes/db.php` reads credentials from `includes/.env.php`
- Each developer has their own `.env.php` with their own password
- Git ignores `.env.php` so passwords are never committed
- `.env.example.php` shows the structure but has no real passwords

## Verification Checklist
- [ ] `includes/.env.php` exists
- [ ] `includes/.env.php` has YOUR password on line 14
- [ ] Run `git status` - you should NOT see `includes/.env.php` listed
- [ ] Database connection works at http://localhost/SU_mentoring/test_db_connection.php

## Common Issues

### "Configuration error. Missing .env.php"
**Solution**: Copy the example file
```bash
cp .env.example.php includes/.env.php
# Then edit includes/.env.php with your password
```

### "Database connection failed: Access denied"
**Solution**: Update your password in `includes/.env.php` (line 14)

### "I see includes/.env.php in git status"
**Solution**: It should be ignored. If not, run:
```bash
git rm --cached includes/.env.php
git commit -m "Remove .env.php from tracking"
```

## For Project Lead

If you need to update the shared configuration structure:
1. Edit `.env.example.php` (the template)
2. Commit `.env.example.php` to Git
3. Notify team to update their local `includes/.env.php` files

**NEVER commit `includes/.env.php`** - it contains real passwords!

---

**Last Updated**: October 22, 2025  
**Changed in Commit**: Remove hardcoded database passwords from db.php
