import ProductQueryBuilder from './components/fieldtypes/ProductQueryBuilder.vue';

Statamic.booting(() => {
    Statamic.component('product_query_builder-fieldtype', ProductQueryBuilder);
});
