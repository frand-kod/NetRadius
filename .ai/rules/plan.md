---
paths:
  - 'resources/js/Pages/Admin/Plan/**'
---

# Plan

## Plan form uses FieldHelp tooltips + conditional visibility
Plan Create/Edit follows the original PHPNuxBill approach: a FieldHelp '?' tooltip (Components/FieldHelp.vue) explains confusing fields; radio groups for binary choices (prepaid, plan_type, typebp, limit_type, enabled); dynamic visibility — postpaid shows Expired Date + validity_unit becomes 'Period', typebp=Limited reveals limit fields, is_radius hides Router. Device is a dropdown of Mikrotik device names. Reuse FieldHelp and this conditional pattern for other resource forms.
