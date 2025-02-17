<template>
    <LoadingView
        :loading="loadingInitial"
        class="space-y-3"
    >
        <Head :title="__('Yearly Calculations')"/>

        <Heading class="mb-6">{{ __('Yearly Calculations') }}</Heading>

        <Card class="relative py-4 px-6 md:col-span-4 card-simulator">
            <h1>{{ __('Simulate without income') }}</h1>

            <div class="flex px-0">
                <div class="px-2 py-2 w-48">
                    <label>{{ __('Period Filter') }}</label>
                    <VueDatePicker
                        v-model="simulateFrom"
                        month-picker
                        :start-date="startDateSimulate"
                        :min-date="startDateSimulate"
                        :max-date="maxDate"
                        auto-apply
                        prevent-min-max-navigation
                        :clearable="true"
                        timezone="Europe/Warsaw"
                        @update:model-value="fetchData"
                    />
                </div>

                <div class="px-2 py-2 flex-1">
                    <label>{{ __('Income Filter') }}</label>
                    <multiselect
                        v-model="incomesSimulate"
                        :options="incomesSimulateOptions"
                        label="label"
                        track-by="label"
                        :multiple="true"
                        :close-on-select="false"
                        :clear-on-select="false"
                        @update:modelValue="fetchData"></multiselect>
                </div>
            </div>
        </Card>

        <div class="filter w-48 px-0">
            <h3 class="mt-6">{{ __('Yearly Period Filter') }}</h3>

            <div class="py-2">
                <VueDatePicker
                    v-model="date"
                    year-picker
                    :start-date="startDate"
                    :year-range="[
                        new Date(minDate).getFullYear(),
                        new Date(maxDate).getFullYear()
                    ]"
                    auto-apply
                    prevent-min-max-navigation
                    :clearable="false"
                    timezone="Europe/Warsaw"
                    @update:model-value="fetchData"
                />
            </div>
        </div>

        <LoadingView
            :loading="loadingData"
        >
            <h2 v-if="incomes.length" class="text-lg mt-6 mb-3">{{ __('Incomes') }}</h2>

            <div class="grid md:grid-cols-12 gap-6" v-if="incomes.length">
                <Card class="relative py-4 px-6 md:col-span-4" v-for="income in incomes">
                    <div class="h-6 mb-4">
                        <h3 class="leading-tight text-sm font-bold text-center">{{ income.name }}</h3>
                    </div>

                    <div class="overflow-hidden overflow-x-auto relative">
                        <table class="w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                            <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                                {{ __('Month') }}
                            </th>
                            <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                                {{ __('Income') }}
                            </th>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            <tr class="divide-x divide-gray-100" v-for="(incomeData, i) in income.data">
                                <td class="py-2 px-4 td-fit" :class="i % 2 ? 'bg-color-second-row' : ''">
                                <span class="name-svg-line">
                                    {{ incomeData.name }}
                                    <component
                                        v-if="incomeData.is_completed"
                                        is="heroicons-outline-check-circle"
                                        height="12"
                                        width="12"
                                        class="text-green-600"
                                    />
                                </span>
                                </td>

                                <td class="py-2 px-4 td-fit text-right" :class="i % 2 ? 'bg-color-second-row' : ''">{{ numberFormat(incomeData.incomes) }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ __('Total') }}:</td>
                                <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ numberFormat(sumArray(income.data, 'incomes')) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>

            <h2 v-if="expenses.length && expensesTypes && Object.keys(expensesTypes).length" class="text-lg mt-6 mb-3">{{ __('Yearly Calculations') }}</h2>

            <Card class="relative" v-if="expenses.length && expensesTypes && Object.keys(expensesTypes).length">
                <div class="overflow-hidden overflow-x-auto relative">
                    <table class="w-full divide-y divide-gray-100 rounded-lg">
                        <thead class="bg-gray-50">
                        <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                            {{ __('Month') }}
                        </th>
                        <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                            {{ __('Income') }}
                        </th>
                        <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                            {{ __('Expenses') }}
                        </th>
                        <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                            {{ __('Balance') }}
                        </th>
                        <th class="uppercase text-gray-500 text-xxs tracking-wide py-2" v-for="expenseType in expensesTypes">
                            {{ expenseType.name }}
                        </th>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        <tr class="divide-x divide-gray-100 group" v-for="(expense, i) in expenses" v-on:click.prevent="goToPage(expense.filter)">
                            <td class="py-2 px-4 td-fit cursor-pointer group-hover:bg-gray-50" :class="i % 2 ? 'bg-color-second-row' : ''">
                                <span class="name-svg-line">
                                    {{ expense.name }}
                                    <component
                                        v-if="expense.is_completed"
                                        is="heroicons-outline-check-circle"
                                        height="12"
                                        width="12"
                                        class="text-green-600"
                                    />
                                </span>
                            </td>
                            <td class="py-2 px-4 td-fit cursor-pointer group-hover:bg-gray-50 text-right" :class="i % 2 ? 'bg-color-second-row' : ''">{{ numberFormat(expense.incomes) }}</td>
                            <td class="py-2 px-4 td-fit cursor-pointer group-hover:bg-gray-50 text-right" :class="i % 2 ? 'bg-color-second-row' : ''">{{ numberFormat(expense.expenses) }}</td>
                            <td class="py-2 px-4 td-fit cursor-pointer group-hover:bg-gray-50 text-right" :class="{'bg-color-second-row' : i % 2, 'text-red-600' : expense.balance < 0, 'text-green-600' : expense.balance > 0}">{{ numberFormat(expense.balance) }}</td>
                            <td class="py-2 px-4 td-fit cursor-pointer group-hover:bg-gray-50 text-right bg-color-red-pink" v-for="expenseType in expensesTypes">{{ numberFormat(expense.expenses_by_type[expenseType.id]) }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ __('Total') }}:</td>
                            <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ numberFormat(sumArray(expenses, 'incomes')) }}</td>
                            <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ numberFormat(sumArray(expenses, 'expenses')) }}</td>
                            <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom" :class="{'text-red-600' : sumArray(expenses, 'balance') < 0, 'text-green-600' : sumArray(expenses, 'balance') > 0}">{{ numberFormat(sumArray(expenses, 'balance')) }}</td>
                            <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom" v-for="expenseType in expensesTypes">{{ numberFormat(sumArray(expenses, 'expenses_by_type', expenseType.id)) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="bg-color-bottom">&nbsp;</td>
                            <td class="py-2 px-4 td-fit font-bold bg-color-bottom text-center" :colspan="Object.keys(expensesTypes).length">{{ numberFormat(multipleSumArray(expensesTypes, expenses, 'expenses_by_type')) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </LoadingView>
    </LoadingView>
</template>

<script>
import minimum from '../util/minimum';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import Multiselect from 'vue-multiselect'

export default {
    components: {VueDatePicker, Multiselect},

    data: () => ({
        loadingInitial: true,
        loadingData: true,

        date: null,
        simulateFrom: null,
        minDate: null,
        maxDate: null,
        startDate: null,
        startDateSimulate: null,
        locale: null,
        currency: null,

        incomesSimulate: null,
        incomesSimulateOptions: [],

        incomes: [],
        expenses: [],
        expensesTypes: [],
    }),

    created() {
        this.fetchInitial();
    },

    methods: {
        async fetchInitial() {
            this.loadingInitial = true;
            this.loadingData = true;

            try {
                const {
                    data: {min_date, max_date, start_date, start_date_month, start_date_simulate, locale, currency, incomes_simulate},
                } = await minimum(
                    Nova.request().get(this.toolEndpoint('config')),
                    200
                )

                this.loadingInitial = false;
                this.minDate = min_date;
                this.maxDate = max_date;
                this.startDate = start_date;
                this.startDateSimulate = start_date_simulate;
                this.locale = locale;
                this.currency = currency;
                this.incomesSimulateOptions = incomes_simulate;

                if (!this.date)
                    this.date = start_date;

                if (!this.simulateFrom)
                    this.simulateFrom = {
                        year: start_date,
                        month: start_date_month,
                    };
            } catch (error) {
                if (error.response && error.response.status === 401)
                    return Nova.redirectToLogin();

                Nova.visit('/404');
            }

            this.fetchData();
        },

        async fetchData() {
            this.loadingData = true;

            let simulateDate = this.simulateFrom ? this.simulateFrom : null;
            let simulateIncomes = this.incomesSimulate ? this.incomesSimulate.map(income => income.value) : null;

            try {
                const {
                    data: {incomes, expenses, expensesTypes},
                } = await minimum(
                    Nova.request().get(this.toolEndpoint('data'), {
                        params: {
                            year: this.date ? this.date : new Date().getFullYear(),
                            simulate_date: simulateDate,
                            simulate_incomes: simulateIncomes,
                        }
                    }),
                    200
                )

                this.loadingData = false;
                this.incomes = incomes;
                this.expenses = expenses;
                this.expensesTypes = expensesTypes;
            } catch (error) {
                if (error.response && error.response.status === 401)
                    return Nova.redirectToLogin();

                Nova.visit('/404');
            }
        },

        toolEndpoint($path) {
            return '/nova-vendor/yearly-calculations/' + $path
        },

        numberFormat(value) {
            return new Intl.NumberFormat(this.locale, {style: 'currency', currency: this.currency}).format(value ? value : 0);
        },

        multipleSumArray(array, arrayToSum, key) {
            let sum = 0;

            for (const [id, value] of Object.entries(array)) {
                sum += this.sumArray(arrayToSum, key, id);
            }

            return sum;
        },

        sumArray(array, key1 = null, key2 = null) {
            return array.reduce((actualValue, item) => actualValue + parseFloat(key1 && key2 ? item[key1][key2] : (key1 ? item[key1] : item)), 0);
        },

        goToPage(filter) {
            Nova.visit('/resources/expenses?expenses_filter=' + filter);
        }
    },
}
</script>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>

<style>
.bg-color-red-pink {
    background-color: #faf3f2;
}

.bg-color-second-row {
    background-color: #fbfbfb;
}

.bg-color-bottom {
    background-color: #f6f6f6;
}

.name-svg-line {
    display: flex;
    align-items: center;
}

.name-svg-line svg {
    margin-left: 5px;
}

.multiselect {
    min-height: 38px;
    width: 500px;
    max-width: 60%;
}

.multiselect__tags {
    min-height: 38px;
    padding-top: 7px;
    border-color: rgba(var(--colors-gray-300));
}

.multiselect__placeholder {
    margin-bottom: 5px;
    padding-top: 0;
}

.multiselect__tag {
    margin-bottom: 1px;
}

.card-simulator {
    overflow: visible !important;
}
</style>
