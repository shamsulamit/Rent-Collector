import './bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

const LandlordLedger = {
    dbName: 'landlord-ledger-db',
    dbVersion: 1,
    db: null,

    async init() {
        this.registerServiceWorker();
        await this.openDb();
        this.setupListeners();
        this.dispatchSync();
    },

    registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    },

    openDb() {
        return new Promise((resolve, reject) => {
            if (this.db) return resolve(this.db);
            const request = indexedDB.open(this.dbName, this.dbVersion);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                if (!db.objectStoreNames.contains('outbox')) {
                    const store = db.createObjectStore('outbox', { keyPath: 'id', autoIncrement: true });
                    store.createIndex('synced', 'synced');
                }
                if (!db.objectStoreNames.contains('cache')) {
                    db.createObjectStore('cache', { keyPath: 'key' });
                }
            };

            request.onsuccess = () => {
                this.db = request.result;
                resolve(this.db);
            };

            request.onerror = () => reject(request.error);
        });
    },

    setupListeners() {
        window.addEventListener('online', () => this.dispatchSync());
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') this.dispatchSync();
        });
        window.addEventListener('sync', () => this.dispatchSync());
    },

    getMeta() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        return {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
    },

    async enqueue(endpoint, payload) {
        const db = await this.openDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction('outbox', 'readwrite');
            tx.objectStore('outbox').add({
                endpoint,
                payload,
                created_at: new Date().toISOString(),
                synced: false,
            });
            tx.oncomplete = () => {
                const syncing = window.Alpine?.store?.syncing ?? false;
                if (navigator.onLine) this.dispatchSync();
                resolve(true);
            };
            tx.onerror = () => reject(tx.error);
        });
    },

    async pendingCount() {
        const db = await this.openDb();
        return new Promise((resolve) => {
            const tx = db.transaction('outbox', 'readonly');
            const count = tx.objectStore('outbox').count();
            count.onsuccess = () => resolve(count.result);
        });
    },

    async dispatchSync() {
        if (!navigator.onLine) return;
        const db = await this.openDb();

        const items = await new Promise((resolve) => {
            const tx = db.transaction('outbox', 'readonly');
            const store = tx.objectStore('outbox');
            const all = store.getAll();
            all.onsuccess = () => resolve(all.result.filter((i) => !i.synced));
        });

        if (items.length === 0) return;

        this.setSyncing(true);
        for (const item of items) {
            try {
                const response = await fetch(item.endpoint, {
                    method: 'POST',
                    headers: this.getMeta(),
                    body: JSON.stringify(item.payload),
                });
                if (!response.ok) continue;
                await new Promise((resolve) => {
                    const tx = db.transaction('outbox', 'readwrite');
                    tx.objectStore('outbox').delete(item.id);
                    tx.oncomplete = resolve;
                });
            } catch (e) {
                break;
            }
        }
        this.setSyncing(false);
    },

    setSyncing(value) {
        document.dispatchEvent(new CustomEvent('ledger-sync', { detail: { syncing: value } }));
    },
};

window.LandlordLedger = LandlordLedger;
