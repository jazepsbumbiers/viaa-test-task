<template>
    <b-card
        :title="book.title"
        :img-src="book.photo.path"
        :img-alt="book.title"
        img-top
        :img-height="imgHeight"
        :img-width="imgWidth"
        :bg-variant="bgVariant"
        :tag="tag"
        class="mr-3 mt-5"
    >
        <b-card-text>
            <div>
                Type: {{ getType(book.type) }} <br/>
                Category: {{ getCategory(book.category.id) }} <br/>
                Price: {{ book.price }} eur<br/>
                In stock: {{ book.in_stock ? 'Yes' : 'No' }}
            </div>
        </b-card-text>

        <div class="actions-and-info-block">
            <!-- <b-button
                variant="primary"
                size="sm"
                class="d-block"
                v-b-modal="`show-recipe-${recipe.id}-modal`"
            >
                Show/edit recipe
            </b-button> -->

            <b-button
                variant="danger"
                size="sm"
                class="d-block mt-2"
                @click="deleteBook(book.id)"
            >
                Delete
            </b-button>

            <b-card-text class="mt-3">
                <small class="text-muted d-block">Date added: {{ book.created_at }}</small>
                <small class="text-muted d-block">Date updated: {{ dateUpdated(book) }}</small>
            </b-card-text>
        </div>

        <!-- <b-modal
            :id="`show-recipe-${recipe.id}-modal`"
            :title="recipe.name"
            centered
            @ok.prevent="$refs.editForm.submit()"
        >
            <template #modal-footer="{ ok, cancel }">
                <b-button size="md" variant="danger" :disabled="!resetAllowed" @click="$refs.editForm.resetForm(recipe.id)">
                    Clear
                </b-button>

                <b-button size="md" variant="secondary" class="ml-auto" @click="cancel()">
                    Cancel
                </b-button>

                <b-button size="md" variant="success" :disabled="!submitAllowed" @click="ok()">
                    {{ external ? 'Save to collection' : 'Update' }}
                </b-button>
            </template>

            <Form
                :id="recipe.id"
                ref="editForm"
                :data="external ? { ...recipe } : null"
                @submit-allowed="v => submitAllowed = v"
                @reset-allowed="v => resetAllowed = v"
                @item-updated="(item) => itemUpdated(item)"
            />
        </b-modal> -->
    </b-card>
</template>

<script>
    import Request from '@/mixins/Request';

    export default {
        mixins: [
            Request,
        ],
        props: {
            book: {
                type: Object,
                default: () => ({}),
            },
            types: {
                type: Array,
                default: () => [],
            },
            categories: {
                type: Array,
                default: () => [],
            },
            imgHeight: {
                type: Number,
                default: 200,
            },
            imgWidth: {
                type: Number,
                default: 400,
            },
            bgVariant: {
                type: String,
                default: 'light',
            },
            tag: {
                type: String,
                default: 'article',
            },
        },
        data() {
            return {
                submitAllowed: false,
                resetAllowed: false,
            };
        },
    
        methods: {
            getType(type) {
                return this.types.find(t => t.id === type)?.name ?? '-';
            },
            getCategory(category) {
                return this.categories.find(c => c.id === category)?.name ?? '-';
            },
            dateUpdated(book) {
                return book.updated_at ?? '-';
            },
            async deleteBook(id) {
                const response = await this.$bvModal.msgBoxConfirm(`Do you really want to delete this book? This cannot be undone!`, {
                    title: 'Please confirm',
                    size: 'sm',
                    buttonSize: 'sm',
                    okVariant: 'danger',
                    okTitle: 'Yes',
                    cancelTitle: 'No',
                    footerClass: 'p-2',
                    hideHeaderClose: false,
                    centered: true,
                });

                if (!response) {
                    return;
                }

                this.loading = true;

                await this.request('DELETE', `/books/${id}`);

                this.loading = false;

                this.$bvToast.toast('Book deleted successfully', {
                    title: 'Item deleted',
                    variant: 'success',
                    toaster: 'b-toaster-bottom-right',
                    toastClass: 'mb-2 mr-2',
                    solid: true,
                });

                this.$emit('item-deleted');
            },
        },
    };
</script>

<style scoped>
   
</style>
