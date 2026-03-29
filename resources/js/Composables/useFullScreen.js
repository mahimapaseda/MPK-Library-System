import { ref, onMounted, onUnmounted } from 'vue';

export function useFullScreen() {
    const isFullScreen = ref(false);

    const toggleFullScreen = () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.error(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
            });
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    };

    const handleFullScreenChange = () => {
        isFullScreen.value = !!document.fullscreenElement;
    };

    onMounted(() => {
        document.addEventListener('fullscreenchange', handleFullScreenChange);
    });

    onUnmounted(() => {
        document.removeEventListener('fullscreenchange', handleFullScreenChange);
    });

    return {
        isFullScreen,
        toggleFullScreen
    };
}
