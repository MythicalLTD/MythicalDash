import Swal from 'sweetalert2';

class StorageMonitor {
    private localStorageKey = 'storageMonitorKey';
    private sessionStorageKey = 'sessionStorageMonitorKey';
    private cookieName = 'cookieMonitor';

    constructor() {
        this.init();
    }

    private init() {
        this.setInitialValues();
        this.monitorLocalStorage();
        this.monitorSessionStorage();
        this.monitorCookies();
    }

    private setInitialValues() {
        if (!localStorage.getItem(this.localStorageKey)) {
            localStorage.setItem(this.localStorageKey, 'initialized');
        }
        if (!sessionStorage.getItem(this.sessionStorageKey)) {
            sessionStorage.setItem(this.sessionStorageKey, 'initialized');
        }
        if (!this.getCookie(this.cookieName)) {
            this.setCookie(this.cookieName, 'initialized', 365);
        }
    }

    private monitorLocalStorage() {
        window.addEventListener('storage', (event) => {
            if (event.key === null || (event.key === this.localStorageKey && event.newValue === null)) {
                this.alertAndReload('Local Storage');
            }
        });
    }

    private monitorSessionStorage() {
        setInterval(() => {
            if (!sessionStorage.getItem(this.sessionStorageKey)) {
                this.alertAndReload('Session Storage');
            }
        }, 1000);
    }

    private monitorCookies() {
        setInterval(() => {
            if (!this.getCookie(this.cookieName)) {
                this.alertAndReload('Cookies');
            }
        }, 1000);
    }

    private alertAndReload(storageType: string) {
        Swal.fire({
            title: 'Security Alert',
            text: `We have detected a potential security threat in your ${storageType}. The page will now reload.`,
            icon: 'warning',
            showConfirmButton: false,
            timer: 5000,
            willOpen: () => {
                Swal.showLoading();
            },
        });
        setTimeout(() => {
            window.location.reload();
        }, 5000);
    }

    private setCookie(name: string, value: string, days: number) {
        const date = new Date();
        date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value};${expires};path=/`;
    }

    private getCookie(name: string) {
        const nameEQ = `${name}=`;
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
}

export default StorageMonitor;
