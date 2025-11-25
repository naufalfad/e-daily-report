// public/js/global/notification.js
window.pushNotif = function({ type = 'info', title = '', message = '' }) {

    const icons = {
        success: '/assets/icon/check-green.svg',
        error: '/assets/icon/error-red.svg',
        warning: '/assets/icon/warning-yellow.svg',
        info: '/assets/icon/info-blue.svg'
    };

    const event = new CustomEvent('global-notif', {
        detail: {
            id: Date.now(),
            type,
            title,
            message,
            icon: icons[type] ?? icons.info,
            time: new Date().toLocaleString('id-ID')
        }
    });

    window.dispatchEvent(event);
};
