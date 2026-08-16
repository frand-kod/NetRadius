---
paths:
  - 'resources/js/**'
---

# Js

## Use Notivue for in-app toast notifications
In-app toast notifications use the Notivue library (npm package `notivue`, registered in resources/js/app.js). Render <Notivue v-slot="item"><Notification :item="item" :theme="toastTheme" /></Notivue> in AdminLayout (theme follows app dark mode). Call push.success/error/info/warn from anywhere; flash success/error are auto-shown as toasts via a watcher in AdminLayout. Do not add a second toast library.
