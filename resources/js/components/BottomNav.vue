<template>
  <nav class="sticky bottom-0 z-30 bg-white/95 backdrop-blur-md border-t border-[#E8E6DE] shadow-[0_-4px_24px_rgba(28,25,23,0.04)] safe-bottom">
    <div class="flex items-center justify-around h-16 max-w-md mx-auto px-3">
      <!-- Beranda -->
      <router-link
        to="/"
        custom
        v-slot="{ href, navigate, isActive }"
      >
        <a
          :href="href"
          @click="navigate"
          class="flex flex-col items-center justify-center flex-1 h-full py-1 text-center group active-press transition"
        >
          <div
            :class="[
              'w-9 h-7 rounded-lg flex items-center justify-center transition-all duration-200',
              isActive ? 'bg-[#14251D] text-white shadow-sm' : 'text-stone-500 group-hover:text-stone-800'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.592 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
          </div>
          <span
            :class="[
              'text-[10px] tracking-tight mt-1 transition-colors',
              isActive ? 'font-bold text-[#14251D]' : 'font-medium text-stone-500'
            ]"
          >
            Beranda
          </span>
        </a>
      </router-link>

      <!-- Buat Laporan -->
      <router-link
        to="/create"
        custom
        v-slot="{ href, navigate, isActive }"
      >
        <a
          :href="href"
          @click="navigate"
          class="flex flex-col items-center justify-center flex-1 h-full py-1 text-center group active-press transition"
        >
          <div
            :class="[
              'w-9 h-7 rounded-lg flex items-center justify-center transition-all duration-200',
              isActive ? 'bg-[#14251D] text-[#D8C79C] shadow-sm' : 'bg-[#EBF1ED] text-[#1B382B] group-hover:bg-[#DEE7E1]'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
          </div>
          <span
            :class="[
              'text-[10px] tracking-tight mt-1 transition-colors',
              isActive ? 'font-bold text-[#14251D]' : 'font-semibold text-[#1B382B]'
            ]"
          >
            Input Laporan
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
          class="flex flex-col items-center justify-center flex-1 h-full py-1 text-center group active-press transition"
        >
          <div
            :class="[
              'w-9 h-7 rounded-lg flex items-center justify-center transition-all duration-200',
              isActive ? 'bg-[#14251D] text-white shadow-sm' : 'text-stone-500 group-hover:text-stone-800'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <span
            :class="[
              'text-[10px] tracking-tight mt-1 transition-colors',
              isActive ? 'font-bold text-[#14251D]' : 'font-medium text-stone-500'
            ]"
          >
            Arsip Riwayat
          </span>
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
          class="flex flex-col items-center justify-center flex-1 h-full py-1 text-center group active-press transition"
        >
          <div
            :class="[
              'w-9 h-7 rounded-lg flex items-center justify-center transition-all duration-200',
              isActive ? 'bg-[#14251D] text-white shadow-sm' : 'text-stone-500 group-hover:text-stone-800'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
          </div>
          <span
            :class="[
              'text-[10px] tracking-tight mt-1 transition-colors',
              isActive ? 'font-bold text-[#14251D]' : 'font-medium text-stone-500'
            ]"
          >
            Template
          </span>
        </a>
      </router-link>
    </div>
  </nav>
</template>

<script setup>
</script>

