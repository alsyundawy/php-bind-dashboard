-- PHP-Bind-Dashboard SQLite Schema
-- Compatible with SQLite 3.35+

PRAGMA foreign_keys = ON;
PRAGMA journal_mode = WAL;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    username        TEXT    NOT NULL UNIQUE COLLATE NOCASE,
    email           TEXT    NOT NULL UNIQUE COLLATE NOCASE,
    password_hash   TEXT    NOT NULL,
    role            TEXT    NOT NULL DEFAULT 'viewer' CHECK(role IN ('admin','operator','viewer')),
    is_active       INTEGER NOT NULL DEFAULT 1,
    last_login_at   TEXT,
    login_attempts  INTEGER NOT NULL DEFAULT 0,
    locked_until    TEXT,
    created_at      TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at      TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- Zones metadata (actual zone data lives in BIND zone files)
CREATE TABLE IF NOT EXISTS zones (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT    NOT NULL UNIQUE COLLATE NOCASE,
    type            TEXT    NOT NULL DEFAULT 'master' CHECK(type IN ('master','slave','forward')),
    file_path       TEXT,
    is_reverse      INTEGER NOT NULL DEFAULT 0,
    serial          INTEGER NOT NULL DEFAULT 1,
    notes           TEXT,
    created_by      INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at      TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at      TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- Zone permissions (per-user access)
CREATE TABLE IF NOT EXISTS zone_permissions (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    zone_id         INTEGER NOT NULL REFERENCES zones(id) ON DELETE CASCADE,
    user_id         INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    can_view        INTEGER NOT NULL DEFAULT 1,
    can_edit        INTEGER NOT NULL DEFAULT 0,
    can_delete      INTEGER NOT NULL DEFAULT 0,
    UNIQUE(zone_id, user_id)
);

-- Activity / Audit log
CREATE TABLE IF NOT EXISTS activity_logs (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER REFERENCES users(id) ON DELETE SET NULL,
    action          TEXT    NOT NULL,
    target_type     TEXT,
    target_id       TEXT,
    details         TEXT,
    ip_address      TEXT,
    user_agent      TEXT,
    created_at      TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- Sessions (optional, can use PHP native)
CREATE TABLE IF NOT EXISTS sessions (
    id              TEXT    PRIMARY KEY,
    user_id         INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    ip_address      TEXT,
    user_agent      TEXT,
    payload         TEXT,
    last_activity   TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- Settings (key-value)
CREATE TABLE IF NOT EXISTS settings (
    key             TEXT    PRIMARY KEY,
    value           TEXT,
    updated_at      TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- Indexes
CREATE INDEX IF NOT EXISTS idx_zones_name ON zones(name);
CREATE INDEX IF NOT EXISTS idx_activity_user ON activity_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_activity_created ON activity_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_zone_perms_zone ON zone_permissions(zone_id);
CREATE INDEX IF NOT EXISTS idx_zone_perms_user ON zone_permissions(user_id);
