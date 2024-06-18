<template>
     <b-overlay
        :show="loading"
        rounded="sm"
        spinner-variant="primary"
    >
        <b-form @reset="resetForm">
            <b-row>
                <b-col md="6">
                    <b-form-group
                        v-if="!hideFields.includes('title')"
                        class="my-2"
                        label="Title *"
                        :invalid-feedback="errors.title"
                    >
                        <b-form-input
                            v-model.trim="item.title"
                            placeholder="Enter title"
                            :state="errors.title ? false : null"
                        />
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col md="6">
                    <b-form-group
                        v-if="!hideFields.includes('type')"
                        class="my-2"
                        label="Type *"
                        :invalid-feedback="errors.type"
                    >
                        <b-form-select
                            v-model="item.type"
                            :options="typeOptions"
                            :state="errors.type ? false : null"
                        />
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col md="6">
                    <b-form-group
                        v-if="!hideFields.includes('category')"
                        class="my-2"
                        label="Category *"
                        :invalid-feedback="errors.category"
                    >
                        <b-form-select
                            v-model="item.category"
                            :options="categoryOptions"
                            :state="errors.category ? false : null"
                        />
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col md="6">
                    <b-form-group
                        v-if="!hideFields.includes('manufacturer')"
                        class="my-2"
                        label="Manufacturer *"
                        :invalid-feedback="errors.manufacturer"
                    >
                        <b-form-select
                            v-model="item.manufacturer"
                            :options="manufacturerOptions"
                            :state="errors.manufacturer ? false : null"
                        />
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col md="6">
                    <b-form-group
                        v-if="!hideFields.includes('summary')"
                        class="my-2"
                        label="Summary *"
                        :invalid-feedback="errors.summary"
                    >
                        <b-form-textarea
                            v-model="item.summary"
                            placeholder="Enter summary..."
                            rows="3"
                            max-rows="6"
                            :state="errors.summary ? false : null"
                        />
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col md="6">
                    <b-form-group
                        v-if="!hideFields.includes('price')"
                        class="my-2"
                        label="Price *"
                        :invalid-feedback="errors.price"
                    >
                        <b-input-group>
                            <template #prepend>
                                <i class="fas fa-euro-sign"></i>
                            </template>

                            <b-form-input
                                v-model.number="item.price"
                                type="number"
                                placeholder="Enter price"
                                :state="errors.price ? false : null"
                                @keypress="handleDecimal($event)"
                            />
                        </b-input-group>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <b-col md="6">
                    <b-form-group
                        v-if="!hideFields.includes('in_stock')"
                        class="my-2"
                        label="In stock *"
                        :invalid-feedback="errors.in_stock"
                    >
                        <b-form-checkbox 
                            v-model="item.in_stock"
                            :state="errors.in_stock ? false : null"
                            switch
                            size="lg"
                        />
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row v-if="!item.photos.length || hasPlaceholderImage()">
                <b-col md="6">
                    <b-form-group
                        v-if="!hideFields.includes('uppy')"
                        class="my-2"
                        label="Picture"
                    >
                        <uppy
                            url="/files"
                            :additional-post-data="{path: 'uploads/images', disk: 'public'}"
                            :meta-fields="uppyMetaFields"
                            :max-number-of-files="1"
                            @file-uploaded="file => item.photos.push(file)"
                            @file-removed="fileName => fileRemoved(fileName)"
                            @upload-in-progress="progress => $emit('upload-in-progress', progress)"
                            @all-uploads-cancelled="$emit('all-uploads-cancelled')"
                        />
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row v-else>
                <b-col md="6">
                    <b-img 
                        :src="id ? `storage/${item.photos[0].path}` : `storage/${item.photos[0].photoPath}`" 
                        fluid 
                        :alt="item.photos[0].name"
                        class="mt-2"
                    />

                    <b-button 
                        size="sm"
                        variant="danger"
                        class="mt-2"
                        @click="deletePhoto(item.photos[0].name)"
                    >
                        Remove
                    </b-button>
                </b-col>
            </b-row>

            <b-button type="reset" variant="danger" class="mt-3">Reset</b-button>
        </b-form>
    </b-overlay>
</template>

<script>
    import Request from '@/mixins/Request';

    export default {
        mixins: [
            Request,
        ],
        props: {
            hideFields: {
                type: Array,
                default: () => [],
            },
            canSubmit: {
                type: Boolean,
                default: false,
            },
            id: {
                type: Number,
                default: null,
            },
        },
        data() {
            return {
                loading: false,
                item: {
                    title: '',
                    type: null,
                    category: null,
                    manufacturer: null,
                    summary: '',
                    price: '',
                    in_stock: false,
                    photos: [],
                },
                errors: {},
                typeOptions: [],
                categoryOptions: [],
                manufacturerOptions: [],
                uppyMetaFields: [
                    { id: 'name', name: 'Name', placeholder: 'Enter name' },
                    { id: 'caption', name: 'Caption', placeholder: 'Enter caption' },
                ],  
            };
        },
        watch: {
            item: {
                handler() {
                    this.$emit('form-updated');
                },
                deep: true,
            },
        },
        mounted() {
            this.getTypes();
            this.getCategories();
            this.getManufacturers();

            if (this.id) {
                this.loadItem();
            }
        },
        methods: {
            async getTypes() {
                this.loading = true;

                const { response } = await this.request('GET', 'http://127.0.0.1:8000/types');

                this.loading = false;

                this.typeOptions = response.data.map(type => ({
                    value: type.id,
                    text: type.name,
                }));

                this.typeOptions.unshift({
                    value: null,
                    text: 'Select type',
                });
            },
            async getCategories() {
                this.loading = true;

                const { response } = await this.request('GET', 'http://127.0.0.1:8000/categories');

                this.loading = false;

                this.categoryOptions = response.data.map(category => ({
                    value: category.id,
                    text: category.name,
                }));

                this.categoryOptions.unshift({
                    value: null,
                    text: 'Select category',
                });
            },
            async getManufacturers() {
                this.loading = true;

                const { response } = await this.request('GET', 'http://127.0.0.1:8000/manufacturers');

                this.loading = false;

                this.manufacturerOptions = response.data.map(manufacturer => ({
                    value: manufacturer.id,
                    text: manufacturer.name,
                }));

                this.manufacturerOptions.unshift({
                    value: null,
                    text: 'Select manufacturer',
                });
            },
            resetForm() {
                Object.assign(
                    this.item,
                    {
                        title: '',
                        type: null,
                        category: null,
                        manufacturer: null,
                        summary: '',
                        price: '',
                        in_stock: false,
                        photos: [],
                    }
                );
            },
            handleDecimal(event) {
                const keysAllowed = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.'];
                const keyPressed = event.key;

                if (!keysAllowed.includes(keyPressed)) {
                    event.preventDefault();
                }
            },
            fileRemoved(fileName) {
                const item = this.item.photos.find(photo => photo.name === fileName);
                const idx = this.item.photos.indexOf(item);

                this.item.photos.splice(idx, 1);
            },
            async submit() {
                if (this.loading) {
                    return;
                }

                this.loading = true;

                const { response, errors } = await this.formRequest(
                    this.id ? 'PUT' : 'POST',
                    this.id ? `http://127.0.0.1:8000/books/${this.id}` : `http://127.0.0.1:8000/books`,
                    this.item
                );

                const hasErrors = Boolean(Object.keys(errors).length);

                if (hasErrors) {
                    this.errors = errors;
                    this.loading = false;

                    return;
                }
 
                this.errors = {};

                this.loading = false;

                this.item = response.data;

                this.$emit('item-added');
            },
            async loadItem() {
                this.loading = true;

                try {
                    const { response } = await this.request('GET', `/books/${this.id}`);

                    const book = response.data;

                    this.item = {
                        ...book,
                        category: book.category.id,
                        manufacturer: book.manufacturer.id,
                        in_stock: book.in_stock === 1,
                        photos: [book.photo],
                    };

                    delete this.item.photo;

                    this.loading = false;
                } catch (error) {
                    this.loading = false;

                    console.error(error);
                }
            },
            hasPlaceholderImage() {
                const item = this.item.photos[0];

                if (!item) {
                    return true;
                }

                return item.name === "placeholder.png";
            },
            async deletePhoto(name) {
                const params = {
                    path: 'uploads/images',
                    disk: 'public',
                };
                
                const paramString = jQuery.param(params);

                await this.request('DELETE', `/files/${encodeURIComponent(name)}?${paramString}`);

                this.$bvToast.toast('Photo deleted', {
                    title: 'Action successfully completed',
                    variant: 'success',
                });

                this.item.photos = [];
            },
        },
    };
</script>

<style scoped>

</style>