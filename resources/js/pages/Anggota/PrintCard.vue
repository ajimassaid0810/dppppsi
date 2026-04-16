<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 p-4">
    <div id="print-area">
      <div class="cards-container">
        <div class="card depan">
          <div class="background" :style="{ backgroundImage: `url(${frontBackgroundUrl})` }"></div>

          <div class="top-section">
            <img :src="logoUrl" class="logo" />
            <div class="header-text">
              <h1 class="text-lg font-bold">Dewan Pimpinan Pusat</h1>
              <h1 class="font-bold">Peserta Pencak Silat Indonesia</h1>
            </div>
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
                <QrcodeVue :value="publicUrl" :size="66" :margin="1" level="M" render-as="svg" />
                <p class="qr-text">Scan untuk cek keaslian anggota</p>
              </div>
            </div>
          </div>
        </div>

        <div class="card belakang">
          <div class="background" :style="{ backgroundImage: `url(${backBackgroundUrl})` }"></div>

          <h3 class="font-bold mb-2">CIRI ANGGOTA PPSI</h3>
          <p class="text-black">Perkataan dan perbuatan selalu sesuai dengan:</p>
          <ul>
            <li>TRILOGI PPSI: Budi - Bakti - Sakti</li>
            <li>TEKAD PPSI: Akur jeung Dulur, Pacegâ€™na Galur, Njaga Lembur</li>
            <li>PAPAGON PPSI: Sholat - Silat - Siliwangi</li>
          </ul>

         

          <div class="ttd">
            <div class="ttd-item">
              <p>Ketua Umum DPP PPSI </p>
                <img class="img-ttd" src="https://dimensy.id/uploads/posts/attachments/mceclip017.png">
              <p class="name">Galih Santika Fadillahkusumah, S.S., M.Si </p>
            </div>
            <div class="ttd-item ttd-kiri">
              <p>Ketua DPW PPSI <br> Provinsi {{ dpwProvinsiName }}</p>
              <img v-if="dpwTtdUrl" class="img-ttd" :src="dpwTtdUrl" alt="TTD Ketua DPW" />
              <div v-else style="height: 8mm;"></div>
              <p class="font-light">{{ dpwKetuaName }}</p>
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
import QrcodeVue from 'qrcode.vue'

interface Anggota {
  id: string
  nomor_anggota: string
  nama_lengkap: string
  foto_path?: string | null
  foto_url?: string | null
  kota_kab?: {
    provinsi?: {
      nama?: string | null
      nama_ketua_dpw?: string | null
      url_ttd_ketua?: string | null
      ttd_public_url?: string | null
    } | null
  } | null
}

const page = usePage<{ anggota: Anggota; publicUrl: string }>()
const anggota = page.props.anggota
const publicUrl = page.props.publicUrl
const dpwProvinsiName = anggota.kota_kab?.provinsi?.nama ?? '-'
const dpwKetuaName = anggota.kota_kab?.provinsi?.nama_ketua_dpw ?? '-'
const dpwTtdUrl =
  anggota.kota_kab?.provinsi?.ttd_public_url ??
  anggota.kota_kab?.provinsi?.url_ttd_ketua ??
  null

const logoUrl = '/storage/images/logo.png'
const frontBackgroundUrl = '/storage/images/front.png'
const backBackgroundUrl = '/storage/images/back.png'
</script>

<style>
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
  display: flex;
  align-items: center;
  gap: 4mm;
  z-index: 1;
}

.card .logo {
  width: 10mm;
  height: auto;
}

.header-text {
  line-height: 1.1;
  font-size: 7pt;
  z-index: 1;
}

.middle-section {
  position: relative;
  z-index: 1;
  padding: 3mm 4.5mm 0 5mm;
  display: flex;
  gap: 3.5mm;
  align-items: center;
  height: 31mm;
}

.photo-wrap {
  flex: 0 0;
}

.card.depan .foto {
  width: 24mm;
  height: 29mm;
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
  margin: 2mm 0 0;
  font-family: 'League Gothic', sans-serif;
  font-size: 13pt;
  line-height: 0.95;
  font-weight: 700;
  text-transform: uppercase;
  color: #0f172a;
  word-break: break-word;
}

.member-number {
  margin: 0.8mm 0 0;
  font-family: 'League Gothic', sans-serif;
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
  font-size: 5.2pt;
  line-height: 1.2;
  color: #0f172a;
}

.card.belakang h3 {
  margin-top: 3mm;
  margin-bottom:3mm;
  z-index: 1;
}

.card.belakang p {
  font-size: 6pt;
  font-weight: bold;
  z-index: 1;
}

.card.belakang ul {
  padding-left: 15px;
  line-height: 1.5;
  font-size: 6pt;
  z-index: 1;
}

.barcode-belakang {
  text-align: center;
  z-index: 1;
}

.barcode-belakang svg {
  width: 100%;
  height: 40px;
}

.card.belakang .ttd {
  margin-left: 2mm;
  margin-right: 0;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 2mm;
  margin-top: auto;
  color: #333;
  z-index: 1;
}

.card.belakang .ttd-item {
  flex: 3/5 2/5 0;
  min-width: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  text-align: center;
}

.card.belakang .ttd-item p {
  margin: 0;
  text-align: center;
}

.btn-print {
  margin-top: 20px;
  padding: 10px 20px;
  border-radius: 8px;
  background: #333;
  color: white;
  cursor: pointer;
}

.img-ttd{
  display: block;
  max-width:20mm;
  max-height: 10mm;
  margin: 0 auto;
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
