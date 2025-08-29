<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted } from 'vue';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import { queryParams } from '@/routes/wayfinder';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard.url
    },
];

const fileupload = ref();

const upload = () => {
    if (!fileupload.value.files.length) return;

    const formData = new FormData();
    formData.append('document', fileupload.value.files[0]) 
  

    axios.post('/upload-document', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(res => console.log(res.data))
    .catch(err => console.error(err));
};

const onUpload = () => {
    console.log('onUpload')
};
const processDocument = () => {
    console.log('processDocument')

    axios.post('/process-document', {documentId: 'name'}, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(res => console.log(res.data))
    .catch(err => console.error(err));
};

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div>
                <Button @click="processDocument">Process</Button>
            </div>
            <div class="card flex flex-wrap gap-6 items-center justify-between">
                <FileUpload
                    ref="fileupload"
                    name="document"
                    :auto="false"         
                    :maxFileSize="1000000"
                />
                <Button label="Upload" @click="upload" severity="secondary" />
            </div>
           

        </div>
    </AppLayout>
</template>
