<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductShowGallery from './Partials/ProductShowGallery.vue';
import ProductShowInfo from './Partials/ProductShowInfo.vue';
import ProductShowDetail from './Partials/ProductShowDetail.vue';
import ProductShowReviews from './Partials/ProductShowReviews.vue';
import ProductShowRecommended from './Partials/ProductShowRecommended.vue';
import { Head } from '@inertiajs/vue3';
import { useProduct } from '@/Composables/useProduct.js';

const {
    product,
} = useProduct();
</script>

<template>
    <Head :title="product.name" />

    <AuthenticatedLayout>
        <div
            class="pb-8
            sm:pt-8"
        >
            <div
                class="max-w-7xl mx-auto
                sm:px-6
                lg:px-8"
            >
                <div
                    class="flex flex-col w-full
                    md:flex-row md:gap-x-4 md:items-start"
                >
                    <ProductShowGallery />

                    <ProductShowDetail />
                </div>

                <ProductShowInfo />

                <ProductShowReviews />

                <ProductShowRecommended />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
