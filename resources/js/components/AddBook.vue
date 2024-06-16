<template>
    <div class="d-flex pa-4" :class="buttonPositionClass">
        <slot name="add-button"></slot>

        <modal
            :id="modalId"
            title="Add book"
            :ok-disabled="okButtonDisabled"
            :cancel-disabled="cancelButtonDisabled"
            :hide-header-close="closeButtonHidden"
            @cancel-pressed="handleModalClose"
            @close-pressed="handleModalClose"
            @ok-pressed="$refs.form.submit()"
        >
            <add-book-form 
                ref="form"
                :can-submit="canSubmit"
                @form-updated="updateFormValidity"
                @item-added="itemAdded()"
                @upload-in-progress="progress => setModalButtonsAvailability(progress)"
                @all-uploads-cancelled="setModalButtonsAvailability(0)"
            />
        </modal>
    </div>
</template>

<script>
    import Request from '@/mixins/Request';
    import { mapGetters } from 'vuex';

    export default {
        mixins: [
            Request,
        ],
        props: {
            buttonPositionClass: {
                type: String,
                default: 'justify-content-end',
            },
            modalId: {
                type: String,
                default: 'book-add-modal',
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
            async itemAdded() {
                this.$bvModal.hide(this.modalId);

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
            async handleModalClose() {
                try {
                    const anyFileUploaded = this.$refs.form.item.photos.length;

                    if (anyFileUploaded) {
                        const params = {
                            disk: 'public',
                            directory: 'uploads/images',
                        };
                        
                        const paramString = jQuery.param(params);
                        
                        await this.request('DELETE', `files/remove-uploaded-photos-without-directory?${paramString}`);
                    }
                } catch (error) {
                    throw new Error(error.toString());
                }
            },
        },
    };
</script>

<style>

</style>