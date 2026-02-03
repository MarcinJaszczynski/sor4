Deployment instructions
=======================

Prerequisites on target host:

- SSH access with a deploy user.
- PHP, Composer, and necessary PHP extensions.
- MySQL (or configured DB) accessible from app.

Quick manual deploy (from repo root):

```bash
bash scripts/deploy.sh deploy@server.com /var/www/sor4
```

GitHub Actions automated deploy:

1. Add repository secrets:
   - `DEPLOY_SSH_PRIVATE_KEY` — private key for deploy user (paste as secret)
   - `DEPLOY_USER` — e.g. `deploy`
   - `DEPLOY_HOST` — e.g. `example.com`
   - `DEPLOY_PATH` — remote path, e.g. `/var/www/sor4`

2. Push to `main` — workflow `.github/workflows/deploy.yml` will run and rsync files.

Notes:
- The workflow excludes `vendor` and `node_modules` — composer is run on the host.
- Ensure `storage` ownership/permissions are correct on remote after deploy.
- Backup DB before running migrations: `bash scripts/db_backup.sh /path/to/store/backups`.
