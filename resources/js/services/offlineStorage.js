import { ref } from 'vue';

const DB_NAME = 'bpsdm_pelaporan_db';
const DB_VERSION = 1;

// Reactive Network & Sync State
export const isOnline = ref(typeof navigator !== 'undefined' ? navigator.onLine : true);
export const isSyncing = ref(false);
export const queuedCount = ref(0);
export const syncSuccessMessage = ref('');

let dbPromise = null;

/**
 * Open or initialize IndexedDB
 */
export function openDatabase() {
  if (dbPromise) return dbPromise;

  dbPromise = new Promise((resolve, reject) => {
    if (typeof window === 'undefined' || !window.indexedDB) {
      reject(new Error('IndexedDB tidak didukung pada perangkat ini.'));
      return;
    }

    const request = window.indexedDB.open(DB_NAME, DB_VERSION);

    request.onupgradeneeded = (event) => {
      const db = event.target.result;

      // 1. Store untuk draft form aktif
      if (!db.objectStoreNames.contains('drafts')) {
        db.createObjectStore('drafts', { keyPath: 'key' });
      }

      // 2. Store untuk antrian laporan offline (outbox)
      if (!db.objectStoreNames.contains('outbox')) {
        db.createObjectStore('outbox', { keyPath: 'id', autoIncrement: true });
      }

      // 3. Store untuk cache data master (locations, templates) saat offline
      if (!db.objectStoreNames.contains('masters')) {
        db.createObjectStore('masters', { keyPath: 'key' });
      }
    };

    request.onsuccess = (event) => {
      const db = event.target.result;
      resolve(db);
      updateQueuedCount();
    };

    request.onerror = (event) => {
      console.error('IndexedDB open error:', event.target.error);
      reject(event.target.error);
    };
  });

  return dbPromise;
}

// ─────────────────────────────────────────────────────────────────────────────
// 1. DRAFT STORAGE (Form data + foto binary/blob)
// ─────────────────────────────────────────────────────────────────────────────

export async function saveFormDraft(formData) {
  try {
    const db = await openDatabase();
    return new Promise((resolve, reject) => {
      const tx = db.transaction('drafts', 'readwrite');
      const store = tx.objectStore('drafts');
      const record = {
        key: 'current_form_draft',
        updatedAt: new Date().toISOString(),
        data: formData,
      };
      const req = store.put(record);
      req.onsuccess = () => resolve(true);
      req.onerror = () => reject(req.error);
    });
  } catch (err) {
    console.error('Gagal menyimpan draft ke IndexedDB:', err);
    // Fallback to localStorage without large files
    try {
      const textOnly = {
        ...formData,
        officer_photo: null,
        locations: formData.locations?.map(l => ({ ...l, photo_1: null, photo_2: null })),
      };
      localStorage.setItem('bpsdm_report_draft_text', JSON.stringify(textOnly));
    } catch {}
    return false;
  }
}

export async function getFormDraft() {
  try {
    const db = await openDatabase();
    return new Promise((resolve) => {
      const tx = db.transaction('drafts', 'readonly');
      const store = tx.objectStore('drafts');
      const req = store.get('current_form_draft');
      req.onsuccess = () => resolve(req.result ? req.result.data : null);
      req.onerror = () => resolve(null);
    });
  } catch (err) {
    console.warn('Gagal membaca draft dari IndexedDB:', err);
    return null;
  }
}

export async function clearFormDraft() {
  try {
    const db = await openDatabase();
    return new Promise((resolve) => {
      const tx = db.transaction('drafts', 'readwrite');
      const store = tx.objectStore('drafts');
      const req = store.delete('current_form_draft');
      req.onsuccess = () => {
        localStorage.removeItem('bpsdm_report_draft_text');
        localStorage.removeItem('bpsdm_report_draft');
        resolve(true);
      };
      req.onerror = () => resolve(false);
    });
  } catch (err) {
    localStorage.removeItem('bpsdm_report_draft_text');
    localStorage.removeItem('bpsdm_report_draft');
    return false;
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 2. MASTER DATA CACHE (Untuk digunakan saat aplikasi dibuka offline)
// ─────────────────────────────────────────────────────────────────────────────

export async function cacheMasterData(key, data) {
  try {
    const db = await openDatabase();
    return new Promise((resolve) => {
      const tx = db.transaction('masters', 'readwrite');
      const store = tx.objectStore('masters');
      const req = store.put({ key, data, cachedAt: new Date().toISOString() });
      req.onsuccess = () => resolve(true);
      req.onerror = () => resolve(false);
    });
  } catch {
    return false;
  }
}

export async function getCachedMasterData(key) {
  try {
    const db = await openDatabase();
    return new Promise((resolve) => {
      const tx = db.transaction('masters', 'readonly');
      const store = tx.objectStore('masters');
      const req = store.get(key);
      req.onsuccess = () => resolve(req.result ? req.result.data : null);
      req.onerror = () => resolve(null);
    });
  } catch {
    return null;
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. OUTBOX QUEUE (Laporan lengkap yang disimpan saat koneksi offline)
// ─────────────────────────────────────────────────────────────────────────────

export async function queueOfflineReport(reportPayload) {
  const db = await openDatabase();
  return new Promise((resolve, reject) => {
    const tx = db.transaction('outbox', 'readwrite');
    const store = tx.objectStore('outbox');
    const record = {
      createdAt: new Date().toISOString(),
      report_number: reportPayload.report_number,
      officer_name: reportPayload.officer_name,
      inspection_date: reportPayload.inspection_date,
      template_id: reportPayload.template_id,
      officer_photo: reportPayload.officer_photo || null,
      locations: reportPayload.locations || [],
      status: 'pending_sync',
    };
    const req = store.add(record);
    req.onsuccess = (e) => {
      updateQueuedCount();
      resolve(e.target.result);
    };
    req.onerror = () => reject(req.error);
  });
}

export async function getQueuedReports() {
  const db = await openDatabase();
  return new Promise((resolve) => {
    const tx = db.transaction('outbox', 'readonly');
    const store = tx.objectStore('outbox');
    const req = store.getAll();
    req.onsuccess = () => resolve(req.result || []);
    req.onerror = () => resolve([]);
  });
}

export async function removeQueuedReport(id) {
  const db = await openDatabase();
  return new Promise((resolve) => {
    const tx = db.transaction('outbox', 'readwrite');
    const store = tx.objectStore('outbox');
    const req = store.delete(id);
    req.onsuccess = () => {
      updateQueuedCount();
      resolve(true);
    };
    req.onerror = () => resolve(false);
  });
}

export async function updateQueuedCount() {
  try {
    const list = await getQueuedReports();
    queuedCount.value = list.length;
  } catch {
    queuedCount.value = 0;
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 4. AUTO-SYNC ENGINE
// ─────────────────────────────────────────────────────────────────────────────

export async function syncOfflineQueue(apiInstance) {
  if (isSyncing.value || !isOnline.value) return;

  const queued = await getQueuedReports();
  if (queued.length === 0) {
    queuedCount.value = 0;
    return;
  }

  isSyncing.value = true;
  let successCount = 0;

  for (const item of queued) {
    try {
      const formData = new FormData();
      formData.append('report_number', item.report_number);
      formData.append('inspection_date', item.inspection_date);
      formData.append('officer_name', item.officer_name);
      formData.append('template_id', item.template_id);

      if (item.officer_photo instanceof Blob || item.officer_photo instanceof File) {
        formData.append('officer_photo', item.officer_photo, 'officer.jpg');
      }

      item.locations.forEach((loc, idx) => {
        formData.append(`locations[${idx}][location_id]`, loc.location_id);
        formData.append(`locations[${idx}][condition]`, loc.condition);
        if (loc.condition === 'clean') {
          formData.append(`locations[${idx}][notes]`, loc.notes || '');
        } else {
          formData.append(`locations[${idx}][problem]`, loc.problem || '');
          formData.append(`locations[${idx}][solution]`, loc.solution || '');
        }

        if (loc.photo_1 instanceof Blob || loc.photo_1 instanceof File) {
          formData.append(`locations.${idx}.photo_1`, loc.photo_1, `loc_${loc.location_id}_p1.jpg`);
        }
        if (loc.photo_2 instanceof Blob || loc.photo_2 instanceof File) {
          formData.append(`locations.${idx}.photo_2`, loc.photo_2, `loc_${loc.location_id}_p2.jpg`);
        }
      });

      const res = await apiInstance.post('/reports', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      if (res.data?.status === 'success') {
        await removeQueuedReport(item.id);
        successCount++;
      }
    } catch (err) {
      console.warn(`Sync failed for offline report #${item.id}:`, err.message);
      // Stop loop if network dropped again
      if (!navigator.onLine) break;
    }
  }

  await updateQueuedCount();
  isSyncing.value = false;

  if (successCount > 0) {
    syncSuccessMessage.value = `${successCount} laporan offline berhasil disinkronkan ke server.`;
    setTimeout(() => {
      syncSuccessMessage.value = '';
    }, 4000);
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// 5. EVENT LISTENERS SETUP
// ─────────────────────────────────────────────────────────────────────────────

export function setupOfflineListeners(apiInstance) {
  if (typeof window === 'undefined') return;

  const onOnline = () => {
    isOnline.value = true;
    syncOfflineQueue(apiInstance);
  };

  const onOffline = () => {
    isOnline.value = false;
  };

  window.addEventListener('online', onOnline);
  window.addEventListener('offline', onOffline);

  // Initial check
  isOnline.value = navigator.onLine;
  updateQueuedCount();
}

