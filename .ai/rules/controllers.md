---
paths:
  - app/Http/Controllers/InvoiceController.php
---

# Controllers

## Invoice page is a DB-markdown template, not a hardcoded layout
The public invoice page is rendered from an admin-editable markdown template (tbl_appconfig key "invoice_template"), not a hardcoded Vue layout. Token {var} are filled per order in InvoiceController::buildData(); {payment_section} is auto-filled by status (pending → QR + payment_instructions, paid/cancelled → note). The {var} registry is InvoiceController::VARS, surfaced in Settings → Payment. To change invoice content, edit the template — do not hardcode into Public/Invoice.vue (it only renders the produced HTML).
