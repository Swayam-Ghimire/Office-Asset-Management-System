<script setup>
defineProps({
    name:  { type: String, default: '' },
    size:  { type: String, default: 'sm' },  // sm | md | lg
});

const iconMap = {
    laptop:   'laptop',
    monitor:  'desktop',
    screen:   'desktop',
    mouse:    'computer-mouse',
    keyboard: 'keyboard',
    chair:    'chair',
    phone:    'mobile-screen',
    tablet:   'tablet-screen-button',
    printer:  'print',
    headset:  'headset',
    camera:   'camera',
    software: 'server',
    network:  'network-wired',
    usb:      'fa-brands fa-usb',
    cable:    'plug',
    bag:      'bag-shopping',
    desk:     'table-columns',
    charger:  'bolt',
};

const sizeMap = {
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-10 h-10',
};

function resolveIcon(categoryName) {
    if (!categoryName) return 'box';
    const n = categoryName.toLowerCase();
    for (const [key, icon] of Object.entries(iconMap)) {
        if (n.includes(key)) return icon;
    }
    return 'box';
}
</script>

<template>
    <fa-icon
        :icon="resolveIcon(name)"
        :class="sizeMap[size]"
    />
</template>