# Sumber gambar Mitra DUDI

Manifest ini mencatat input yang digunakan untuk membentuk kartu 1200×800 piksel di `queen-alfalah-core/assets/images/partners/`. Berkas sumber mentah tidak disimpan di Git; unduh kembali dari kanal resmi, beri nama sesuai kolom **Input**, lalu cocokkan SHA-256 sebelum membangun ulang. URL gambar CDN Instagram sengaja tidak dicatat karena bersifat sementara—ambil foto profil yang sedang tampil dari akun resmi dan lakukan verifikasi visual bila asetnya telah berubah.

| Input | SHA-256 input yang digunakan | Kanal resmi / cara memperoleh |
|---|---|---|
| `jtv-kediri.png` | `75420ABC82BFCB875E161131D87B8FB5488571E12DB0AB673AD08452CC39807F` | [Logo pada halaman biro JTV Kediri](https://portaljtv.com/images/biro/new/jtv-kediri.png) |
| `ourweb.webp` | `28256F686D5DB51F1A16D3DDDB8F0100F5B0D2616C3E64D70DE1F2154B932B13` | [Logo pada situs OurWeb.id](https://ourweb.id/wp-content/uploads/2023/12/Logo-OurWeb-Jasa-Website.webp) |
| `terra-computer.jpg` | `446649FFB8F869FF7362B3C36810E092C2D8DBF26123CFF7E7ECC61892961B8A` | Foto profil [Instagram Terra Computer System Kediri](https://www.instagram.com/terracomputersystemkediri/); cocokkan identitas dengan [registri Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/K5663463) |
| `cv-nusantara-media-mandiri.png` | `761E055102CF14F7240E1D12DBEE5D3D6D112C8B0C08163377DC0133ADD7FB95` | [Ikon situs CV Nusantara Media Mandiri](https://cv-nmm.com/wp-content/uploads/2020/07/cropped-icon1-192x192.png) |
| `bead-group.png` | `DA65224A532C73DD1BF65440B746B1CA00AFB740633520502CEE4FCA0DFFD935` | Logo yang tampil pada halaman resmi [BeAD IT Consultant](https://beadgrup.com/tentang-kami/) |
| `candradimuka-digital.png` | `335ED64C51AB8C4A1D9803CAC498FC988488F7352FDCBF3344F07DA899F53E66` | Logo header pada [situs Candradimuka Digital](https://candradimukadigital.com/) |
| `lp3i.svg` | `562C10087E644FFF588A15E9A76C9DE301E7286427E593809323E6835DE97110` | Logo SVG yang tampil pada halaman resmi [LP3I College Kediri](https://www.lp3i.ac.id/campus/lp3i-college-kediri/) |
| `rs-bhayangkara-kediri.png` | `3A871C12C3DF6FA5770AF2208314ABC2BC399027029C69F348EAD81ACC67FF7F` | Logo yang tampil pada [situs RS Bhayangkara Tk. II Kediri](https://rsbhayangkarakediri.com/); identitas rumah sakit juga tersedia di [Pusdokkes Polri](https://pusdokkes.polri.go.id/Facility/2/rumah-sakit-bhayangkara-tk-ii-kediri) |
| `puskesmas-mojo.jpg` | `D880EF4FAF5E5F57D8A3F546013DCFF4306EE518E4090AA1F431D3A445E83D9D` | Foto profil [Instagram UPTD Puskesmas Mojo](https://www.instagram.com/uptd_puskesmas_mojo/); cocokkan dengan [situs Pemerintah Kabupaten Kediri](https://puskesmasmojo.kedirikab.go.id/) |
| `rsu-arga-husada.png` | `5223AB8B9D4B54F3174A0C9178450881D66105D1308161B0FF19EC7FBA2D0F84` | Logo yang tampil pada [situs RSU Arga Husada](https://rsargahusada.com/) |

Lima kartu lain—FA Cinema, PT Alfiz, Beneficia Tech, PT JWB, dan Asterix Comp—dibuat langsung oleh skrip sebagai identitas sementara. Kartu tersebut tidak memakai atau mengaku sebagai logo resmi dan harus diganti setelah sekolah memperoleh aset yang terkonfirmasi.

## Membangun ulang

Gunakan Node.js sesuai `.nvmrc`, kemudian:

```powershell
Set-Location tools
npm ci
npm run build:partners -- "C:\lokasi\sumber-logo" "..\queen-alfalah-core\assets\images\partners"
```

Periksa hasil secara visual dan jalankan `tests/partner-logo-contract.php` sebelum membuat paket plugin.
