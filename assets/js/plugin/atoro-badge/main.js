function addDebugBadge() {
    if (window.location.hostname.split('.')[1] === 'id') return;
    const msg = document.currentScript.getAttribute('msg');

    const badge = document.createElement('div');
    badge.style.position = 'fixed';
    badge.style.bottom = '-80px'; // start offscreen
    badge.style.right = '16px';
    badge.style.padding = '8px';
    badge.style.backgroundColor = '#0F87FF';
    badge.style.color = '#fff';
    badge.style.fontWeight = 'bold';
    badge.style.zIndex = '9999';
    badge.style.borderRadius = '10px';
    badge.style.textAlign = 'center';
    badge.textContent = msg;
    document.body.appendChild(badge);
    const animateIn = () => {
        let pos = -80;
        const interval = setInterval(() => {
            if (pos >= 80) {
                clearInterval(interval);
            } else {
                pos += 10; 
                badge.style.bottom = `${pos}px`;
            }
        }, 10);
    };

    animateIn();
}

addDebugBadge();
