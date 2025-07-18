<template>
	<div v-if="props.album.preFormattedData.url" class="w-full h-1/2 relative">
		<img class="absolute block top-0 left-0 w-full h-full object-cover object-center z-0" :src="props.album.preFormattedData.url" />
		<div class="h-full ltr:pl-7 rtl:pr-7 pt-7 relative text-shadow-sm w-full bg-gradient-to-b from-black/20 via-80%">
			<h1 class="font-bold text-4xl text-surface-0">{{ props.album.title }}</h1>
			<span v-if="props.album.preFormattedData.min_max_text" class="text-surface-200 text-sm">
				{{ props.album.preFormattedData.min_max_text }}
			</span>
		</div>
	</div>
	<Card class="w-full">
		<template #content>
			<div class="w-full flex flex-row-reverse items-start">
				<div class="order-1 flex flex-col w-full">
					<h1 v-if="!props.album.preFormattedData.url" class="font-bold text-2xl">{{ props.album.title }}</h1>
					<span v-if="props.album.preFormattedData.created_at" class="block text-muted-color text-sm">
						{{ $t("gallery.album.hero.created") }} {{ props.album.preFormattedData.created_at }}
					</span>
					<span v-if="props.album.preFormattedData.copyright" class="block text-muted-color text-sm">
						{{ $t("gallery.album.hero.copyright") }} {{ props.album.preFormattedData.copyright }}
					</span>
					<span v-if="props.album.preFormattedData.num_children" class="block text-muted-color text-sm">
						{{ props.album.preFormattedData.num_children }} {{ $t("gallery.album.hero.subalbums") }}
					</span>
					<span v-if="props.album.preFormattedData.num_photos" class="block text-muted-color text-sm">
						{{ props.album.preFormattedData.num_photos }} {{ $t("gallery.album.hero.images") }}
						<span v-if="props.album.preFormattedData.license" class="text-muted-color text-sm">
							&mdash; {{ props.album.preFormattedData.license }}
						</span>
					</span>
				</div>
				<div class="flex flex-col w-full gap-2">
					<div class="flex flex-row-reverse items-center">
						<a
							v-if="props.album.rights.can_download"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							:title="$t('gallery.album.hero.download')"
							@click="download"
						>
							<i class="pi pi-cloud-download" />
						</a>
						<a
							v-if="props.album.rights.can_share"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							:title="$t('gallery.album.hero.share')"
							v-on:click="openSharingModal"
						>
							<i class="pi pi-share-alt" />
						</a>
						<a
							v-if="is_se_enabled && user?.id !== null"
							class="shrink-0 px-3 cursor-pointer inline-block transform duration-300 hover:scale-150 hover:text-color"
							v-on:click="openStatistics"
						>
							<i class="pi pi-chart-scatter text-primary-emphasis" />
						</a>
						<a
							v-if="is_se_preview_enabled && user?.id !== null"
							class="shrink-0 px-3 cursor-not-allowed text-primary-emphasis"
							v-tooltip.left="$t('gallery.album.hero.stats_only_se')"
						>
							<i class="pi pi-chart-scatter" />
						</a>
						<router-link
							:to="{ name: 'frame', params: { albumId: props.album.id } }"
							v-if="props.config.is_mod_frame_enabled"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
							v-tooltip.bottom="'Frame'"
						>
							<i class="pi pi-desktop" />
						</router-link>
						<router-link
							:to="{ name: 'map', params: { albumId: props.album.id } }"
							v-if="props.config.is_map_accessible && hasCoordinates"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
						>
							<i class="pi pi-map" />
						</router-link>
						<a
							v-tooltip.bottom="'Start slideshow'"
							@click="emits('toggleSlideShow')"
							v-if="props.album.photos.length > 0 && is_slideshow_enabled"
							class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
						>
							<i class="pi pi-play" />
						</a>

						<template v-if="isTouchDevice() && user?.id !== null">
							<a
								v-if="props.hasHidden && are_nsfw_visible"
								class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
								:title="'hide hidden'"
								@click="are_nsfw_visible = false"
							>
								<i class="pi pi pi-eye-slash" />
							</a>
							<a
								v-if="props.hasHidden && !are_nsfw_visible"
								class="shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-150 hover:text-color"
								:title="'show hidden'"
								@click="are_nsfw_visible = true"
							>
								<i class="pi pi-eye" />
							</a>
						</template>
					</div>
					<AlbumStatistics v-if="props.album.statistics" :stats="props.album.statistics" />
				</div>
			</div>
			<div
				v-if="props.album.preFormattedData.description"
				class="w-full max-w-full my-4 text-justify text-muted-color text-base/5 prose dark:prose-invert prose-sm"
				v-html="props.album.preFormattedData.description"
			/>
		</template>
	</Card>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import { useAuthStore } from "@/stores/Auth";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import Card from "primevue/card";
import { computed } from "vue";
import AlbumStatistics from "./AlbumStatistics.vue";

const auth = useAuthStore();
const lycheeStore = useLycheeStateStore();
const { is_se_enabled, is_se_preview_enabled, are_nsfw_visible, is_slideshow_enabled } = storeToRefs(lycheeStore);
const { user } = storeToRefs(auth);

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource;
	hasHidden: boolean;
	config: {
		is_map_accessible: boolean;
		is_mod_frame_enabled: boolean;
	};
}>();

const hasCoordinates = computed(() => props.album.photos.some((photo) => photo.latitude !== null && photo.longitude !== null));

const emits = defineEmits<{
	openSharingModal: [];
	openStatistics: [];
	toggleSlideShow: [];
}>();

function openSharingModal() {
	emits("openSharingModal");
}

function openStatistics() {
	emits("openStatistics");
}

function download() {
	AlbumService.download([props.album?.id]);
}
</script>
