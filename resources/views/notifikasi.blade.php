<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Notifikasi Perpustakaan</title>
</head>
<body class="bg-gray-100 p-6">

  <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- NOTIFIKASI BIASA (BIRU) -->
    <div class="space-y-4" id="notif-list"></div>


    <!-- PESAN (ABU-ABU) -->
    <div class="space-y-4" id="message-list"></div>

  </div>

  <script>
    function csrf() {
      const el = document.querySelector('meta[name="csrf-token"]');
      return el ? el.getAttribute('content') : '';
    }
    async function fetchJSON(url, options = {}) {
      const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf(),
        ...(options.headers || {})
      };
      const res = await fetch(url, { ...options, headers });
      if (!res.ok) throw new Error('Network error');
      return res.json();
    }

    function renderNotifItem(n) {
      const icon = n.tipe === 'warning' ? '⚠️' : n.tipe === 'success' ? '✅' : n.tipe === 'error' ? '⛔' : '📘';
      const div = document.createElement('div');
      div.className = 'bg-blue-100 rounded-xl p-4 shadow flex gap-3 items-start';
      div.innerHTML = `<span class="text-3xl">${icon}</span><p class="text-sm font-medium">${n.judul ? n.judul + ' — ' : ''}${n.pesan}</p>`;
      return div;
    }

    function renderMessageItem(m) {
      const div = document.createElement('div');
      div.className = 'bg-gray-200 p-4 rounded-xl shadow';
      const statusText = m.status === 'confirmed' ? '<span class="text-green-600 text-xs ml-2">Dikonfirmasi</span>' : '';
      div.innerHTML = `
        <p class="font-semibold text-sm">Perpus ${statusText}</p>
        <div class="flex gap-3 mt-2">
          <span class="text-3xl">👤</span>
          <p class="text-sm">${m.isi}</p>
        </div>
        <div class="flex justify-end mt-2 text-xs gap-4">
          ${m.status === 'sent' ? `<button class="text-green-600" data-action="confirm" data-id="${m.id}">Konfirmasi</button>` : ''}
          <button class="text-blue-500" data-action="reply" data-id="${m.id}">Balas</button>
        </div>
      `;
      div.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', async () => {
          const id = btn.getAttribute('data-id');
          const action = btn.getAttribute('data-action');
          try {
            if (action === 'confirm') {
              await fetchJSON(`/api/pesan/${id}/confirm`, { method: 'PUT' });
            }
            if (action === 'reply') {
              const isi = prompt('Tulis balasan:');
              if (isi) {
                await fetchJSON(`/api/pesan/${id}/reply`, {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ isi })
                });
              }
            }
            await loadAll();
          } catch (e) {
            alert('Gagal memproses aksi');
          }
        });
      });
      return div;
    }

    async function loadAll() {
      try {
        const notif = await fetchJSON('/api/notifikasi');
        const notifList = document.getElementById('notif-list');
        notifList.innerHTML = '';
        notif.notifikasi.forEach(n => notifList.appendChild(renderNotifItem(n)));

        const messages = await fetchJSON('/api/pesan?only_inbox=1');
        const msgList = document.getElementById('message-list');
        msgList.innerHTML = '';
        messages.data.forEach(m => msgList.appendChild(renderMessageItem(m)));
      } catch (e) {
        console.error(e);
      }
    }

    loadAll();
  </script>
</body>
</html>
