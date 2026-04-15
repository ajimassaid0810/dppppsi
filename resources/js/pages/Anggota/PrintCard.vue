<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 p-4">
    <div id="print-area">
      <div class="cards-container">
        <!-- Kartu Depan -->
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
            <div class="foto" v-if="anggota.foto">
              <img :src="anggota.foto ? `/storage/${anggota.foto}` : '/storage/images/default.png'" />
            </div>
            <div class="data">
              <table>
                <tr><td>Nama</td><td>: {{ anggota.nama_lengkap }}</td></tr>
                <tr><td>NIK</td><td>: {{ anggota.nik }}</td></tr>
                <tr><td>Alamat</td><td>: {{ anggota.alamat ?? '-' }}</td></tr>
                <tr><td>Kelurahan</td><td>: {{ anggota.kelurahan?.nama ?? '-' }}</td></tr>
                <tr><td>Kecamatan</td><td>: {{ anggota.kelurahan?.kecamatan?.nama ?? '-' }}</td></tr>
                <tr><td>DPD Kota/Kab</td><td>: {{ anggota.kelurahan?.kecamatan?.kota_kab?.nama ?? '-' }}</td></tr>
                <tr><td>DPW Provinsi</td><td>: {{ anggota.kelurahan?.kecamatan?.kota_kab?.provinsi?.nama ?? '-' }}</td></tr>
                <tr><td>Golongan Darah</td><td>: {{ anggota.golongan_darah ?? '-' }}</td></tr>
                <tr><td>Masa Berlaku</td><td>: {{ '5 Tahun' }}</td></tr>
              </table>
            </div>
          </div>67
        </div>

        <!-- Kartu Belakang -->
        <div class="card belakang">
          <div class="background" :style="{ backgroundImage: `url(${backBackgroundUrl})` }"></div>

          <h3 class="font-bold mb-4">CIRI ANGGOTA PPSI</h3>
          <p class="text-black">Perkataan dan perbuatan selalu sesuai dengan:</p>
          <ul>
            <li>TRILOGI PPSI: Budi - Bakti - Sakti</li>
            <li>TEKAD PPSI: Akur jeung Dulur, Paceg’na Galur, Njaga Lembur</li>
            <li>PAPAGON PPSI: Sholat - Silat - Siliwangi</li>
          </ul>

          <div class="barcode-belakang" v-if="anggota.barcode_data">
            <svg id="barcode"></svg>
          </div>

          <div class="ttd">
            <p>Karawang, 01 Oktober 2025</p>
            <p>Ketua Umum DPP PPSI</p>
            <p>(Tanda Tangan Digital)</p>
          </div>
        </div>
      </div>

      <button @click="goToPrintPage()" class="btn-print">🖨 Cetak</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import JsBarcode from 'jsbarcode'

interface Cabang {
  id: string
  nama_cabang: string
}

interface Anggota {
  id: string
  nama_lengkap: string
  nik: string
  tanggal_lahir: string
  alamat: string | null
  telepon: string | null
  perguruan: string | null
  golongan_darah: string | null
  masa_berlaku: string | null
  tanggal_gabung: string
  cabang?: Cabang
  kelurahan?: {
    id: string
    nama: string
    kecamatan?: {
      nama: string
      kota_kab?: {
        nama: string
        provinsi?: {
          nama: string
        }
      }
    }
  } | null
  foto?: string | null
  barcode_data?: string
}

const page = usePage<{ anggota: Anggota }>()
const anggota = page.props.anggota

const logoUrl = '/storage/images/logo.png'
const frontBackgroundUrl = '/storage/images/front.png'
const backBackgroundUrl = '/storage/images/back.png'

onMounted(() => {
  if (anggota.barcode_data) {
    JsBarcode('#barcode', anggota.barcode_data, {
      format: 'CODE128',
      lineColor: '#000',
      width: 2,
      height: 40,
      displayValue: false
    })
  }
})

function goToPrintPage() {

    window.location.href = `/anggota/${anggota.id}/cetak`
  
}
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

/* Kartu */
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

/* Background gambar */
.card .background {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  z-index: 0;
}

/* Top section */
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
}

/* Middle section */
.middle-section {
  display: flex;
  gap: 4mm;
  margin-top: 3mm;
  z-index: 1;
}

.card.depan .foto {
  width: 25mm;
  height: 30mm;
  border: 1px solid #333;
  overflow: hidden;
}

.card.depan .foto img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card .data {
  font-size: 6pt;
  line-height: 1.1;
  z-index: 1;
}

.card .data table {
  width: 100%;
  border-collapse: collapse;
}

.card .data td {
  padding: 1px 4px;
  vertical-align: top;
}

/* Kartu belakang */
.card.belakang h3 {
margin-top: 5mm;
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
  margin-top: auto;
  margin-bottom: 5mm;
  text-align: right;
  font-size: 6pt;
  z-index: 1;
}

/* Tombol cetak */
.btn-print {
  margin-top: 20px;
  padding: 10px 20px;
  border-radius: 8px;
  background: #333;
  color: white;
  cursor: pointer;
}

/* Print styles */
@media print {
  .btn-print { display: none; }
  body { background: white; }
  .cards-container { gap: 5mm; }
}
</style>