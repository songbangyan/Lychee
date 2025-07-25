import { computed, Ref } from "vue";
import { useHasNextPreviousPhoto } from "./hasNextPreviousPhoto";

export enum ImageViewMode {
	Original = "original",
	Medium = "medium",
	Raw = "raw",
	Video = "video",
	LivePhotoMedium = "livephoto-medium",
	LivePhotoOriginal = "livephoto-original",
	Pdf = "pdf",
}

export function usePhotoBaseFunction(
	photo: Ref<App.Http.Resources.Models.PhotoResource | undefined>,
	photos: Ref<App.Http.Resources.Models.PhotoResource[]> | undefined = undefined,
	videoElement: Ref<HTMLVideoElement | null> | undefined = undefined,
) {
	const { hasNext, hasPrevious } = useHasNextPreviousPhoto(photo);

	const previousStyle = computed(() => {
		if (!hasPrevious() || photos === undefined) {
			return "";
		}

		const previousId = photo.value?.previous_photo_id;
		const previousPhoto = photos.value.find((p) => p.id === previousId);
		if (previousPhoto === undefined) {
			return "";
		}
		return "background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('" + previousPhoto.size_variants.thumb?.url + "')";
	});

	const nextStyle = computed(() => {
		if (!hasNext() || photos === undefined) {
			return "";
		}

		const nextId = photo.value?.next_photo_id;
		const nextPhoto = photos.value.find((p) => p.id === nextId);
		if (nextPhoto === undefined) {
			return "";
		}
		return "background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('" + nextPhoto.size_variants.thumb?.url + "')";
	});

	const srcSetMedium = computed(() => {
		const medium = photo.value?.size_variants.medium ?? null;
		const medium2x = photo.value?.size_variants.medium2x ?? null;
		if (medium === null || medium2x === null) {
			return "";
		}

		return `${medium.url} ${medium.width}w, ${medium2x.url} ${medium2x.width}w`;
	});

	const style = computed(() => {
		if (!photo.value?.precomputed.is_livephoto) {
			return "background-image: url(" + photo.value?.size_variants.small?.url + ")";
		}
		if (photo.value?.size_variants.medium !== null) {
			return "width: " + photo.value?.size_variants.medium.width + "px; height: " + photo.value?.size_variants.medium.height + "px";
		}
		if (photo.value?.size_variants.original === null) {
			return "";
		}
		return "width: " + photo.value?.size_variants.original.width + "px; height: " + photo.value?.size_variants.original.height + "px";
	});

	const imageViewMode = computed<ImageViewMode>(() => {
		if (photo.value?.precomputed.is_video) {
			return ImageViewMode.Video;
		}

		if (photo.value?.precomputed.is_raw) {
			if (photo.value?.size_variants.medium !== null) {
				return ImageViewMode.Medium;
			}
			if (photo.value?.size_variants.original?.url?.endsWith(".pdf")) {
				return ImageViewMode.Pdf;
			}
			return ImageViewMode.Raw;
		}

		if (photo.value?.precomputed.is_livephoto === true) {
			if (photo.value?.size_variants.medium !== null) {
				return ImageViewMode.LivePhotoMedium;
			}
			return ImageViewMode.LivePhotoOriginal;
		}

		if (photo.value?.size_variants.medium !== null) {
			return ImageViewMode.Medium;
		}
		return ImageViewMode.Original;
	});

	function refresh(): void {
		if (videoElement === undefined) {
			return;
		}

		// handle videos.
		const videoElementValue = videoElement.value;
		if (photo.value?.precomputed?.is_video && videoElementValue) {
			videoElementValue.src = photo.value?.size_variants?.original?.url ?? "";
			videoElementValue.load();
		}
	}

	return {
		previousStyle,
		nextStyle,
		srcSetMedium,
		style,
		imageViewMode,
		refresh,
	};
}
