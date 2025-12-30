<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();
const product = computed(() => page.props.product);
const recommendedProducts = computed(() => page.props.recommended ?? []);
const cartItem = computed(() => page.product.value?.cart_items?.[0] ?? null);

const errorMessage = ref('');
const successMessage = ref(page.flash.success ?? '');
const isLoading = ref(false);
const qtyCount = ref(1);

watch(() => cartItem.value?.quantity, (val) => {
    qtyCount.value = val ?? 1;
}, { immediate: true });

const decrementCount = () => {
    successMessage.value = '';
    errorMessage.value = '';

    if (isLoading.value
    || (qtyCount.value - 1) < 1)
    return;

    qtyCount.value = qtyCount.value - 1;
};
const incrementCount = () => {
    successMessage.value = '';
    errorMessage.value = '';

    if (isLoading.value
    || (qtyCount.value + 1) > product.value.stock_quantity)
    return;

    qtyCount.value = qtyCount.value + 1;
}
const buyProductNow = () => {
    window.alert('Congratulation, the button is working')
};
const upsertProductToCart = () => {
    if (isLoading.value
    || qtyCount.value < 1
    || cartItem.value && qtyCount.value === cartItem.value.quantity
    || qtyCount.value > product.value.stock_quantity)
        return;

    isLoading.value = true;

    successMessage.value = '';
    errorMessage.value = '';

    router.put(`/cart/items/${product.value.id}`, {
        count: qtyCount.value
    }, {
        preserveScroll: true,
        preserveState: true, // Reload product to reflect authoritative server state (e.g. stock or availability changes)
        onError: (err) => {
            console.error('Error when upsert product to cart:', err);

            if (err.count) {
                errorMessage.value = err.count;
            } else {
                errorMessage.value = err ?? 'Sorry, something went wrong, please try again later';
            }

            router.reload({
                only: ['product'] // Reload product, in case if it's deleted, stock change, etc
            });
        },
        onSuccess: () => {
            router.reload({
                only: ['product', 'flash'],
            });
        },
        onFinish: () => {
            isLoading.value = false;
        }
    });
};
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
                    <!-- Product gallery -->
                    <div
                        class="flex flex-col product-card relative
                        md:w-[75%]
                        lg:w-2/3"
                    >
                        <div
                            class="product-image-placeholder"
                        >
                            <!--  -->
                        </div>
                    </div>

                    <div
                        class="flex flex-col items-start pt-2 px-4 w-full
                        sm:px-0"
                    >
                        <div
                            class="flex flex-col mb-2
                            sm:flex-col-reverse"
                        >
                            <div class="flex flex-col gap-y-1.5">
                                <h1 class="font-semibold text-3xl">{{ product.name }}</h1>

                                <div class="divide-x divide-gray-300 flex flex-row text-lg">
                                    <span class="pr-2">4.9 (3 reviews)</span>

                                    <span class="pl-2">2 sold</span>
                                </div>
                            </div>
                        </div>

                        <h2 class="font-semibold pb-2 text-3xl text-blue-600">${{ product.price }}</h2>

                        <div v-if="product.stock_quantity < 1">
                            Sorry, but no stock available at this moment
                        </div>
                        <div
                            v-else
                            class="flex flex-col items-start w-full"
                        >
                            <div>
                                Available stock: {{ product.stock_quantity }}
                            </div>

                            <div
                                v-if="cartItem"
                                class="border border-yellow-600 flex-grow-0 flex-shrink-0 mb-4 mt-2 p-2 rounded-lg text-yellow-800"
                            >
                                You already had {{ cartItem.quantity }} in your cart
                            </div>

                            <div class="flex flex-col gap-4 items-start w-full">
                                <div class="flex flex-row gap-4 items-center w-full">
                                    <button
                                        @click="decrementCount"
                                        type="button"
                                        class="bg-white border border-stone-300 flex-grow-0 flex-shrink-0 font-bold px-3 py-1 rounded-lg text-2xl"
                                    >-</button>

                                    <input
                                        v-model.number="qtyCount"
                                        :max="product.stock_quantity"
                                        :min="1"
                                        type="number"
                                        name="quantity"
                                        id="quantity"
                                        class="border border-stone-300 flex-grow px-4 py-1.5 rounded-lg text-center text-lg
                                        md:flex-grow-0"
                                    />

                                    <button
                                        @click="incrementCount"
                                        type="button"
                                        class="bg-white border border-stone-300 flex-grow-0 flex-shrink-0 font-bold px-3 py-1 rounded-lg text-2xl"
                                    >+</button>
                                </div>
    
                                <div class="flex flex-row gap-4 items-center w-full
                                md:items-start">
                                    <button
                                        @click="buyProductNow"
                                        :disabled="isLoading"
                                        class="bg-blue-600 font-semibold px-4 py-2 text-white rounded-lg w-full
                                        md:w-auto"
                                    >
                                        <template v-if="isLoading">
                                            Loading...
                                        </template>
                                        <template v-else>
                                            Buy now
                                        </template>
                                    </button>

                                    <button
                                        @click="upsertProductToCart"
                                        :disabled="isLoading"
                                        class="border border-blue-600 font-semibold px-4 py-2 text-blue-600 rounded-lg w-full
                                        md:w-auto"
                                    >
                                        <template v-if="isLoading">
                                            Loading...
                                        </template>
                                        <template v-else-if="cartItem">
                                            Update cart
                                        </template>
                                        <template v-else>
                                            Add to Cart
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Success message -->
                        <div
                            v-if="successMessage"
                            class="bg-green-100 border border-green-600 bottom-4 fixed font-semibold mt-2 p-2 rounded-lg text-center text-green-600 w-[calc(100%-28px)] z-10
                            sm:w-[calc(100%-48px)]
                            md:border-0 md:border-transparent md:bottom-auto md:p-0 md:static md:w-auto"
                        >
                            {{ successMessage }}
                        </div>
                        <div
                            v-else-if="errorMessage"
                            class="bg-green-100 border border-red-600 bottom-4 fixed font-semibold mt-2 p-2 rounded-lg text-center text-red-600 w-[calc(100%-28px)] z-10
                            sm:w-[calc(100%-48px)]
                            md:border-0 md:border-transparent md:bottom-auto md:p-0 md:static md:w-auto"
                        >
                            {{ errorMessage }}
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
                            <ProductCard
                                v-for="recommendedProduct in recommendedProducts"
                                :key="recommendedProduct.id"
                                :product="recommendedProduct"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
