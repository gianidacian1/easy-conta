<template>
    <DataTable
        v-model:filters="filters"
        :value="documents"
        tableStyle="min-width: 50rem"
        stripedRows 
        paginator
        :rows="5" :rowsPerPageOptions="[1, 5, 20]"
        :showGridlines="true"
        filterDisplay="row" 
        :loading="loading"
        :globalFilterFields="['denumirea_contului']"
        >
        <template #header>
            <div class="flex justify-end">
                <IconField>
                    <InputIcon>
                        <i class="pi pi-search" />
                    </InputIcon>
                    <InputText v-model="filters['global'].value" placeholder="Keyword Search" />
                </IconField>
            </div>
        </template>
        <template #empty> No documents found. </template>
        <template #loading> Loading documents data. Please wait. </template>
        <Column field="cont" header="Cont"></Column>
        <Column field="denumirea_contului" header="Denumirea contului"></Column>

        <Column field="solduri_initiale" header="Solduri initiale">
            <template #body="slotProps">
                <AccountDetailsTable
                    :debitoare="slotProps.data.solduri_initiale.debitoare"
                    :creditoare="slotProps.data.solduri_initiale.creditoare"
                />
            </template>
        </Column>
        <Column field="sume_precedente" header="Sume precedente">
            <template #body="slotProps">
                <AccountDetailsTable
                    :debitoare="slotProps.data.sume_precedente.debitoare"
                    :creditoare="slotProps.data.sume_precedente.creditoare"
                />
            </template>
        </Column>
        <Column field="rulaje_perioada" header="Rulaje perioada">
            <template #body="slotProps">
                <AccountDetailsTable
                    :debitoare="slotProps.data.rulaje_perioada.debitoare"
                    :creditoare="slotProps.data.rulaje_perioada.creditoare"
                />
            </template>
        </Column>
        <Column field="sume_totale" header="Sume totale">
            <template #body="slotProps">
                <AccountDetailsTable
                    :debitoare="slotProps.data.sume_totale.debitoare"
                    :creditoare="slotProps.data.sume_totale.creditoare"
                />
            </template>
        </Column>
        <Column field="solduri_finale" header="Solduri finale">
            <template #body="slotProps">
                <AccountDetailsTable
                    :debitoare="slotProps.data.solduri_finale.debitoare"
                    :creditoare="slotProps.data.solduri_finale.creditoare"
                />
            </template>
        </Column>
    </DataTable>
</template>

<script setup lang="ts">
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import  IconField  from 'primevue/iconfield';
import  InputText  from 'primevue/inputtext';
import  InputIcon  from 'primevue/inputicon';
import Button from 'primevue/button'
import AccountDetailsTable from '@/components/AccountDetailsTable.vue';

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    denumirea_contului: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    // 'country.name': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    // representative: { value: null, matchMode: FilterMatchMode.IN },
    // status: { value: null, matchMode: FilterMatchMode.EQUALS },
    // verified: { value: null, matchMode: FilterMatchMode.EQUALS }
});

defineProps<{
  documents: any,
  loading: boolean
}>()



</script>
