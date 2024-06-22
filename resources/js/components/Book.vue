<template>
    <b-card
        :title="book.title"
        :img-src="book.photos[0].path"
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
            <b-button
                variant="primary"
                size="sm"
                class="d-block"
                v-b-modal="`edit-book-${book.id}-modal`"
            >
                Edit
            </b-button>

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

        <modal
            :id="`edit-book-${book.id}-modal`"
            :title="book.title"
            :ok-disabled="okButtonDisabled"
            :cancel-disabled="cancelButtonDisabled"
            :hide-header-close="closeButtonHidden"
            @ok-pressed="$refs.form.submit()"
        >
            <add-book-form 
                ref="form"
                :id="book.id"
                :can-submit="canSubmit"
                @form-updated="updateFormValidity"
                @item-added="itemAdded(book)"
                @upload-in-progress="progress => setModalButtonsAvailability(progress)"
                @all-uploads-cancelled="setModalButtonsAvailability(0)"
            />
        </modal>
    </b-card>
</template>

<script>
    import Request from '@/mixins/Request';
    import { mapGetters } from 'vuex';

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
                canSubmit: false,
                cancelButtonDisabled: false,
                okButtonDisabled: true,
                closeButtonHidden: false,
            };
        },
        computed: {
            ...mapGetters({
                currentPage: 'getCurrentPage',
                itemsPerPage: 'getItemsPerPage',
            }),
        },
        methods: {
            updateFormValidity() {
                if (this.$refs.form) {
                    const formItem = this.$refs.form.item;
                    
                    this.canSubmit = Object.entries(formItem)
                        .filter(([key, ]) => !['photos', 'in_stock'].includes(key))
                        .every(([, value]) => value);

                    this.okButtonDisabled = !this.canSubmit;
                } else {
                    this.canSubmit = false;
                }
            },
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
            async itemAdded(book) {
                this.$bvModal.hide(`edit-book-${book.id}-modal`);

                this.$bvToast.toast('Book saved', {
                    title: 'Action successfully completed',
                    variant: 'success',
                });

                await this.$store.dispatch('getBooks', {
                    page: this.currentPage,
                    perPage: this.itemsPerPage,
                });
            },
            setModalButtonsAvailability(progress) {
                if (progress === 100 || progress === 0) {
                    this.cancelButtonDisabled = false;
                    this.okButtonDisabled = false;
                    this.closeButtonHidden = false;

                    return;
                }

                this.cancelButtonDisabled = true;
                this.okButtonDisabled = true;
                this.closeButtonHidden = true;
            },
        },
    };
</script>

<style scoped>
   
</style>
