<template>
  <nav class="sticky bottom-0 z-30 bg-white border-t border-slate-200 shadow-lg safe-bottom">
    <div class="flex items-center justify-around h-16 max-w-md mx-auto px-2">
      <!-- Beranda -->
      <router-link
        to="/"
        custom
        v-slot="{ href, navigate, isActive }"
      >
        <a
          :href="href"
          @click="navigate"
          :class="[
            'flex flex-col items-center justify-center flex-1 py-1 text-xs font-medium transition',
            isActive ? 'text-blue-700 font-semibold' : 'text-slate-500 hover:text-slate-700'
          ]"
        >
          <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span>Beranda</span>
        </a>
      </router-link>

      <!-- Buat Laporan (Special highlighted action button) -->
      <router-link
        to="/create"
        custom
        v-slot="{ href, navigate, isActive }"
      >
        <a
          :href="href"
          @click="navigate"
          class="flex flex-col items-center justify-center flex-1 -mt-4 group"
        >
          <div
            :class="[
              'w-13 h-13 rounded-full flex items-center justify-center shadow-lg transition transform active:scale-95',
              isActive
                ? 'bg-blue-800 text-white ring-4 ring-blue-200'
                : 'bg-blue-700 text-white hover:bg-blue-800'
            ]"
          >
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
          </div>
          <span :class="['text-[11px] font-semibold mt-1', isActive ? 'text-blue-700' : 'text-slate-600']">
            Buat Laporan
          </span>
        </a>
      </router-link>

      <!-- Riwayat -->
      <router-link
        to="/history"
        custom
        v-slot="{ href, navigate, isActive }"
      >
        <a
          :href="href"
          @click="navigate"
          :class="[
            'flex flex-col items-center justify-center flex-1 py-1 text-xs font-medium transition',
            isActive ? 'text-blue-700 font-semibold' : 'text-slate-500 hover:text-slate-700'
          ]"
        >
          <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Riwayat</span>
        </a>
      </router-link>

      <!-- Template -->
      <router-link
        to="/templates"
        custom
        v-slot="{ href, navigate, isActive }"
      >
        <a
          :href="href"
          @click="navigate"
          :class="[
            'flex flex-col items-center justify-center flex-1 py-1 text-xs font-medium transition',
            isActive ? 'text-blue-700 font-semibold' : 'text-slate-500 hover:text-slate-700'
          ]"
        >
          <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
          </svg>
          <span>Template</span>
        </a>
      </router-link>
    </div>
  </nav>
</template>

<script setup>
</script>

