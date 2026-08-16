---
paths:
  - 'resources/js/Pages/Admin/**'
---

# Admin

## Use ConfirmModal.vue for all destructive confirmations
All destructive/inline-confirm actions (delete, order cancel) in admin pages must use the reusable Components/ConfirmModal.vue (open/close/confirm handlers + router.delete/form.post with onFinish), NOT the native browser confirm() or <Link method="delete">. Each CRUD index page wires a single ConfirmModal with openDelete(id)/confirmDelete(). Reuse it rather than re-implementing.
