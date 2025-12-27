<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
const { props } = usePage();
const { current_page, data, last_page } = props.products;
</script>

<template>
    <Head title="Products" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl">New products</h2>

                <div
                    class="grid grid-cols-2 gap-4 mt-4
                    lg:grid-cols-3
                    xl:grid-cols-5"
                >
                    <Link
                        v-for="product in data"
                        :key="product.id"
                        :href="route('products.show', product.id)"
                        class="product-card"
                    >
                        <div class="product-image-placeholder">

                        </div>

                        <div class="flex flex-1 flex-col gap-y-2 mt-2">
                            <span class="font-semibold">{{ product.name }}</span>

                            <span class="text-sm">${{ product.price }}</span>
                        </div>
                    </Link>
                </div>

                <div class="flex flex-col gap-4 mt-4
                sm:flex-row sm:items-center sm:justify-end">
                    <Link
                        v-if="current_page > 1"
                        :href="route('products.index', { page: current_page - 1 })"
                        class="border border-blue-500 px-4 py-2 text-blue-500 rounded-lg"
                    >
                        Prev
                    </Link>
                    <Link
                        v-if="current_page < last_page"
                        :href="route('products.index', { page: current_page + 1 })"
                        class="border border-blue-500 px-4 py-2 text-blue-500 rounded-lg"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
