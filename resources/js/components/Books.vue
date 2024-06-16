<template>
    <b-overlay
        :show="loading"
        rounded="sm"
        spinner-variant="primary"
    >
        <b-container class="mb-5">
            <b-row>
                <b-col
                    v-for="book in items"
                    :key="book.id"
                    lg="4"
                >
                    <Book
                        :book="book"
                        :types="types"
                        :categories="categories"
                        @item-updated="getBooks"
                        @item-deleted="getBooks"
                    />
                </b-col>
            </b-row>
        </b-container>

        <div v-if="!loading && !items.length" class="d-flex justify-content-center mt-5">
            <h5 class="text-danger font-weight-bold">
                No books found...
            </h5>
        </div>

        <div class="d-flex justify-content-center mt-3">
            <b-pagination
                v-if="items.length"
                v-model="currentPage"
                :per-page="itemsPerPage"
                :total-rows="totalRows"
                first-number
                last-number
                align="right"
                @input="pageNo => pageChanged(pageNo)"
            />
        </div>
    </b-overlay>
</template>

<script>
    import { mapGetters } from 'vuex';
    import Request from '@/mixins/Request';

    export default {
        mixins: [
            Request,
        ],
        props: {
            itemsPerPage: {
                type: Number,
                default: 10,
            },
        },
        data() {
            return {
                loading: false,
                currentPage: 1,
                types: [],
                categories: [],
            };
        },
        computed: {
            ...mapGetters({
                items: 'getBooks',
                totalRows: 'getTotalRecords',
            }),
        },
        async mounted() {
            this.getBooks();
            await this.getTypes();
            await this.getCategories();
        },
        methods: {
            async getBooks() {
                this.loading = true;

                await this.$store.dispatch('getBooks', {
                    page: this.currentPage,
                    perPage: this.itemsPerPage,
                });
                
                this.loading = false;
            },
            async pageChanged(pageNo) {
                this.currentPage = pageNo;

                this.getBooks();
            },
            async getTypes() {
                this.loading = true;

                const { response } = await this.request('GET', 'http://127.0.0.1:8000/types');

                this.loading = false;

                this.types = response.data;
            },
            async getCategories() {
                this.loading = true;

                const { response } = await this.request('GET', 'http://127.0.0.1:8000/categories');

                this.loading = false;

                this.categories = response.data;
            },
        },
    };
</script>

<style scoped>

</style>