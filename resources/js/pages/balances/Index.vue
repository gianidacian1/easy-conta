<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
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
import Checkbox from 'primevue/checkbox'
import ConfirmDialog from 'primevue/confirmdialog';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';


const breadcrumbs = computed(() => [
    {
        title: t('balances.title'),
        href: '/balances',
    },
]);

const props = defineProps({
      balances: Object, // Paginated object from Laravel
      filters: Object
  })

  // Use props directly
console.log(props.balances)

const loading = ref(false);
const visible = ref(false)
const toast = useToast();
const confirm = useConfirm();
const { t } = useI18n();
const selectedRows = ref([])
const selectAll = ref(false)
const filePreview = ref(null)
const selectedFile = ref(null)

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    cont: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    denumirea_contului: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

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
    console.log('balances')
    if (!selectedFile.value) return;
    console.log('upload file exists')
    const formData = new FormData();
    formData.append('balance', selectedFile.value)

    axios.post('/balances/upload', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(res => {
        console.log(res.data)
        toast.add({
            severity: 'success',
            summary: t('balances.uploadSuccessful'),
            detail: t('balances.fileProcessed'),
            life: 5000
        });
        visible.value = false
        clearPreview()
        router.reload({ only: ['balances'] })


    })
    .catch(err => {
        console.error(err);

        toast.add({
            severity: 'error',
            summary: t('balances.uploadFailed'),
            detail: t('balances.uploadError'),
            life: 5000
        });
        visible.value = false
    });
};

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedRows.value = props.balances.data.map(item => item.id)
    } else {
        selectedRows.value = []
    }
}

const deleteSelected = () => {
    if (!selectedRows.value.length) return

    confirm.require({
        message: `${t('balances.areYouSureDelete')} ${selectedRows.value.length} ${t('balances.itemsDeleted')}?`,
        header: t('balances.deleteConfirmation'),
        icon: 'pi pi-info-circle',
        rejectProps: {
            label: t('common.cancel'),
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: t('common.delete'),
            severity: 'danger'
        },
        accept: () => {
            router.delete('/balances/bulk-delete', {
                data: { ids: selectedRows.value },
                onSuccess: () => {
                    toast.add({
                        severity: 'success',
                        summary: t('balances.deletedSuccessfully'),
                        detail: `${selectedRows.value.length} ${t('balances.itemsDeleted')}`,
                        life: 5000
                    });
                    selectedRows.value = []
                    selectAll.value = false
                }
            })
        }
    });
}

const onFileSelect = (event) => {
    const file = event.files[0]
    if (!file) return

    selectedFile.value = file

    // Generate preview based on file type
    if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
            filePreview.value = {
                type: 'image',
                url: e.target.result,
                name: file.name,
                size: formatFileSize(file.size)
            }
        }
        reader.readAsDataURL(file)
    } else {
        // For non-image files, show file info
        filePreview.value = {
            type: 'file',
            name: file.name,
            size: formatFileSize(file.size),
            extension: file.name.split('.').pop().toUpperCase()
        }
    }
}

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const clearPreview = () => {
    filePreview.value = null
    selectedFile.value = null
}
</script>

<template>
    <Head :title="t('balances.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex justify-between items-center">
                <Button
                    v-if="selectedRows.length > 0"
                    :label="t('balances.deleteSelected')"
                    severity="danger"
                    @click="deleteSelected"
                    class="px-4 py-2"
                />
                <Button
                    :label="t('balances.addDocument')"
                    rounded
                    class="w-40 px-2 py-1 text-sm ml-auto"
                    @click="addDocument"
                />
            </div>
            <DataTable
                v-model:filters="filters"
                :value="balances.data"
                :paginator="false"
                :loading="loading"
                stripedRows
                sortable
                resizableColumns
                filterDisplay="row"
                :globalFilterFields="['cont', 'denumirea_contului']"
            >
                <template #header>
                    <div class="flex justify-end">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" :placeholder="t('balances.searchBalances')" />
                        </IconField>
                    </div>
                </template>
                <template #empty>{{ t('balances.noBalancesFound') }}</template>
                <template #loading>{{ t('balances.loadingBalances') }}</template>
                <Column field="id" header="" style="width: 3rem">
                    <template #header>
                        <Checkbox
                            v-model="selectAll"
                            @change="toggleSelectAll"
                        />
                    </template>
                    <template #body="slotProps">
                        <Checkbox
                            v-model="selectedRows"
                            :value="slotProps.data.id"
                        />
                    </template>
                </Column>
                <Column field="cont" :header="t('balances.cont')" sortable></Column>
                <Column field="denumirea_contului" :header="t('balances.denumireaContului')" sortable></Column>
                <Column field="solduri_initiale_an" :header="t('balances.solduriInitiale')">
                    <template #body="slotProps">
                        D: {{ slotProps.data.solduri_initiale_an?.debitoare || 0 }} |
                        C: {{ slotProps.data.solduri_initiale_an?.creditoare || 0 }}
                    </template>
                </Column>
                <Column field="solduri_finale" :header="t('balances.solduriFinale')">
                    <template #body="slotProps">
                        D: {{ slotProps.data.solduri_finale?.debitoare || 0 }} |
                        C: {{ slotProps.data.solduri_finale?.creditoare || 0 }}
                    </template>
                </Column>
            </DataTable>

            <!-- Custom pagination -->
            <div class="flex justify-between items-center mt-4" v-if="balances.links">
                <span class="text-sm text-gray-600">
                    {{ t('common.showing') }} {{ balances.from }}-{{ balances.to }} {{ t('common.of') }} {{ balances.total }}
                </span>

                <div class="flex space-x-1">
                    <Button
                        v-for="link in balances.links"
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
            :header="t('common.upload')"
        >
            <span class="text-surface-500 dark:text-surface-400 block mb-8">{{ t('balances.uploadDocument') }}</span>
            <div class="flex items-center gap-4 mb-4">
                <FileUpload
                    ref="fileupload"
                    name="document"
                    :auto="false"
                    accept="image/*,.pdf,.xlsx,.xls,.doc,.docx"
                    :showUploadButton="false"
                    :showCancelButton="false"
                    @select="onFileSelect"
                    @clear="clearPreview"
                />

            </div>

            <!-- File Preview Section -->
            <div v-if="filePreview" class="mb-4 p-4 border rounded-lg bg-gray-50">
                <h4 class="text-sm font-medium mb-2">{{ t('balances.filePreview') }}:</h4>

                <!-- Image Preview -->
                <div v-if="filePreview.type === 'image'" class="flex items-center gap-4">
                    <img :src="filePreview.url" :alt="filePreview.name" class="w-20 h-20 object-cover rounded border" />
                    <div>
                        <p class="font-medium">{{ filePreview.name }}</p>
                        <p class="text-sm text-gray-600">{{ filePreview.size }}</p>
                    </div>
                </div>

                <!-- File Info Preview -->
                <div v-else class="flex items-center gap-4">
                    <div class="w-20 h-20 bg-blue-100 rounded border flex items-center justify-center">
                        <span class="text-xs font-bold text-blue-600">{{ filePreview.extension }}</span>
                    </div>
                    <div>
                        <p class="font-medium">{{ filePreview.name }}</p>
                        <p class="text-sm text-gray-600">{{ filePreview.size }}</p>
                        <p class="text-xs text-gray-500">{{ filePreview.extension }} file</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button type="button" :label="t('common.cancel')" severity="secondary" @click="visible = false"></Button>
                <Button type="button" :label="t('common.save')" @click="upload"></Button>
            </div>
        </Dialog>

        <Toast position="bottom-left" />
        <ConfirmDialog />
    </AppLayout>
</template>

<style scoped>
.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}
</style>