import Vue from 'vue';
import Vuex from 'vuex';
import Request from '@/mixins/Request';

Vue.use(Vuex);

const state = () => ({
    books: [],
    totalRecords: 0,
    currentPage: 1,
    itemsPerPage: 10,
});

const mutations = {
    SET_BOOKS (state, books) {
        state.books = books;
    },
    SET_TOTAL_RECORDS (state, total) {
        state.totalRecords = total;
    },
    SET_CURRENT_PAGE (state, page) {
        state.currentPage = page;
    },
    SET_ITEMS_PER_PAGE (state, count) {
        state.itemsPerPage = count;
    },
};

const actions = {
    getBooks: async ({ commit, state }, { page, perPage }) => {
        const { response } = await Request.methods.request(
            'GET',
            '/books', 
            { params: { page, perPage } }
        );

        const books = response.data.data.map(book => ({
            ...book,
            photo: {
                ...book.photo,
                path: `storage/${book.photo.path}`
            }
        }));
        const totalRows = response.data.meta.total;

        commit('SET_BOOKS', books);
        commit('SET_TOTAL_RECORDS', totalRows);
        commit('SET_CURRENT_PAGE', page);
        commit('SET_ITEMS_PER_PAGE', perPage);
    },
    setCurrentPage: ({ commit }, page) => {
        commit('SET_CURRENT_PAGE', page);
    },
    setItemsPerPage: ({ commit }, count) => {
        commit('SET_ITEMS_PER_PAGE', count);
    },
};

const getters = {
    getBooks: state => state.books,
    getTotalRecords: state => state.totalRecords,
    getCurrentPage: state => state.currentPage,
    getItemsPerPage: state => state.itemsPerPage,
};

export default new Vuex.Store({
    state,
    getters,
    actions,
    mutations,
});
