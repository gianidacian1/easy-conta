<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { FilterMatchMode } from '@primevue/core/api';
import IconField from 'primevue/iconfield';
import InputText from 'primevue/inputtext';
import InputIcon from 'primevue/inputicon';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Checkbox from 'primevue/checkbox';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import Card from 'primevue/card';
import Badge from 'primevue/badge';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

const { t } = useI18n();

const breadcrumbs = computed(() => [
    {
        title: t('dashboard.title'),
        href: '/dashboard',
    },
]);

const props = defineProps({
    dashboardWidgets: Object,
    stats: Object,
    filters: Object
});

const loading = ref(false);
const visible = ref(false);
const toast = useToast();
const confirm = useConfirm();
const selectedRows = ref([]);
const selectAll = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    title: { value: null, matchMode: FilterMatchMode.CONTAINS },
    widget_type: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const addWidget = () => {
    visible.value = true;
};

const goToPage = (url: string) => {
    if (url) {
        router.get(url);
    }
};

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedRows.value = props.dashboardWidgets.data.map(item => item.id);
    } else {
        selectedRows.value = [];
    }
};

const deleteSelected = () => {
    if (!selectedRows.value.length) return;

    confirm.require({
        message: `${t('dashboard.areYouSureDeleteWidgets')} ${selectedRows.value.length} ${t('dashboard.selectedWidgets')}?`,
        header: t('dashboard.deleteConfirmation'),
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
            router.delete('/dashboard/bulk-delete', {
                data: { ids: selectedRows.value },
                onSuccess: () => {
                    toast.add({
                        severity: 'success',
                        summary: t('dashboard.deletedSuccessfully'),
                        detail: `${selectedRows.value.length} ${t('dashboard.widgetsDeleted')}`,
                        life: 5000
                    });
                    selectedRows.value = [];
                    selectAll.value = false;
                }
            });
        }
    });
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};

const getWidgetTypeColor = (type) => {
    const colors = {
        chart: 'info',
        table: 'success',
        metric: 'warning',
        text: 'secondary'
    };
    return colors[type] || 'secondary';
};
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">

            <!-- Stats Cards Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <Card class="bg-blue-50 border-blue-200">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-600 text-sm font-medium">{{ t('dashboard.totalBalances') }}</p>
                                <p class="text-2xl font-bold text-blue-800">{{ stats.total_balances }}</p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i class="pi pi-chart-line text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="bg-green-50 border-green-200">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 text-sm font-medium">{{ t('dashboard.totalZDocuments') }}</p>
                                <p class="text-2xl font-bold text-green-800">{{ stats.total_z_documents }}</p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <i class="pi pi-file text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="bg-purple-50 border-purple-200">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-600 text-sm font-medium">{{ t('dashboard.dashboardWidgets') }}</p>
                                <p class="text-2xl font-bold text-purple-800">{{ stats.total_dashboard_widgets }}</p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i class="pi pi-th-large text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="bg-orange-50 border-orange-200">
                    <template #content>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-600 text-sm font-medium">{{ t('dashboard.recentActivity') }}</p>
                                <p class="text-2xl font-bold text-orange-800">{{ stats.recent_balances.length + stats.recent_z_documents.length }}</p>
                            </div>
                            <div class="bg-orange-100 p-3 rounded-full">
                                <i class="pi pi-clock text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Recent Activity Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Recent Balances -->
                <Card>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-chart-bar text-blue-600"></i>
                            {{ t('dashboard.recentBalances') }}
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-3">
                            <div v-for="balance in stats.recent_balances" :key="balance.id"
                                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium">{{ balance.cont }}</p>
                                    <p class="text-sm text-gray-600">{{ balance.denumirea_contului }}</p>
                                </div>
                                <Badge :value="formatDate(balance.created_at)" severity="info" />
                            </div>
                            <div v-if="stats.recent_balances.length === 0" class="text-center py-4 text-gray-500">
                                {{ t('dashboard.noRecentBalances') }}
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Recent Z Documents -->
                <Card>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-file-text text-green-600"></i>
                            {{ t('dashboard.recentZDocuments') }}
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-3">
                            <div v-for="doc in stats.recent_z_documents" :key="doc.id"
                                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium">Z-{{ doc.number }}</p>
                                    <p class="text-sm text-gray-600">{{ t('dashboard.sales') }}: {{ doc.sales }} | {{ t('dashboard.balance') }}: {{ doc.final_balance }}</p>
                                </div>
                                <Badge :value="formatDate(doc.created_at)" severity="success" />
                            </div>
                            <div v-if="stats.recent_z_documents.length === 0" class="text-center py-4 text-gray-500">
                                {{ t('dashboard.noRecentZDocuments') }}
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Dashboard Widgets Management -->
            <Card>
                <template #title>{{ t('dashboard.widgets') }}</template>
                <template #content>
                    <div class="flex justify-between items-center mb-4">
                        <Button
                            v-if="selectedRows.length > 0"
                            :label="t('dashboard.deleteSelected')"
                            severity="danger"
                            @click="deleteSelected"
                            class="px-4 py-2"
                        />
                        <Button
                            :label="t('dashboard.addWidget')"
                            rounded
                            class="w-40 px-2 py-1 text-sm ml-auto"
                            @click="addWidget"
                        />
                    </div>

                    <DataTable
                        v-model:filters="filters"
                        :value="dashboardWidgets.data"
                        :paginator="false"
                        :loading="loading"
                        stripedRows
                        sortable
                        resizableColumns
                        filterDisplay="row"
                        :globalFilterFields="['title', 'description', 'widget_type']"
                    >
                        <template #header>
                            <div class="flex justify-end">
                                <IconField>
                                    <InputIcon>
                                        <i class="pi pi-search" />
                                    </InputIcon>
                                    <InputText v-model="filters['global'].value" :placeholder="t('dashboard.searchWidgets')" />
                                </IconField>
                            </div>
                        </template>
                        <template #empty>{{ t('dashboard.noWidgetsFound') }}</template>
                        <template #loading>{{ t('dashboard.loadingDashboard') }}</template>

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
                        <Column field="title" :header="t('dashboard.widgetTitle')" sortable></Column>
                        <Column field="description" :header="t('dashboard.widgetDescription')" sortable></Column>
                        <Column field="widget_type" :header="t('dashboard.widgetType')" sortable>
                            <template #body="slotProps">
                                <Badge :value="slotProps.data.widget_type" :severity="getWidgetTypeColor(slotProps.data.widget_type)" />
                            </template>
                        </Column>
                        <Column field="size" :header="t('dashboard.widgetSize')" sortable></Column>
                        <Column field="position" :header="t('dashboard.widgetPosition')" sortable></Column>
                        <Column field="is_active" :header="t('dashboard.widgetStatus')" sortable>
                            <template #body="slotProps">
                                <Badge :value="slotProps.data.is_active ? t('dashboard.active') : t('dashboard.inactive')"
                                       :severity="slotProps.data.is_active ? 'success' : 'secondary'" />
                            </template>
                        </Column>
                        <Column field="created_at" :header="t('dashboard.widgetCreated')" sortable>
                            <template #body="slotProps">
                                {{ formatDate(slotProps.data.created_at) }}
                            </template>
                        </Column>
                    </DataTable>

                    <!-- Custom pagination -->
                    <div class="flex justify-between items-center mt-4" v-if="dashboardWidgets.links">
                        <span class="text-sm text-gray-600">
                            {{ t('common.showing') }} {{ dashboardWidgets.from }}-{{ dashboardWidgets.to }} {{ t('common.of') }} {{ dashboardWidgets.total }}
                        </span>

                        <div class="flex space-x-1">
                            <Button
                                v-for="link in dashboardWidgets.links"
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
                </template>
            </Card>
        </div>

        <!-- Add Widget Dialog -->
        <Dialog
            v-model:visible="visible"
            modal
            :header="t('dashboard.addDashboardWidget')"
        >
            <span class="text-surface-500 dark:text-surface-400 block mb-8">{{ t('dashboard.createNewWidget') }}</span>
            <div class="flex items-center gap-4 mb-4">
                <p class="text-sm text-gray-600">{{ t('dashboard.widgetCreationForm') }}</p>
            </div>

            <div class="flex justify-end gap-2">
                <Button type="button" :label="t('common.cancel')" severity="secondary" @click="visible = false"></Button>
                <Button type="button" :label="t('common.save')" @click="visible = false"></Button>
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