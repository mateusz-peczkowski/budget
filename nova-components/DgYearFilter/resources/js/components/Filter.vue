<template>
    <div class="filter px-6">
        <div class="py-4">
            <h4>{{ filter.name }}</h4>

            <div class="py-2">
                <VueDatePicker v-model="date" timezone="Europe/Warsaw" year-picker :min-date="minDate" :max-date="maxDate" :start-date="startDate" :year-range="yearRange" auto-apply prevent-min-max-navigation @update:model-value="update" :clearable="false"></VueDatePicker>
            </div>
        </div>
    </div>
</template>

<script>
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

export default {
    components: {VueDatePicker},

    props: {
        resourceName: {
            type: String,
            required: true,
        },

        filterKey: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            date: null,
            minDate: null,
            maxDate: null,
            startDate: null,
            yearRange: null,
        };
    },

    created() {
        this.setCurrentFilterValue();

        this.minDate = this.filter.options.filter(filter => {
            return filter.label === 'min_date';
        })
            .map(item => item.value)[0];

        this.maxDate = this.filter.options.filter(filter => {
            return filter.label === 'max_date';
        })
            .map(item => item.value)[0];

        this.startDate = this.filter.options.filter(filter => {
            return filter.label === 'start_date';
        })
            .map(item => item.value)[0];

        this.yearRange = this.filter.options.filter(filter => {
            return filter.label === 'year_range';
        })
            .map(item => [item[0], item[1]])[0];

        if (!this.date) {
            this.date = this.filter.options.filter(filter => {
                return filter.label === 'date';
            })
                .map(item => item.value)[0];

            this.update();
        }
    },

    mounted() {
        Nova.$on('filter-reset', this.setCurrentFilterValue)
    },

    beforeUnmount() {
        Nova.$off('filter-reset', this.setCurrentFilterValue)
    },

    methods: {
        setCurrentFilterValue() {
            this.date = this.filter.currentValue;
        },

        update() {
            this.$store.commit(`${this.resourceName}/updateFilterState`, {
                filterClass: this.filterKey,
                value: this.date,
            })

            this.$emit('change');
        },
    },

    computed: {
        filter() {
            return this.$store.getters[`${this.resourceName}/getFilter`](
                this.filterKey
            )
        },
    },
};
</script>
