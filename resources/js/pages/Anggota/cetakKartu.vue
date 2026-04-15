<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 p-4 cetak">
    <div id="print-area">
      <div class="cards-container">
        <!-- Kartu Depan -->
        <div class="card depan">
          <div class="background" :style="{ backgroundImage: `url(${frontBackgroundUrl})` }"></div>

          <div class="top-section">
            <!-- <img :src="logoUrl" class="logo" /> -->
            <div class="header-text">
              <!-- <h1 class="text-lg ">Dewan Pimpinan Pusat</h1>
              <h2 class="font-bold">Persatuan Pencak Silat Indonesia</h2> -->
            </div>
          </div>

          <div class="middle-section">

           
            <div>
                <div class="foto" v-if="anggota.foto">
                   <img :src="anggota.foto ? `/storage/${anggota.foto}` : '/storage/images/default.png'"  />
                </div>
            </div>
            
            <div class="qr-section">
           
                    <table>
                    <tr><td>No Anggota</td><td>: {{ anggota.nik }}</td></tr>
                    <tr><td>Nama</td><td>: {{ anggota.nama_lengkap }}</td></tr>
        
                    <tr><td>DPD Kota/Kab</td><td>: {{ anggota.kelurahan?.kecamatan?.kota_kab?.nama ?? '-' }}</td></tr>

                    </table>
             
              
            </div>
           <diV class="space">
            <QrcodeVue :value="`http://localhost:8000/anggota/${anggota.id}/print`" :size="80" />
           </diV>
          </div>
        </div>

        <!-- Kartu Belakang -->
        <div class="card belakang">
          <div class="background" :style="{ backgroundImage: `url(${backBackgroundUrl})` }"></div>

          <!-- <h3 class="font-bold mb-4">CIRI ANGGOTA PPSI</h3>
          <p class="text-black">Perkataan dan perbuatan selalu sesuai dengan:</p>
          <ul>
            <li>Trilogi PPSI: Budi - Bakti - Sakti </li>
            <li>Tekad PPSI: Panceg na Galur, Akur Jeung Dulur, Ngajaga Lembur</li>
            <li>Papagon PPSI: Sholat - Silat - Siliwangi</li>
          </ul> -->

          <div class="ttd">
            <div>
                
                <p>Ketua Umum DPP PPSI </p>
                <div style="height: 12mm;">

                </div>
                <p class="name">Galih Santika Fadillahkusumah, S.S., M.Si </p>
            </div>
            <div>
                <p>Ketua DPW PPSI <br> Provinsi Kalimatan Tengah</p>
                <div style="height: 8mm;">
                     
                </div>
                 <p class="font-light">Aulia Ibrahim</p>
            </div>
            

          </div>
        </div>
      </div>

      <button @click="window.print()" class="btn-print">🖨 Cetak</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { defineAsyncComponent } from 'vue'

const QrcodeVue = defineAsyncComponent(() => import('qrcode.vue'))

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
}

const page = usePage<{ anggota: Anggota }>()
const anggota = page.props.anggota

const logoUrl = '/storage/images/logo.png'
const frontBackgroundUrl = '/storage/images/front.png'
const backBackgroundUrl = '/storage/images/back.png'
</script>

<style>
.cetak{
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
    height: 20mm;
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
  line-height: 1;
  letter-spacing: 0.5;
  font-size: 7pt;
  text-transform: uppercase;
}
.header-text h2{
    font-size: 9.2pt;
}
.header-text h1{
    font-weight: bolder;
}

/* Middle section */
.middle-section {
  padding: 0 5mm 0 8mm;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 3mm;
  z-index: 1;
}
.middle-section .space{
 
     margin-top: -2.5mm;
  margin-left: -12mm;
}

td{
    min-width: 10mm;
}

.card.depan .foto {
  width: 15mm;
  height: 20mm;
  margin-top: -4mm;
  border: 1px solid #333;
  overflow: hidden;
}

.card.depan .foto img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* QR Code section */
.qr-section {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 2mm;
  height: 30mm;
  margin-top: -12mm;
  margin-left: -14mm;
  z-index: 1;
}
.qr-section table{
    font-size: 6pt;
    line-height: 1.5;
}

.side-icon {
  width: 10mm;
  height: auto;
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

.card.belakang .ttd {
    margin-left: 2mm;
    margin-right: 0mm;
    display: flex;
    justify-content: space-between;
    margin-top: auto;
    color: #333;
    z-index: 1;
}
.ttd p{
    margin-bottom: 2mm;
    text-align: center;
    
    letter-spacing: 1px;

}
.ttd .name{
    margin-top: -1mm;
    
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