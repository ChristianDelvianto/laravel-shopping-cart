<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// import CartCheckoutModal from '@/Components/CartCheckoutModal.vue';
import CartEditModal from '@/Components/CartEditModal.vue';
import IconLoading from '@/svg/mdi/IconLoading.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const items = ref(page.props.items ?? []);

const computedItems = computed(() => {
    if (items.value.length === 0) return [];

    return items.value.map(item => {
        item.subtotal = (item.quantity * item.product.price).toFixed(2);

        return item;
    });
})
const subtotalAmount = computed(() => {
    if (items.value.length === 0) return 0;

    const total = items.value.reduce((acc, val) => acc + (val.product.price * val.quantity), 0);
    
    return total.toFixed(2);
});

const isEditing = ref(false);
const isLoading = ref(false);
const editItem = ref(null);

const checkoutCart = () => {
    if (isEditing.value || isLoading.value) return;

    isLoading.value = true;

    router.post(`/cart/items/checkout`, null, {
        onError: (err) => {
            console.error('Error checking out cart:', err);
        },
        onSuccess: () => {
            items.value = [];
        },
        onFinish: () => {
            isLoading.value = false;
        }
    });
};
const closeEditCartItemModal = () => {
    isEditing.value = false;
    editItem.value = null;
};
const editCartItem = (item) => {
    if (isEditing.value || isLoading.value) return;

    editItem.value = item;
    isEditing.value = true;
};
const updateCartItemQuantity = (quantity) => {
    const item = items.value.find(item => item.id === editItem.value.id);

    if (item) {
        item.quantity = quantity;
    }

    closeEditCartItemModal();
};
const removeCartItem = (cartItem) => {
    if (isLoading.value) return;

    items.value = items.value.filter(item => item.id !== cartItem.id);

    router.delete(`/cart/${cartItem.id}`, {
        onErr: (err) => {
            
        }
    });
};
</script>

<template>
    <Head title="My cart" />

    <AuthenticatedLayout>
        <div class="py-8">
            <div
                class="max-w-7xl mx-auto w-full
                sm:px-6
                lg:px-8"
            >
                <div
                    v-if="computedItems.length"
                    class="flex flex-col gap-4 min-w-[640px] overflow-x-auto w-full"
                >
                    <table
                        class="bg-white w-full"
                    >
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Available Stock</th>
                                <th>Price Per Unit</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="item in computedItems"
                                :key="item.id"
                            >
                                <!-- Id -->
                                <td
                                    class="px-3 py-1.5 text-center"
                                >{{ item.id }}</td>

                                <!-- Product info -->
                                <td>
                                    <Link
                                        :href="route('products.show', item.product.id)"
                                        class="flex flex-row gap-x-3 items-center p-3 product-card"
                                    >
                                        <span class="flex-grow-0 flex-shrink-0 product-image-placeholder rounded-lg size-20"></span>

                                        <span class="text-blue-600">{{ item.product.name }}</span>
                                    </Link>
                                </td>

                                <!-- Product stock quantity -->
                                <td
                                    class="text-center"
                                >
                                    {{ item.product.stock_quantity }}
                                </td>

                                <!-- Product unit price -->
                                <td
                                    class="text-center"
                                >
                                    ${{ item.product.price }}
                                </td>

                                <!-- Cart item quantity -->
                                <td
                                    class="text-center"
                                >
                                    {{ item.quantity }}
                                </td>

                                <!-- Subtotal amount -->
                                <td
                                    class="text-center"
                                >
                                    ${{ item.subtotal }}
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div
                                        class="flex flex-col gap-y-3 items-center"
                                    >
                                        <button
                                            v-if="item.product.status === 'active'"
                                            @click="editCartItem(item)"
                                            :disabled="isLoading"
                                            type="button"
                                            class="bg-green-100 border border-green-600 px-3 py-1.5 rounded-lg text-green-600 text-sm"
                                        >Edit</button>

                                        <button
                                            @click="removeCartItem(item)"
                                            :disabled="isLoading"
                                            type="button"
                                            class="bg-red-100 border border-red-600 px-3 py-1.5 rounded-lg text-red-600 text-sm"
                                        >Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex flex-col items-end w-full">
                        <div class="border-t-2 border-stone-300 flex flex-col items-end pt-2 w-full
                        sm:flex-row sm:items-center sm:justify-between">
                            Total
                            
                            <span class="font-semibold text-blue-600 text-2xl">${{ subtotalAmount }}</span>
                        </div>

                         <button
                            @click="checkoutCart"
                            :disabled="isLoading"
                            type="button"
                            class="bg-blue-600 flex-grow-0 flex-shrink-0 font-semibold px-4 py-2 rounded-lg text-white w-full
                            sm:min-w-32 sm:mt-4 sm:w-auto"
                        >
                            <IconLoading
                                v-if="isLoading"
                                :size="24"
                                color="#fff"
                                class="animate-spin mx-auto"
                            />
                            <template v-else>Checkout</template>
                        </button>
                    </div>
                </div>
                <div
                    v-else
                    class="flex flex-col gap-4"
                >
                    You don't have any products in your cart
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <CartEditModal
        v-if="isEditing && editItem"
        @close="closeEditCartItemModal"
        @success="updateCartItemQuantity"
        :item="editItem"
    />
</template>
