<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 p-4 cetak">
    <div id="print-area">
      <div class="cards-container">
        <div class="card depan">
          <div class="background" :style="{ backgroundImage: `url(${frontBackgroundUrl})` }"></div>

          <div class="top-section">
            <div class="header-text"></div>
          </div>

          <div class="middle-section">
            <div class="photo-wrap">
              <div class="foto" v-if="anggota.foto_url">
                <img :src="anggota.foto_url" />
              </div>
              <div v-else class="foto foto-empty">Foto</div>
            </div>

            <div class="content-wrap">
              <div class="identity">
                <h1 class="member-name">{{ anggota.nama_lengkap }}</h1>
                <p class="member-number">{{ anggota.nomor_anggota }}</p>
              </div>

              <div class="qr-wrap">
                <QrcodeVue :value="publicUrl" :size="66" :margin="1" level="M" />
                <p class="qr-text">Scan untuk cek keaslian anggota</p>
              </div>
            </div>
          </div>
        </div>

        <div class="card belakang">
          <div class="background" :style="{ backgroundImage: `url(${backBackgroundUrl})` }"></div>

          <div class="ttd">
            <div>
              <p>Ketua Umum DPP PPSI </p>
              <div style="height: 12mm;"></div>
              <p class="name">Galih Santika Fadillahkusumah, S.S., M.Si </p>
            </div>
            <div>
              <p>Ketua DPW PPSI <br> Provinsi Kalimatan Tengah</p>
              <div style="height: 8mm;"></div>
              <p class="font-light">Aulia Ibrahim</p>
            </div>
          </div>
        </div>
      </div>

      <button @click="window.print()" class="btn-print">Cetak</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { defineAsyncComponent } from 'vue'

const QrcodeVue = defineAsyncComponent(() => import('qrcode.vue'))

interface Anggota {
  id: string
  nomor_anggota: string
  nama_lengkap: string
  foto_path?: string | null
  foto_url?: string | null
}

const page = usePage<{ anggota: Anggota; publicUrl: string }>()
const anggota = page.props.anggota
const publicUrl = page.props.publicUrl

const frontBackgroundUrl = '/storage/images/front.png'
const backBackgroundUrl = '/storage/images/back.png'
</script>

<style>
.cetak {
  font-family: 'League Gothic', sans-serif;
  font-weight: lighter;
}

#print-area {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.cards-container {
  display: flex;
  gap: 20px;
  align-items: flex-start;
  flex-wrap: wrap;
}

.card {
  width: 85.6mm;
  height: 54mm;
  border: 1px solid #000;
  border-radius: 6px;
  position: relative;
  padding: 2mm;
  font-size: 9pt;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  background-color: white;
}

.card .background {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  z-index: 0;
}

.top-section {
  height: 11mm;
  position: relative;
  z-index: 1;
}

.middle-section {
  position: relative;
  z-index: 1;
  padding: 0 4.5mm 0 6mm;
  display: flex;
  gap: 3.5mm;
  align-items: center;
  height: 33mm;
}

.photo-wrap {
  flex: 0 0 16mm;
}

.card.depan .foto {
  width: 16mm;
  height: 21mm;
  border: 1px solid #333;
  border-radius: 3px;
  overflow: hidden;
  background: #fff;
}

.card.depan .foto img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.foto-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 6pt;
  color: #64748b;
}

.content-wrap {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 2.5mm;
}

.identity {
  min-width: 0;
}

.label {
  margin: 0;
  font-size: 6.3pt;
  letter-spacing: 0.8px;
  text-transform: uppercase;
  color: #14532d;
}

.label.mt {
  margin-top: 1.8mm;
}

.member-name {
  margin: 0.8mm 0 0;
  font-size: 13pt;
  line-height: 0.95;
  font-weight: 700;
  text-transform: uppercase;
  color: #0f172a;
  word-break: break-word;
}

.member-number {
  margin: 0.8mm 0 0;
  font-size: 10.5pt;
  line-height: 1;
  font-weight: 700;
  letter-spacing: 0.4px;
  color: #0b6b31;
}

.qr-wrap {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 1mm;
}

.qr-text {
  margin: 0;
  font-family: Arial, sans-serif;
  font-size: 5.2pt;
  line-height: 1.2;
  color: #0f172a;
}

.card.belakang .ttd {
  margin-left: 2mm;
  margin-right: 0mm;
  display: flex;
  justify-content: space-between;
  margin-top: auto;
  color: #333;
  z-index: 1;
}

.ttd p {
  margin-bottom: 2mm;
  text-align: center;
  letter-spacing: 1px;
  font-size: 6pt;
  font-weight: bold;
}

.ttd .name {
  margin-top: -1mm;
}

.btn-print {
  margin-top: 20px;
  padding: 10px 20px;
  border-radius: 8px;
  background: #333;
  color: white;
  cursor: pointer;
}

@media print {
  .btn-print {
    display: none;
  }

  body {
    background: white;
  }

  .cards-container {
    gap: 5mm;
  }
}
</style>
