# FreeRADIUS REST Integration

> **The single source of truth for this integration is now
> `resources/markdown/freeradius.md`** (rendered live at `/freeradius` in the app).
> This file exists only as a legacy archive / English pointer.

The application exposes `POST /api/radius` (FreeRADIUS REST), protected by:

- a **shared-secret middleware** (`RADIUS_API_SECRET`, fail-closed), and
- **rate limiting** (60/min per IP).

Supported actions are `authenticate`, `authorize`, and `accounting`. The legacy
`post-auth` section is **not** supported by the controller and will be rejected.

## Quick reference

1. Install: `apt-get install -y freeradius freeradius-rest`
2. `clients.conf` → add each MikroTik/NAS with a strong `secret`.
3. `mods-enabled/rest` → set `connect_uri` to `https://your-app/api/radius`, enable
   `check_cert = yes` + `ca_file`, and append `&secret=<RADIUS_API_SECRET>` to every
   `data` payload.
4. `sites-enabled/default` → add `rest` in `authorize`, `authenticate`, `accounting`, and
   the `post-auth` block to map reply attributes.
5. Validate: `freeradius -XC`, then `systemctl restart freeradius`.
6. App side: set `RADIUS_API_SECRET` in `.env` (same value as step 3).

See `resources/markdown/freeradius.md` for the full step-by-step guide, code blocks,
workflow, troubleshooting, and hardening.
