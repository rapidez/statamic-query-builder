import ProductQueryBuilder from './components/fieldtypes/ProductQueryBuilder.vue';
import DefaultPreset from './Pages/DefaultPreset.vue';

Statamic.booting(() => {
    Statamic.$components.register('product_query_builder-fieldtype', ProductQueryBuilder);

    Statamic.$inertia.register('statamic-query-builder::DefaultPreset', DefaultPreset);
});
