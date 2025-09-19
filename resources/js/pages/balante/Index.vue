<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import  IconField  from 'primevue/iconfield';
import  InputText  from 'primevue/inputtext';
import  InputIcon  from 'primevue/inputicon';
import Button from 'primevue/button'
import DocumentsIndex from '@/components/DocumentsIndex.vue';
import Dialog from 'primevue/dialog';
import axios  from 'axios'
import FileUpload from 'primevue/fileupload';
import { defineProps } from 'vue'
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Documents',
        href: '/documents',
    },
];

const props = defineProps({
      balante: Object, // Paginated object from Laravel
      filters: Object
  })

  // Use props directly
console.log(props.balante)

const loading = ref(false);
const visible = ref(false)
const toast = useToast();

const addDocument = () => {
    visible.value = true
}

const goToPage = (url: string) => {
    if (url) {
        router.get(url)
    }
}

const fileupload = ref()

const upload = () => {
    console.log('upload')
    if (!fileupload.value.files.length) return;
    console.log('upload file exists')
    const formData = new FormData();
    formData.append('balanta', fileupload.value.files[0])

    axios.post('/balante/upload', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(res => {
        console.log(res.data)
        toast.add({
            severity: 'success',
            summary: 'Upload Successful',
            detail: 'File will be processed in the background',
            life: 5000
        });
        visible.value = false
    })
    .catch(err => {
        console.error(err);
        toast.add({
            severity: 'error',
            summary: 'Upload Failed',
            detail: 'There was an error uploading your file',
            life: 5000
        });
        visible.value = false
    });
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
            <DataTable
                :value="balante.data"
                :paginator="false"
                :loading="loading"
                stripedRows
                sortable
                resizableColumns
            >
                <Column field="cont" header="Cont" sortable></Column>
                <Column field="denumirea_contului" header="Denumirea Contului" sortable></Column>
                <Column field="solduri_initiale_an" header="Solduri Initiale">
                    <template #body="slotProps">
                        D: {{ slotProps.data.solduri_initiale_an?.debitoare || 0 }} |
                        C: {{ slotProps.data.solduri_initiale_an?.creditoare || 0 }}
                    </template>
                </Column>
                <Column field="solduri_finale" header="Solduri Finale">
                    <template #body="slotProps">
                        D: {{ slotProps.data.solduri_finale?.debitoare || 0 }} |
                        C: {{ slotProps.data.solduri_finale?.creditoare || 0 }}
                    </template>
                </Column>
            </DataTable>

            <!-- Custom pagination -->
            <div class="flex justify-between items-center mt-4" v-if="balante.links">
                <span class="text-sm text-gray-600">
                    Showing {{ balante.from }}-{{ balante.to }} of {{ balante.total }}
                </span>

                <div class="flex space-x-1">
                    <Button
                        v-for="link in balante.links"
                        :key="link.label"
                        @click="goToPage(link.url)"
                        :disabled="!link.url"
                        :class="link.active ? 'p-button-primary' : 'p-button-secondary'"
                        size="small"
                        text
                    >
                        <span v-html="link.label"></span>
                    </Button>
                </div>
            </div>
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

        <Toast />
    </AppLayout>
</template>

<style scoped>
.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}
</style>