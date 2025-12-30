<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    product: { type: Object, required: true }
});
</script>

<template>
    <Link
        :href="route('products.show', product.id)"
        class="border border-stone-300 flex flex-col overflow-hidden product-card rounded-lg w-full"
    >
        <span class="product-image-placeholder"></span>

        <span class="product-info">
            <span class="block break-words whitespace-normal w-full">{{ product.name }}</span>

            <span class="font-semibold text-blue-600 text-lg">${{ product.price }}</span>
        </span>
    </Link>
</template>
