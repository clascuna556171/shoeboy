# Deployment Guide

The Shoe Boy can run in three shapes. Pick one:

| # | Topology | Best for | Cost |
|---|----------|----------|------|
| A | Local PC on the shop WiFi | Single location, LAN only | Free |
| B | Cloud (Render + managed Postgres) | Access from anywhere, always on | ~$0–7/mo |
| C | Local PC + Tailscale | LAN speed **and** secure remote access | Free |

The repository ships a **Dockerfile** and a **render.yaml** blueprint for Option B.

---

## A — Local PC on the shop WiFi

1. Install **PHP 8.3+** (with `pdo_sqlite`, `zip`, `mbstring`, `openssl`), **Composer 2**, **Node 20+**.
2. Clone, install, and prepare the app:
   ```powershell
   git clone https://github.com/clascuna556171/shoeboy.git
   cd shoeboy
   composer install
   copy .env.example .env
   php artisan key:generate
   New-Item -ItemType File -Path database\database.sqlite -Force
   php artisan migrate --seed
   npm install; npm run build
   ```
3. Serve to the LAN (not just localhost):
   ```powershell
   php artisan serve --host=0.0.0.0 --port=8000
   ```
4. Set `APP_URL` in `.env` to the PC's LAN address, e.g. `http://192.168.1.50:8000`.
5. Allow inbound TCP 8000 through Windows Firewall:
   ```powershell
   New-NetFirewallRule -DisplayName "Shoe Boy" -Direction Inbound -Protocol TCP -LocalPort 8000 -Action Allow
   ```
6. Other devices on the same WiFi open `http://192.168.1.50:8000`.

**Scheduled jobs are required.** The reservation sweep releases lapsed holds and a daily snapshot is taken. On Windows, add a Task Scheduler task that runs every minute:
```
php artisan schedule:run
```
(or run `php artisan schedule:work` in a background window).

**Backups:** the daily snapshot lands in `storage/app/backups` (SQLite file copies). Copy that folder to a USB/cloud periodically. In-app restore and `.sqlite` import work in this mode.

> Change the seeded passwords (`admin@theshoeboy.com` / `staff@theshoeboy.com`, both `password`) before real use.

---

## B — Cloud (Render + Postgres)

The repo includes `Dockerfile`, `docker/entrypoint.sh`, and `render.yaml`.

1. Create a new Render **Blueprint** from the repo — it provisions a web service and a managed Postgres (`shoeboy-db`).
2. **Set `APP_KEY` as a secret.** `render.yaml` declares it with `sync: false`, so Render will prompt for a value. Generate one locally and paste it:
   ```powershell
   php artisan key:generate --show
   ```
3. On boot the entrypoint runs `config:cache`, `route:cache`, `view:cache`, `migrate --force`, and seeds only when the database is empty.
4. **Scheduled jobs:** Render does not run Laravel's scheduler automatically. Add a **Cron Job** pointing at the service that runs every minute:
   ```
   php artisan schedule:run
   ```
   Without this, reservations will not auto-release and daily backups will not run.
5. **Backups:** the image installs the **PostgreSQL 18 client** (`pg_dump`) from the PGDG repo, so it can dump any Render server (13–18) — the client must never be older than the server. Dumps are stored as `.sql` under `storage/app/backups`. In-app **restore/import** is SQLite-only — restore a Postgres dump with `psql` or Render's database tooling. Note the **free tier's disk is ephemeral**: snapshots are lost on redeploy/restart, so **create a backup, then download it immediately**.
6. The free plan sleeps when idle, so the first request after a while is slow.

### Environment notes
- `DB_CONNECTION=pgsql` with `DATABASE_URL` from the managed database.
- `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=sync`.
- `TRUSTED_PROXIES` — defaults to `*` (trust the single Render proxy). Tighten to the proxy CIDR if you front the app with your own gateway.

---

## C — Local PC + Tailscale (recommended for small shops)

Run Option A on the shop PC, then install **Tailscale** on the PC and on each staff/owner device. Any of your devices can then reach the app securely from anywhere — no port forwarding, no public exposure. Data stays in the shop, and you keep LAN speed locally.

---

## Production checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_KEY` set as a secret (never committed)
- [ ] `APP_URL` matches the real public (or LAN) URL
- [ ] Default seed passwords changed
- [ ] Scheduler running (`schedule:run` every minute)
- [ ] Backups verified (create one, download it, confirm it is non-empty)
- [ ] HTTPS enabled (Render provides it automatically; on LAN use `mkcert` if needed)

## Mobile

The UI adapts to phones and tablets:

- **Phones** use a slide-in drawer for navigation and **list rows** (tap a row to expand; "Full details" opens a bottom sheet). All modals become bottom sheets.
- **Tablets/desktop** keep full tables with sticky headers on wide screens.
- Users can set their default **list layout** (Cards/Table) under **Settings**.
