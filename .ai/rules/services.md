---
paths:
  - app/Services/NotificationTemplateService.php
  - app/Services/PasswordResetOtpService.php
---

# Services

## Notification messages come from DB templates, not hardcoded strings
All customer-facing WhatsApp/Telegram message text must be rendered via App\Services\NotificationTemplateService::render($event, $data). Event defaults + {placeholder} vars are defined in its EVENTS registry; stored templates live in tbl_appconfig (prefix "notif_"), edited by admins in Settings → Notification. Do NOT hardcode message strings in listeners/commands — add a new EVENTS entry instead. (OTP lives in PasswordResetOtpService, keep as-is.)

## OTP message uses the template system too
The OTP reset-password WhatsApp message is also rendered from the DB template system via NotificationTemplateService::render('otp', ...) — event 'otp' (vars: otp, ttl, username) is in NotificationTemplateService::EVENTS and editable in Settings → Notification. Do not hardcode the OTP text; use the template renderer.

## Every notification event has an on/off toggle checked before sending
Each notification event can be toggled on/off via NotificationTemplateService::isEnabled($event) (AppConfig key "notif_<event>_enabled", default on). Every sender (listeners, CheckExpiredPlans, PasswordResetOtpService) must check isEnabled() before sending — off means no message is sent. The toggle + per-event template are edited in Settings → Notification.
