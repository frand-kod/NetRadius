<script setup>
defineProps({ status: String });
</script>

<template>
    <span :class="{
        'bg-green-100 text-green-700': ['Active', 'Paid', 'Connected', 'Used'].includes(status),
        'bg-red-100 text-red-700': ['Banned', 'Disabled', 'Cancelled', 'Failed'].includes(status),
        'bg-amber-100 text-amber-700': ['Inactive', 'Suspended', 'Pending', 'Waiting', 'Unused'].includes(status),
        'bg-gray-100 text-gray-700': ['Limited', 'Expired'].includes(status),
    }" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold">
        {{ status }}
    </span>
</template>
