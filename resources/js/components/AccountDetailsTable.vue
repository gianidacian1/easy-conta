<template>
  <table class="w-full  text-sm">
    <thead>
      <tr>
        <th class="">Debitoare</th>
        <th class="">Creditoare</th>
      </tr>
    </thead>
    <tbody>
      <tr class="text-center">
        <td class="  ">
          {{ debitoare }}
        </td>
        <td class="  ">
          {{ creditoare }}
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script setup lang="ts">
defineProps<{
  debitoare: string | number
  creditoare: string | number
}>()
</script>
