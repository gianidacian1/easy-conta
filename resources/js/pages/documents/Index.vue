<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
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
import { router } from '@inertiajs/vue3'

defineProps<{
  documents: any,
}>()

const { t } = useI18n();

const breadcrumbs = computed(() => [
    {
        title: t('documents.title'),
        href: '/documents',
    },
]);

const loading = ref(false);
const visible = ref(false)

const addDocument = () => {
    visible.value = true
}

const fileupload = ref()

const upload = () => {
    console.log('upload')
    if (!fileupload.value.files.length) return;
    console.log('upload file exists', fileupload.value.files[0])
    const formData = new FormData();
    formData.append('document', fileupload.value.files[0])

    axios.post('/documents/upload', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(() => {
        // refresh documents list from server
        router.reload({ only: ['documents'] })
        visible.value = false
    })
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
    <Head :title="t('documents.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <Button
                :label="t('documents.addDocument')"
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
                                <InputText v-model="filters['global'].value" :placeholder="t('documents.keywordSearch')" />
                            </IconField>
                        </div>
                    </template>
                    <template #empty>{{ t('documents.noDocumentsFound') }}</template>
                    <template #loading>{{ t('documents.loadingDocuments') }}</template>
                    <Column field="filename" :header="t('documents.filename')"></Column>
                    <Column field="user_id" :header="t('common.actions')">
                        <template #body="slotProps">
                        <Link :href="'/documents/'+slotProps.data.id">
                            <Button
                                :label="t('common.edit')"
                            />
                        </Link>

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
            <span class="text-surface-500 dark:text-surface-400 block mb-8">{{ t('documents.uploadDocument') }}</span>
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
    </AppLayout>
</template>

<style scoped>
.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}
</style>