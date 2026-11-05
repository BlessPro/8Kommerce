#!/usr/bin/env bash
set -Eeuo pipefail

BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

sudo install -m 0644 "$BASE_DIR/systemd/8kommerce-backup.service" /etc/systemd/system/8kommerce-backup.service
sudo install -m 0644 "$BASE_DIR/systemd/8kommerce-backup.timer" /etc/systemd/system/8kommerce-backup.timer
sudo systemctl daemon-reload
sudo systemctl enable --now 8kommerce-backup.timer
sudo systemctl list-timers 8kommerce-backup.timer --no-pager
