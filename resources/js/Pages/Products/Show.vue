<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const { flash, props } = usePage();
const mainProduct = props.product;
const recommendedProducts = props.recommended;

const isLoading = ref(false);
const qtyCount = ref(1);

function addToCart() {
    if (isLoading.value
    || qtyCount.value < 1
    || qtyCount.value > mainProduct.stock_quantity)
        return;

    isLoading.value = true;

    router.post(`/cart/${mainProduct.id}/add`, {
        count: qtyCount.value
    }, {
        async: true,
        preserveScroll: true,
        preserveState: true,
        onError: (err) => {
            console.error('Error:', err);

            // 
        },
        onSuccess: () => {
            // 
        },
        onFinish: () => {
            isLoading.value = false;
        }
    });
}

console.log('flash.success:', flash.success);
</script>

<template>
    <Head :title="mainProduct.name" />

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
                    <!-- Product gallery -->
                    <div
                        class="flex flex-col relative
                        md:w-[75%]
                        lg:w-1/2"
                    >
                        <div
                            class="product-image-placeholder"
                        >
                            <!--  -->
                        </div>
                    </div>

                    <div
                        class="flex flex-col pt-3 px-3 w-full
                        sm:px-0
                        md:pb-3
                        lg:items-start lg:pt-0"
                    >
                        <div
                            class="flex flex-col mb-2
                            sm:flex-col-reverse"
                        >
                            <div class="flex flex-col gap-y-1.5">
                                <h1 class="font-semibold text-3xl">{{ mainProduct.name }}</h1>

                                <div class="divide-x divide-stone-600 flex flex-row text-lg">
                                    <span class="pr-1.5">4.9 (3 reviews)</span>

                                    <span class="pl-1.5">2 sold</span>
                                </div>
                            </div>
                        </div>

                        <div v-if="mainProduct.stock_quantity < 1">
                            Sorry, but no stock available at this moment
                        </div>
                        <div v-else>
                            <div>
                                Available stock: {{ mainProduct.stock_quantity }}
                            </div>

                            <div class="flex flex-row gap-2 items-center mt-2">
                                <input
                                    v-model.number="qtyCount"
                                    :max="mainProduct.stock_quantity"
                                    :min="1"
                                    type="number"
                                    name="quantity"
                                    id="quantity"
                                    class="border border-stone-300 px-4 py-1.5 rounded-lg text-center text-lg"
                                />

                                <button
                                    @click="addToCart"
                                    :disabled="isLoading"
                                    class="bg-blue-500 px-4 py-2 text-white rounded-lg"
                                >
                                    Add to Cart
                                </button>
                            </div>
                        </div>

                        <!-- Success message -->
                        <div
                            v-if="flash.success"
                            class="font-semibold mt-2 rounded-lg text-green-600"
                        >
                            {{ flash.success }}
                        </div>
                    </div>
                </div>

                <!-- Product info -->
                <div
                    class="px-4 mt-4
                    sm:px-0"
                >
                    <div
                        class="bg-white p-4 rounded-lg shadow shadow-stone-300/60"
                    >
                        <h2 class="border-b-2 border-gray-300 font-semibold mb-4 pb-2 text-xl">Product info</h2>

                        <div>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Assumenda ipsum ipsam aut minus at laborum, pariatur maxime porro a minima alias dolor saepe aliquid, deleniti tempora, voluptas autem accusamus eveniet.
                        </div>
                    </div>
                </div>

                <!-- User reviews -->
                <div
                    class="px-4 mt-4
                    sm:px-0"
                >
                    <div
                        class="bg-white p-4 rounded-lg shadow shadow-stone-300/60"
                    >
                        <h2 class="border-b-2 border-gray-300 font-semibold mb-4 pb-2 text-xl">Product reviews</h2>

                        <div
                            class="flex flex-col gap-2"
                        >
                            <div
                                v-for="n in 3"
                                :key="n"
                                class="flex flex-1 flex-row gap-4"
                            >
                                <div
                                    class="bg-gray-300 flex-grow-0 flex-shrink-0 rounded-full size-12"
                                ></div>

                                <div class="flex flex-col items-start">
                                    <span class="font-semibold w-16">User {{ n }}</span>

                                    <div>Lorem ipsum dolor sit amet consectetur adipisicing elit. Placeat similique laborum veniam est magnam dicta nesciunt eligendi accusantium eaque deleniti aut commodi laboriosam, suscipit sint nam veritatis nemo ducimus dolore.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommended products -->
                <div
                    v-if="recommendedProducts.length"
                    class="px-4 mt-4
                    sm:px-0"
                >
                    <div
                        class="bg-white p-4 rounded-lg shadow shadow-stone-300/60"
                    >
                        <h2 class="font-semibold mb-4 text-xl">Recommended products</h2>

                        <div
                            class="grid grid-cols-2 gap-4 mt-4
                            lg:grid-cols-3
                            xl:grid-cols-5"
                        >
                            <Link
                                v-for="product in recommendedProducts"
                                :key="product.id"
                                :href="route('products.show', product.id)"
                                class="product-card"
                            >
                                <div class="product-image-placeholder">

                                </div>

                                <div class="flex flex-1 flex-col gap-y-2 mt-2">
                                    <span>{{ product.name }}</span>

                                    <span class="font-semibold text-lg">${{ product.price }}</span>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
