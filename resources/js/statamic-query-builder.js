import ProductQueryBuilder from './components/fieldtypes/ProductQueryBuilder.vue';

Statamic.booting(() => {
    Statamic.$components.register('product_query_builder-fieldtype', ProductQueryBuilder);
});
