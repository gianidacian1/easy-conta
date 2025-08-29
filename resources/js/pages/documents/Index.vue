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
import {Link} from '@inertiajs/vue3';

defineProps<{
  documents: any,
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Documents',
        href: '/documents',
    },
];

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


const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    denumirea_contului: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    // 'country.name': { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    // representative: { value: null, matchMode: FilterMatchMode.IN },
    // status: { value: null, matchMode: FilterMatchMode.EQUALS },
    // verified: { value: null, matchMode: FilterMatchMode.EQUALS }
});

const redirect = (id) => {
    console.log('redirect', id)
}
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
                    <Column field="file_name" header="Filename"></Column>
                    <Column field="user_id" header="Actions">
                        <template #body="slotProps">
                        <Link :href="'/documents/'+slotProps.data.id">
                            <Button
                                label="Edit"
                            />
                        </Link>
                        
                        </template>
                        
                    </Column>
            </DataTable>
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