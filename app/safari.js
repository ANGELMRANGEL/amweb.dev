window.appSafari = {
    open: function(url) {
        if (!url) url = 'https://amweb.dev';
        const urlBar = document.getElementById('safari-url-bar');
        const container = document.getElementById('safari-iframe-container');
        const extLink = document.getElementById('safari-external-link');
        
        if (urlBar) urlBar.textContent = url;
        if (extLink) extLink.href = url;

        // Show the backdrop
        const backdrop = document.getElementById('safari-backdrop');
        if (backdrop) backdrop.style.display = 'block';

        // Show the loader overlay
        const loader = document.getElementById('safari-loader');
        if (loader) loader.style.display = 'flex';

        // Remove old iframe to clear the DOM
        const oldIframe = document.getElementById('safari-iframe');
        if (oldIframe) {
            oldIframe.remove();
        }

        // Create new iframe
        const iframe = document.createElement('iframe');
        iframe.id = 'safari-iframe';
        iframe.name = 'safari-frame';
        iframe.style.width = '100%';
        iframe.style.height = '100%';
        iframe.style.border = 'none';
        iframe.style.background = '#fff';
        iframe.style.display = 'none'; // Avoid white blank flash during load

        if (container) {
            container.appendChild(iframe);
        }

        iframe.onload = function() {
            if (loader) loader.style.display = 'none';
            iframe.style.display = 'block';
        };

        // Safety fallback timeout (2.5 seconds) to ensure loader hides if onload is blocked/cached
        setTimeout(() => {
            if (loader && loader.style.display !== 'none') {
                loader.style.display = 'none';
                iframe.style.display = 'block';
            }
        }, 2500);

        iframe.src = url;

        window.openDockModal('safari-modal');
    }
};
