<template>
    <LoadingView
        :loading="loadingInitial"
        class="space-y-3"
    >
        <Head :title="__('Closest Expenses and Incomes')"/>

        <Heading class="mb-6">{{ __('Closest Expenses and Incomes') }}</Heading>

        <LoadingView
            :loading="loadingData"
        >
            <div class="grid md:grid-cols-12 gap-6">
                <Card class="relative py-4 px-6 md:col-span-6" v-if="expenses && expenses.length">
                    <div class="h-6 mb-4">
                        <h3 class="leading-tight text-sm font-bold text-center">{{ __('Expenses') }}</h3>
                    </div>

                    <div class="overflow-hidden overflow-x-auto relative">
                        <table class="w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                            <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                                {{ __('Name') }}
                            </th>
                            <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                                {{ __('Date') }}
                            </th>
                            <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                                {{ __('Value') }}
                            </th>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            <tr class="divide-x divide-gray-100" v-for="(expense, i) in expenses">
                                <td class="py-2 px-4 td-fit" :class="{'bg-color-second-row' : i % 2}">
                                    <div class="icon-text-title">
                                        <div :class="expense.icon_class" class="cursor-pointer" v-on:click.prevent="changeStatusToPaidConfirmation(expense.resource_id, 'expense')">
                                            <component
                                                :is="`heroicons-outline-${expense.icon}`"
                                                height="24"
                                                width="24"
                                            />
                                        </div>
                                        <div class="leading-normal">
                                            <div>
                                                <span class="whitespace-nowrap font-semibold">{{ expense.name }}</span>
                                            </div>
                                            <div v-if="expense.sub_name">
                                                <span class="whitespace-nowrap text-xs italic text-80">{{ expense.sub_name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2 px-4 td-fit text-center" :class="{'bg-color-second-row' : i % 2}">{{ expense.date_formated }}</td>
                                <td class="py-2 px-4 td-fit text-right" :class="{'bg-color-second-row' : i % 2}">{{ numberFormat(expense.value) }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom" colspan="2">{{ __('Total') }}:</td>
                                <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ numberFormat(sumArray(expenses, 'value')) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <Card class="relative py-4 px-6 md:col-span-6" v-if="incomes && incomes.length">
                    <div class="h-6 mb-4">
                        <h3 class="leading-tight text-sm font-bold text-center">{{ __('Incomes') }}</h3>
                    </div>

                    <div class="overflow-hidden overflow-x-auto relative">
                        <table class="w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                            <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                                {{ __('Name') }}
                            </th>
                            <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                                {{ __('Date') }}
                            </th>
                            <th class="uppercase text-gray-500 text-xxs tracking-wide py-2">
                                {{ __('Value') }}
                            </th>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                            <tr class="divide-x divide-gray-100" v-for="(income, i) in incomes">
                                <td class="py-2 px-4 td-fit" :class="{'bg-color-second-row' : i % 2}">
                                    <div class="icon-text-title">
                                        <div :class="income.icon_class" class="cursor-pointer" v-on:click.prevent="changeStatusToPaidConfirmation(income.resource_id, 'income')">
                                            <component
                                                :is="`heroicons-outline-${income.icon}`"
                                                height="24"
                                                width="24"
                                            />
                                        </div>
                                        <div class="leading-normal">
                                            <div>
                                                <span class="whitespace-nowrap font-semibold">{{ income.name }}</span>
                                            </div>
                                            <div v-if="income.sub_name">
                                                <span class="whitespace-nowrap text-xs italic text-80">{{ income.sub_name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2 px-4 td-fit text-center" :class="{'bg-color-second-row' : i % 2}">{{ income.date_formated }}</td>
                                <td class="py-2 px-4 td-fit text-right" :class="{'bg-color-second-row' : i % 2}">{{ numberFormat(income.gross) }}</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom" colspan="2">{{ __('Total') }}:</td>
                                <td class="py-2 px-4 td-fit text-right font-bold bg-color-bottom">{{ numberFormat(sumArray(incomes, 'gross')) }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>

            <Modal
                :show="showChangeStatusModal"
                @close-via-escape="showChangeStatusModal = false"
                data-testid="confirm-action-modal"
                tabindex="-1"
                role="dialog"
                size="2xl"
            >
                <form
                    ref="theForm"
                    autocomplete="off"
                    @submit.prevent.stop="changeStatusToPaid"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden space-y-6"
                >
                    <ModalHeader v-text="__('Change status to paid')"/>

                    <!-- Confirmation Text -->
                    <p class="px-8">{{ __('Are you sure you want to run this action?') }}</p>

                    <ModalFooter>
                        <div class="flex items-center ml-auto">
                            <CancelButton
                                component="button"
                                type="button"
                                dusk="cancel-action-button"
                                class="ml-auto mr-3"
                                @click="showChangeStatusModal = false"
                            >
                                {{ __('Cancel') }}
                            </CancelButton>

                            <LoadingButton
                                type="submit"
                                ref="runButton"
                                dusk="confirm-action-button"
                                :disabled="changeStatusModalLoading"
                                :loading="changeStatusModalLoading"
                                component="DefaultButton"
                            >
                                {{ __('Run Action') }}
                            </LoadingButton>
                        </div>
                    </ModalFooter>
                </form>
            </Modal>
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

        locale: null,
        currency: null,

        showChangeStatusModal: false,
        changeStatusModalLoading: false,
        changeStatusModalId: null,
        changeStatusModalType: null,

        incomes: [],
        expenses: [],
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
                    data: {locale, currency},
                } = await minimum(
                    Nova.request().get(this.toolEndpoint('config')),
                    200
                )

                this.loadingInitial = false;
                this.locale = locale;
                this.currency = currency;
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
                    data: {incomes, expenses},
                } = await minimum(
                    Nova.request().get(this.toolEndpoint('data')),
                    200
                )

                this.loadingData = false;
                this.incomes = incomes;
                this.expenses = expenses;
            } catch (error) {
                if (error.response && error.response.status === 401)
                    return Nova.redirectToLogin();

                Nova.visit('/404');
            }
        },

        toolEndpoint($path) {
            return '/nova-vendor/closest-expenses-and-incomes/' + $path
        },

        numberFormat(value) {
            return new Intl.NumberFormat(this.locale, {style: 'currency', currency: this.currency}).format(value ? value : 0);
        },

        sumArray(array, key1 = null, key2 = null) {
            return array.reduce((actualValue, item) => actualValue + parseFloat(key1 && key2 ? item[key1][key2] : (key1 ? item[key1] : item)), 0);
        },

        changeStatusToPaidConfirmation(id, type) {
            this.changeStatusModalId = id;
            this.changeStatusModalType = type;
            this.showChangeStatusModal = true;
        },

        async changeStatusToPaid() {
            this.changeStatusModalLoading = true;

            try {
                await minimum(
                    Nova.request().post(this.toolEndpoint('change-status-to-paid'), {
                        id: this.changeStatusModalId,
                        type: this.changeStatusModalType,
                    }),
                    200
                )

                this.changeStatusModalLoading = false;
                this.showChangeStatusModal = false;
                this.changeStatusModalId = null;
                this.changeStatusModalType = null;

                this.fetchData();
            } catch (error) {
                if (error.response && error.response.status === 401)
                    return Nova.redirectToLogin();

                Nova.visit('/404');
            }
        },
    },
}
</script>

<style>
.bg-color-second-row {
    background-color: #fbfbfb;
}

.bg-color-bottom {
    background-color: #f6f6f6;
}

.icon-text-title {
    display: flex;
    align-items: center;
}

.icon-text-title > div:first-child {
    margin-right: 10px;
}
</style>
