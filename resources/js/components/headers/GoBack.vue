<template>
	<Button icon="pi pi-angle-left" class="mr-2 border-none" severity="secondary" text @click="emits('goBack')" v-if="isLTR()" />
	<Button icon="pi pi-angle-right" class="ml-2 border-none" severity="secondary" text @click="emits('goBack')" v-else />
</template>
<script setup lang="ts">
import { useLtRorRtL } from "@/utils/Helpers";
import Button from "primevue/button";

const { isLTR } = useLtRorRtL();

const emits = defineEmits<{
	goBack: [];
}>();
</script>
