<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Dropdown from 'primevue/dropdown';
import { ref, computed } from 'vue';

const { locale } = useI18n();

const languages = ref([
    { code: 'ro', name: 'Română', flag: '🇷🇴' },
    { code: 'en', name: 'English', flag: '🇺🇸' }
]);

const selectedLanguage = computed({
    get: () => languages.value.find(lang => lang.code === locale.value),
    set: (language) => {
        if (language) {
            locale.value = language.code;
            localStorage.setItem('locale', language.code);
        }
    }
});
</script>

<template>
    <div class="language-switcher">
        <Dropdown
            v-model="selectedLanguage"
            :options="languages"
            option-label="name"
            :showClear="false"
            class="w-40"
        >
            <template #value="slotProps">
                <div v-if="slotProps.value" class="flex items-center gap-2">
                    <span>{{ slotProps.value.flag }}</span>
                    <span>{{ slotProps.value.name }}</span>
                </div>
            </template>
            <template #option="slotProps">
                <div class="flex items-center gap-2">
                    <span>{{ slotProps.option.flag }}</span>
                    <span>{{ slotProps.option.name }}</span>
                </div>
            </template>
        </Dropdown>
    </div>
</template>

<style scoped>
.language-switcher {
    display: flex;
    align-items: center;
}
</style>