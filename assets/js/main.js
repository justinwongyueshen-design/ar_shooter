const statusToast = document.getElementById('statusToast');
const showError = (error) => {
    const message = error instanceof Error
        ? error.message
        : error == null
            ? 'MindAR could not open the camera. Check permission for this address, select an available camera, and close other apps using it.'
            : String(error);
    console
    console.error('AR startup failed:', error);
    statusToast.innerText = `AR STARTUP FAILED: ${message}`;
};

window.addEventListener('error', (event) => showError(event.error || event.message));
window.addEventListener('unhandledrejection', (event) => showError(event.reason));

window.addEventListener('DOMContentLoaded', async () => {
    try {
        const scriptVersion = document.querySelector('meta[name="script-version"]')?.content || '';
        const { GameManager } = await import(`./game.js?v=${scriptVersion}`);
        const game = new GameManager();
        await game.init();
    } catch (error) {
        showError(error);
        return;
    }

    const restartBtn = document.getElementById('restartBtn');
    if (restartBtn) {
        restartBtn.addEventListener('click', () => {
            window.location.reload();
        });
    }
});