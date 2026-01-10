<script setup>
import ProductCard from '@/Components/ProductCard.vue';

defineProps({
    items: { type: Array, required: true }
});
</script>

<template>
    <div
        class="mt-4 px-4
        sm:px-0"
    >
        <div class="bg-white p-4 rounded-lg shadow shadow-stone-300/60">
            <h2
                class="font-semibold mb-4 text-xl"
            >Recommended products</h2>

            <div
                class="grid grid-cols-2 gap-4 mt-4
                lg:grid-cols-3
                xl:grid-cols-5"
            >
                <ProductCard
                    v-for="product in $props.items"
                    :key="product.id"
                    :product="product"
                />
            </div>
        </div>
    </div>
</template>
