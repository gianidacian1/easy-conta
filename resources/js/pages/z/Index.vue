<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { ref, watch, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { FilterMatchMode } from '@primevue/core/api';
import  IconField  from 'primevue/iconfield';
import  InputText  from 'primevue/inputtext';
import  InputIcon  from 'primevue/inputicon';
import Button from 'primevue/button'
import SplitButton from 'primevue/splitbutton'
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import InputNumber from 'primevue/inputnumber';
import Message from 'primevue/message';
import DatePicker from 'primevue/datepicker';
import { useToast } from 'primevue/usetoast';
import  FileUpload from 'primevue/fileupload';
import {Link} from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'

defineProps<{
  zDocuments: any,
}>()

const { t } = useI18n();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: t('zDocuments.title'),
        href: '/z-documents',
    },
];

const loading = ref(false);
const visible = ref(false);
const editVisible = ref(false);
const paymentVisible = ref(false);
const toast = useToast();
const editForm = ref({
    id: null,
    number: '',
    initial_balance: 0,
    sales: 0,
    final_balance: 0
});

const editFormErrors = ref({
    number: '',
    initial_balance: '',
    sales: '',
    final_balance: ''
});

const paymentForm = ref({
    number: '',
    payment: '',
    name: '',
    date: null
});

// Reactive validation - clear errors when user fixes fields
watch(() => editForm.value.number, (newValue) => {
    if (newValue && newValue.toString().trim() !== '') {
        editFormErrors.value.number = '';
    }
});

watch(() => editForm.value.initial_balance, (newValue) => {
    if (newValue !== null && newValue !== undefined && newValue !== '' && !isNaN(newValue)) {
        editFormErrors.value.initial_balance = '';
    }
});

watch(() => editForm.value.sales, (newValue) => {
    if (newValue !== null && newValue !== undefined && newValue !== '' && !isNaN(newValue)) {
        editFormErrors.value.sales = '';
    }
});

watch(() => editForm.value.final_balance, (newValue) => {
    if (newValue !== null && newValue !== undefined && newValue !== '' && !isNaN(newValue)) {
        editFormErrors.value.final_balance = '';
    }
});

const addDocument = () => {
    visible.value = true
}

const addPayment = () => {
    // Reset form and open payment dialog
    paymentForm.value = {
        number: '',
        name: '',
        payment: '',
        date: null
    };
    paymentVisible.value = true;
}

// Menu items for the split button
const menuItems = computed(() => [
    {
        label: t('zDocuments.adaugaIncasare'),
        icon: 'pi pi-plus',
        command: () => addDocument()
    },
    {
        label: t('zDocuments.adaugaPlata'),
        icon: 'pi pi-minus',
        command: () => addPayment()
    }
])

const fileupload = ref()

const upload = () => {
    console.log('upload')
    if (!fileupload.value.files.length) return;
    console.log('upload file exists', fileupload.value.files[0])
    const formData = new FormData();
    formData.append('document', fileupload.value.files[0])

    router.post('z-documents/upload', formData, {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: t('zDocuments.uploadSuccessful'),
                detail: t('zDocuments.documentUploaded'),
                life: 5000
            });
            visible.value = false;
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: t('zDocuments.uploadFailed'),
                detail: t('zDocuments.uploadError'),
                life: 5000
            });
        }
    });
};


const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    number: { value: null, matchMode: FilterMatchMode.CONTAINS },
    initial_balance: { value: null, matchMode: FilterMatchMode.EQUALS },
    sales: { value: null, matchMode: FilterMatchMode.EQUALS },
    final_balance: { value: null, matchMode: FilterMatchMode.EQUALS }
});

const clearValidationErrors = () => {
    editFormErrors.value = {
        number: '',
        initial_balance: '',
        sales: '',
        final_balance: ''
    };
};

const validateEditForm = () => {
    clearValidationErrors();
    let isValid = true;

    // Validate number
    if (!editForm.value.number || editForm.value.number.toString().trim() === '') {
        editFormErrors.value.number = t('zDocuments.contorNumberRequired');
        isValid = false;
    }

    // Validate initial_balance
    if (editForm.value.initial_balance === null || editForm.value.initial_balance === undefined || editForm.value.initial_balance === '') {
        editFormErrors.value.initial_balance = t('zDocuments.initialBalanceRequired');
        isValid = false;
    } else if (isNaN(editForm.value.initial_balance)) {
        editFormErrors.value.initial_balance = t('zDocuments.soldInitial') + ' ' + t('zDocuments.mustBeNumber');
        isValid = false;
    }

    // Validate sales
    if (editForm.value.sales === null || editForm.value.sales === undefined || editForm.value.sales === '') {
        editFormErrors.value.sales = t('zDocuments.salesRequired');
        isValid = false;
    } else if (isNaN(editForm.value.sales)) {
        editFormErrors.value.sales = t('zDocuments.vanzari') + ' ' + t('zDocuments.mustBeNumber');
        isValid = false;
    }

    // Validate final_balance
    if (editForm.value.final_balance === null || editForm.value.final_balance === undefined || editForm.value.final_balance === '') {
        editFormErrors.value.final_balance = t('zDocuments.finalBalanceRequired');
        isValid = false;
    } else if (isNaN(editForm.value.final_balance)) {
        editFormErrors.value.final_balance = t('zDocuments.soldFinal') + ' ' + t('zDocuments.mustBeNumber');
        isValid = false;
    }

    return isValid;
};

const editDocument = (document) => {
    editForm.value = {
        id: document.id,
        number: document.number,
        initial_balance: document.initial_balance,
        sales: document.sales,
        final_balance: document.final_balance
    };
    clearValidationErrors();
    editVisible.value = true;
};

const updateDocument = () => {
    // Validate form before submitting
    if (!validateEditForm()) {
        toast.add({
            severity: 'warn',
            summary: t('zDocuments.validationError'),
            detail: t('zDocuments.fixErrors'),
            life: 5000
        });
        return;
    }

    router.put(`/z-documents/${editForm.value.id}`, {
        number: editForm.value.number,
        initial_balance: editForm.value.initial_balance,
        sales: editForm.value.sales,
        final_balance: editForm.value.final_balance
    }, {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: t('zDocuments.updateSuccessful'),
                detail: t('zDocuments.documentUpdated'),
                life: 5000
            });
            editVisible.value = false;
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: t('zDocuments.updateFailed'),
                detail: t('zDocuments.updateError'),
                life: 5000
            });
        }
    });
};

const submitPayment = () => {
    router.post('/z-documents/adauga-plata', {
        number: paymentForm.value.number,
        payment: paymentForm.value.payment,
        name: paymentForm.value.name,
        date: paymentForm.value.date
    }, {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: t('zDocuments.paymentForm.title'),
                detail: 'Payment added successfully',
                life: 5000
            });
            paymentVisible.value = false;
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'There was an error adding the payment',
                life: 5000
            });
        }
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '';

    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const year = date.getFullYear();
    const hours = date.getHours().toString().padStart(2, '0');
    const minutes = date.getMinutes().toString().padStart(2, '0');

    return `${day}-${month}-${year} ${hours}:${minutes}`;
};
</script>

<template>
    <Head :title="t('zDocuments.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <SplitButton
                :label="t('zDocuments.adaugaIncasare')"
                :model="menuItems"
                @click="addDocument"
                class="ml-auto"
                size="small"
            />

            <DataTable
                v-model:filters="filters"
                :value="zDocuments"
                tableStyle="min-width: 50rem"
                stripedRows
                paginator
                :rows="5" :rowsPerPageOptions="[1, 5, 20]"
                :showGridlines="true"
                filterDisplay="row"
                :loading="loading"
                :globalFilterFields="['number', 'initial_balance', 'sales', 'final_balance']"
                >
                    <template #header>
                        <div class="flex justify-end">
                            <IconField>
                                <InputIcon>
                                    <i class="pi pi-search" />
                                </InputIcon>
                                <InputText v-model="filters['global'].value" :placeholder="t('zDocuments.searchZDocuments')" />
                            </IconField>
                        </div>
                    </template>
                    <template #empty>{{ t('zDocuments.noDocumentsFound') }}</template>
                    <template #loading>{{ t('zDocuments.loadingDocuments') }}</template>
                    <Column field="number" :header="t('zDocuments.contor')"></Column>
                    <Column field="activation_time" :header="t('zDocuments.dataInitializare')">
                        <template #body="slotProps">
                            {{ formatDate(slotProps.data.activation_time) }}
                        </template>
                    </Column>
                    <Column field="initial_balance" :header="t('zDocuments.soldInitial')"></Column>
                    <Column field="type" :header="t('zDocuments.type')">
                        <template #body="slotProps">
                            {{ slotProps.data.type.charAt(0).toUpperCase() + slotProps.data.type.slice(1) }}
                        </template>
                    </Column>
                    <Column field="sales" :header="t('zDocuments.vanzari')"></Column>
                    <Column field="final_balance" :header="t('zDocuments.soldFinal')"></Column>

                    <Column field="user_id" :header="t('common.actions')">
                        <template #body="slotProps">
                            <Button
                                :label="t('common.edit')"
                                severity="secondary"
                                size="small"
                                @click="editDocument(slotProps.data)"
                            />
                        </template>
                    </Column>
            </DataTable>
        </div>

        <Dialog
            v-model:visible="visible"
            modal
            :header="t('common.upload')"
            :style="{ width: '25rem' }"
        >
            <span class="text-surface-500 dark:text-surface-400 block mb-8">{{ t('balances.uploadDocument') }}</span>
            <div class="flex items-center gap-4 mb-4">
                <FileUpload
                    ref="fileupload"
                    name="document"
                    :showUploadButton="false"
                    :showCancelButton="false"
                    :auto="false"
                />

            </div>

            <div class="flex justify-end gap-2">
                <Button type="button" :label="t('common.cancel')" severity="secondary" @click="visible = false"></Button>
                <Button type="button" :label="t('common.save')" @click="upload"></Button>
            </div>
        </Dialog>

        <!-- Edit Z Document Dialog -->
        <Dialog
            v-model:visible="editVisible"
            modal
            :header="t('zDocuments.editZDocument')"
            :style="{ width: '32rem' }"
        >
            <div class="flex flex-col gap-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label for="number" class="text-sm font-medium text-gray-700">{{ t('zDocuments.contorNumber') }}</label>
                        <InputText
                            id="number"
                            v-model="editForm.number"
                            :placeholder="t('zDocuments.enterContorNumber')"
                            :class="{ 'p-invalid': editFormErrors.number }"
                        />
                        <Message v-if="editFormErrors.number" severity="error" :closable="false">
                            {{ editFormErrors.number }}
                        </Message>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="initial_balance" class="text-sm font-medium text-gray-700">{{ t('zDocuments.soldInitial') }}</label>
                        <InputNumber
                            id="initial_balance"
                            v-model="editForm.initial_balance"
                            :min-fraction-digits="2"
                            :max-fraction-digits="2"
                            placeholder="0.00"
                            :class="{ 'p-invalid': editFormErrors.initial_balance }"
                        />
                        <Message v-if="editFormErrors.initial_balance" severity="error" :closable="false">
                            {{ editFormErrors.initial_balance }}
                        </Message>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label for="sales" class="text-sm font-medium text-gray-700">{{ t('zDocuments.vanzari') }}</label>
                        <InputNumber
                            id="sales"
                            v-model="editForm.sales"
                            :min-fraction-digits="2"
                            :max-fraction-digits="2"
                            placeholder="0.00"
                            :class="{ 'p-invalid': editFormErrors.sales }"
                        />
                        <Message v-if="editFormErrors.sales" severity="error" :closable="false">
                            {{ editFormErrors.sales }}
                        </Message>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="final_balance" class="text-sm font-medium text-gray-700">{{ t('zDocuments.soldFinal') }}</label>
                        <InputNumber
                            id="final_balance"
                            v-model="editForm.final_balance"
                            :min-fraction-digits="2"
                            :max-fraction-digits="2"
                            placeholder="0.00"
                            :class="{ 'p-invalid': editFormErrors.final_balance }"
                        />
                        <Message v-if="editFormErrors.final_balance" severity="error" :closable="false">
                            {{ editFormErrors.final_balance }}
                        </Message>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button type="button" :label="t('common.cancel')" severity="secondary" @click="editVisible = false"></Button>
                    <Button type="button" :label="t('common.update')" @click="updateDocument"></Button>
                </div>
            </template>
        </Dialog>

        <!-- Payment Dialog -->
        <Dialog
            v-model:visible="paymentVisible"
            modal
            :header="t('zDocuments.paymentForm.title')"
            :style="{ width: '32rem' }"
        >
            <div class="flex flex-col gap-6">
                <div class="flex flex-col gap-2">
                    <label for="payment_number" class="text-sm font-medium text-gray-700">{{ t('zDocuments.paymentForm.number') }}</label>
                    <InputText
                        id="payment_number"
                        v-model="paymentForm.number"
                        :placeholder="t('zDocuments.paymentForm.numberPlaceholder')"
                    />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="payment" class="text-sm font-medium text-gray-700">{{ t('zDocuments.paymentForm.payment') }}</label>
                    <InputText
                        id="payment"
                        v-model="paymentForm.payment"
                        :placeholder="t('zDocuments.paymentForm.paymentPlaceholder')"
                    />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="payment_name" class="text-sm font-medium text-gray-700">{{ t('zDocuments.paymentForm.name') }}</label>
                    <InputText
                        id="payment_name"
                        v-model="paymentForm.name"
                        :placeholder="t('zDocuments.paymentForm.namePlaceholder')"
                    />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="payment_date" class="text-sm font-medium text-gray-700">{{ t('zDocuments.paymentForm.date') }}</label>
                    <DatePicker
                        id="payment_date"
                        v-model="paymentForm.date"
                        dateFormat="dd/mm/yy"
                        :showIcon="true"
                    />
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button type="button" :label="t('common.cancel')" severity="secondary" @click="paymentVisible = false"></Button>
                    <Button type="button" :label="t('zDocuments.paymentForm.submit')" @click="submitPayment"></Button>
                </div>
            </template>
        </Dialog>

        <Toast position="bottom-left" />
    </AppLayout>
</template>

<style scoped>
.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}
</style>