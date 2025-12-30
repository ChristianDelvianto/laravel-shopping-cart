<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CartEditModal from '@/Components/CartEditModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const items = ref(page.props.items?.data ?? []);

const isEditing = ref(false);
const editItem = ref(null);

const closeEditCartItemModal = () => {
    isEditing.value = false;

    editItem.value = null;
};
const editCartItem = (item) => {
    if (isEditing.value) return;

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
const removeFromCart = (item) => {
    router.delete(`/cart/${item.id}/delete`, {
        onSuccess: () => {
            console.warn('Cart item deleted');
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
                <table class="bg-white w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Available Stock</th>
                            <th>Price Per Unit</th>
                            <th>Subtotal</th>
                            <th>Qty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="(item, index) in items"
                            :key="item.id"
                        >
                            <td class="px-3 py-1.5 text-center">{{ index + 1 }}</td>

                            <td>
                                <Link
                                    :href="route('products.show', item.product.id)"
                                    class="flex flex-row gap-x-3 items-center p-3 product-card"
                                >
                                    <span class="flex-grow-0 flex-shrink-0 product-image-placeholder rounded-lg size-20">

                                    </span>

                                    <span class="text-blue-600">{{ item.product.name }}</span>
                                </Link>
                            </td>

                            <td class="text-center">{{ item.product.stock_quantity }}</td>

                            <td class="text-center">${{ item.product.price }}</td>

                            <td class="text-center">${{ item.quantity * item.product.price }}</td>

                            <td class="text-center">
                                {{ item.quantity }}
                            </td>

                            <!-- Actions -->
                            <td>
                                <div class="flex flex-col gap-y-3 items-center">
                                    <button
                                        @click="editCartItem(item)"
                                        type="button"
                                        class="bg-green-100 border border-green-600 px-3 py-1.5 rounded-lg text-green-600 text-sm"
                                    >Edit</button>

                                    <button
                                        @click="removeFromCart(item)"
                                        type="button"
                                        class="bg-red-100 border border-red-600 px-3 py-1.5 rounded-lg text-red-600 text-sm"
                                    >Delete</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
