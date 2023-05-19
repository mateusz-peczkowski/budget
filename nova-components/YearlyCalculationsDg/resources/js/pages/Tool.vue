<template>
    <LoadingView
        :loading="loadingInitial"
        class="space-y-3"
    >
        <Head :title="__('Yearly Calculations DG')"/>

        <Heading class="mb-6">{{ __('Yearly Calculations DG') }}</Heading>

        <div class="filter w-48 px-0">
            <h3>{{ __('Yearly Period Filter') }}</h3>

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
            <div class="grid md:grid-cols-12 gap-6">
                <Card class="relative py-4 px-6 md:col-span-6" v-if="data && Object.keys(data).length" v-for="type in ['zus', 'tax', 'vat', 'total']">
                    <div class="h-6 mb-4">
                        <h3 class="leading-tight text-sm font-bold text-center">{{ __(getCardNameByType(type)) }}</h3>
                    </div>

                    <div class="overflow-hidden overflow-x-auto relative">
                        <table class="w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                            <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                                {{ __('Month') }}
                            </th>
                            <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                                {{ __('Planned') }}
                            </th>
                            <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                                {{ __('Paid') }}
                            </th>
                            <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                                {{ __('Balance') }}
                            </th>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            <tr class="divide-x divide-gray-100" v-for="(dataItem, i) in data">
                                <td class="py-2 px-4 td-fit" :class="i % 2 ? 'bg-color-second-row' : ''">{{ dataItem.name }}</td>
                                <td class="py-2 px-4 td-fit text-right" :class="i % 2 ? 'bg-color-second-row' : ''">{{ numberFormat(dataItem[type].planned) }}</td>
                                <td class="py-2 px-4 td-fit text-right" :class="i % 2 ? 'bg-color-second-row' : ''">{{ numberFormat(dataItem[type].paid) }}</td>
                                <td class="py-2 px-4 td-fit text-right" :class="{'bg-color-second-row' : i % 2, 'text-red-600' : dataItem[type].balance < 0, 'text-green-600' : dataItem[type].balance > 0}">{{ numberFormat(dataItem[type].balance) }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ __('Total') }}:</td>
                                <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ numberFormat(sumArray(data, type, 'planned')) }}</td>
                                <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ numberFormat(sumArray(data, type, 'paid')) }}</td>
                                <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom" :class="{'text-red-600' : sumArray(data, type, 'balance') < 0, 'text-green-600' : sumArray(data, type, 'balance') > 0}">{{ numberFormat(sumArray(data, type, 'balance')) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>

            <h2 v-if="data && Object.keys(data).length" class="text-lg mt-6 mb-3">{{ __('Yearly Calculations') }}</h2>

            <Card class="relative" v-if="data && Object.keys(data).length">
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
                            {{ __('ZUS') }}
                        </th>
                        <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                            {{ __('Tax') }}
                        </th>
                        <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                            {{ __('VAT') }}
                        </th>
                        <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                            {{ __('Balance') }}
                        </th>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                        <tr class="divide-x divide-gray-100 group" v-for="(dataItem, i) in data">
                            <td class="py-2 px-4 td-fit cursor-pointer group-hover:bg-gray-50" :class="i % 2 ? 'bg-color-second-row' : ''">{{ dataItem.name }}</td>
                            <td class="py-2 px-4 td-fit cursor-pointer group-hover:bg-gray-50 text-right" :class="i % 2 ? 'bg-color-second-row' : ''">{{ numberFormat(dataItem.gross_income) }}</td>
                            <td class="py-2 px-4 td-fit cursor-pointer group-hover:bg-gray-50 text-right bg-color-red-pink">{{ numberFormat(dataItem.zus.paid_planned) }}</td>
                            <td class="py-2 px-4 td-fit cursor-pointer group-hover:bg-gray-50 text-right bg-color-red-pink">{{ numberFormat(dataItem.tax.paid_planned) }}</td>
                            <td class="py-2 px-4 td-fit cursor-pointer group-hover:bg-gray-50 text-right bg-color-red-pink">{{ numberFormat(dataItem.vat.paid_planned) }}</td>
                            <td class="py-2 px-4 td-fit cursor-pointer group-hover:bg-gray-50 text-right" :class="{'bg-color-second-row' : i % 2, 'text-red-600' : (dataItem.net_income) < 0, 'text-green-600' : (dataItem.net_income) > 0}">{{ numberFormat((dataItem.net_income)) }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ __('Total') }}:</td>
                            <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ numberFormat(sumArray(data, 'gross_income')) }}</td>
                            <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ numberFormat(sumArray(data, 'zus', 'paid_planned')) }}</td>
                            <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ numberFormat(sumArray(data, 'tax', 'paid_planned')) }}</td>
                            <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ numberFormat(sumArray(data, 'vat', 'paid_planned')) }}</td>
                            <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom" :class="{'text-red-600' : sumArray(data, 'net_income') < 0, 'text-green-600' : sumArray(data, 'net_income') > 0}">{{ numberFormat(sumArray(data, 'net_income')) }}</td>
                        </tr>
                        <tr v-if="taxFreeMonth">
                            <td class="py-2 px-4 td-fit text-center bg-color-bottom" colspan="6">{{ __('Tax Free Month') }}: <strong>{{ taxFreeMonth }}</strong></td>
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

export default {
    components: {VueDatePicker},

    data: () => ({
        loadingInitial: true,
        loadingData: true,

        date: null,
        minDate: null,
        maxDate: null,
        startDate: null,
        locale: null,
        currency: null,

        data: [],
        taxFreeMonth: '',
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
                    data: {min_date, max_date, start_date, locale, currency},
                } = await minimum(
                    Nova.request().get(this.toolEndpoint('config')),
                    200
                )

                this.loadingInitial = false;
                this.minDate = min_date;
                this.maxDate = max_date;
                this.startDate = start_date;
                this.locale = locale;
                this.currency = currency;

                if (!this.date)
                    this.date = start_date;
            } catch (error) {
                if (error.response && error.response.status === 401)
                    return Nova.redirectToLogin();

                Nova.visit('/404');
            }

            this.fetchData();
        },

        async fetchData() {
            this.loadingData = true;

            try {
                const {
                    data: {data, taxFreeMonth},
                } = await minimum(
                    Nova.request().get(this.toolEndpoint('data'), {
                        params: {
                            year: this.date ? this.date : new Date().getFullYear(),
                        }
                    }),
                    200
                )

                this.loadingData = false;
                this.data = data;
                this.taxFreeMonth = taxFreeMonth;
            } catch (error) {
                if (error.response && error.response.status === 401)
                    return Nova.redirectToLogin();

                Nova.visit('/404');
            }
        },

        toolEndpoint($path) {
            return '/nova-vendor/yearly-calculations-dg/' + $path
        },

        numberFormat(value) {
            return new Intl.NumberFormat(this.locale, { style: 'currency', currency: this.currency }).format(value ? value : 0);
        },

        sumArray(array, key1 = null, key2 = null) {
            return array.reduce((actualValue, item) => actualValue + parseFloat(key1 && key2 ? item[key1][key2] : (key1 ? item[key1] : item)), 0);
        },

        getCardNameByType(type) {
            if (type === 'zus')
                return 'ZUS';
            else if (type === 'tax')
                return 'Tax';
            else if (type === 'vat')
                return 'VAT';
            else if (type === 'total')
                return 'ZUS + Tax + VAT';

            return type;
        },
    },
}
</script>

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
</style>
