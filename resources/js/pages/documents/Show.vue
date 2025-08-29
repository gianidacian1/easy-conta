<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import  IconField  from 'primevue/iconfield';
import  InputText  from 'primevue/inputtext';
import  InputIcon  from 'primevue/inputicon';
import Button from 'primevue/button'
import AccountDetailsTable from '@/components/AccountDetailsTable.vue';
import DocumentsIndex from '@/components/DocumentsIndex.vue';
import Dialog from 'primevue/dialog';
import axios  from 'axios'
import  FileUpload from 'primevue/fileupload';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Documents',
        href: '/documents',
    },
];

const documents = ref([
    {
        cont: 1011,
        denumirea_contului: 'Capital subscris nevarsat',
        solduri_initiale: {
            debitoare: '0.00',
            creditoare: '0.00'
        },
        sume_precedente: {
            debitoare: '0.00',
            creditoare: '0.00'
        },
        rulaje_perioada: {
            debitoare: '0.00',
            creditoare: '0.00'
        },
        sume_totale: {
            debitoare: '0.00',
            creditoare: '0.00'
        },
        solduri_finale: {
            debitoare: '0.00',
            creditoare: '0.00'
        }
    },
    {
        cont: 1012,
        denumirea_contului: 'Capital subscris varsat',
        solduri_initiale: {
            debitoare: '0.00',
            creditoare: '0.00'
        },
        sume_precedente: {
            debitoare: '0.00',
            creditoare: '0.00'
        },
        rulaje_perioada: {
            debitoare: '0.00',
            creditoare: '0.00'
        },
        sume_totale: {
            debitoare: '0.00',
            creditoare: '0.00'
        },
        solduri_finale: {
            debitoare: '0.00',
            creditoare: '0.00'
        }
    },
    {
        cont: 1171,
        denumirea_contului: 'Rezultatul Reportat - Profitul Nerep/Pierdere Neacop.',
        solduri_initiale: {
            debitoare: '22.628,48',
            creditoare: '0.00'
        },
        sume_precedente: {
            debitoare: '58,150.12',
            creditoare: '0.00'
        },
        rulaje_perioada: {
            debitoare: '0.00',
            creditoare: '0.00'
        },
        sume_totale: {
            debitoare: '58,150.12',
            creditoare: '0.00'
        },
        solduri_finale: {
            debitoare: '58,150.12',
            creditoare: '0.00'
        }
    },
    {
        cont: 121,
        denumirea_contului: 'Profit si pierdere',
        solduri_initiale: {
            debitoare: '35.531,64',
            creditoare: '0.00'
        },
        sume_precedente: {
            debitoare: '164.658,07',
            creditoare: '0.00'
        },
        rulaje_perioada: {
            debitoare: '28.000,15',
            creditoare: '19.397,91'
        },
        sume_totale: {
            debitoare: '190.658,22',
            creditoare: '167.323,47'
        },
        solduri_finale: {
            debitoare: '23.334,75',
            creditoare: '0.00'
        }
    }
])
const loading = ref(false);
const visible = ref(false)

const addDocument = () => {
    visible.value = true
}

const fileupload = ref()

const upload = () => {
    console.log('upload')
    if (!fileupload.value.files.length) return;
    console.log('upload file exists')
    const formData = new FormData();
    formData.append('document', fileupload.value.files[0]) 

    axios.post('/documents/upload', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(res => console.log(res.data) )
    .catch(err => console.error(err));
    visible.value = false
};
</script>

<template>
    <Head title="Documents" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <Button 
                label="Add document"
                rounded
                class="w-40 px-2 py-1 text-sm ml-auto"
                @click="addDocument"
            />
            <DocumentsIndex 
                :documents="documents" 
                :loading="loading"
            />
        </div>
        <Dialog 
            v-model:visible="visible"
            modal
            header="Upload"
        >
            <span class="text-surface-500 dark:text-surface-400 block mb-8">Upload document</span>
            <div class="flex items-center gap-4 mb-4">
                <FileUpload
                    ref="fileupload"
                    name="document"
                    :auto="false"         
                    :maxFileSize="1000000"
                />
                
            </div>
            
            <div class="flex justify-end gap-2">
                <Button type="button" label="Cancel" severity="secondary" @click="visible = false"></Button>
                <Button type="button" label="Save" @click="upload"></Button>
            </div>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}
</style>