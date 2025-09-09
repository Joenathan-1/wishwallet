<?php require_once __DIR__ . '/../config/database.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wish Wallet</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
            /* CSS DASAR */
            body, html {
                background-color: #f5f7fa; /* Latar belakang abu-abu sangat muda */
            }

            /* --- STYLING BARU UNTUK SIDEBAR --- */
            .app-sidebar {
                background-color: #5e50b1; /* Warna ungu yang sedikit lebih dalam */
                padding: 2rem 1.5rem;
            }

            .app-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem; 
            font-weight: 600;
            letter-spacing: 1px;
            animation: fadeInUp 0.8s ease-out forwards;
            transition: transform 0.3s ease;
            flex-wrap: nowrap; /* Tambahkan baris ini */
        }

            .menu-list a {
                padding: 0.9rem 1rem; /* Padding lebih besar untuk area klik yang lebih luas */
                margin-bottom: 0.5rem;
                border-radius: 8px;
                transition: background-color 0.3s ease, color 0.3s ease;
            }

            .menu-list a:hover {
                background-color: rgba(255, 255, 255, 0.1); /* Efek hover putih transparan */
            }

            /* Indikator menu aktif yang lebih modern (garis di kiri) */
            .menu-list a.is-active {
                background-color: #796cc4; /* Warna ungu lebih terang */
                color: #fff !important;
                font-weight: 600;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            }
            
            /* Tombol Logout yang terintegrasi */
            .logout-link a {
                padding: 0.9rem 1rem;
                border-radius: 8px;
                transition: background-color 0.3s ease;
            }
            .logout-link a:hover {
                background-color: rgba(255, 82, 82, 0.2); /* Efek hover merah transparan */
                color: #fff !important;
            }

            /* --- STYLING KONTEN UTAMA --- */
            .main-content {
                padding: 2.5rem;
            }
            .main-content .box {
                background-color: #ffffff;
                border-radius: 12px;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .main-content .box:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
            }

            /* --- ANIMASI BARU UNTUK BRAND/LOGO --- */

            /* 1. Mendefinisikan animasi "muncul dari bawah" */
            @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
            }

            /* 2. Menerapkan animasi saat halaman dimuat */
            .app-brand {
                /* ... (style .app-brand yang sudah ada sebelumnya, jangan dihapus) ... */
                animation: fadeInUp 0.8s ease-out forwards; /* Terapkan animasi fadeInUp */
                transition: transform 0.3s ease; /* Transisi untuk efek hover */
            }

            /* 3. Menambahkan efek saat di-hover */
            .app-brand:hover {
                transform: scale(1.05); /* Sedikit memperbesar logo */
                cursor: pointer; /* Mengubah cursor menjadi tangan */
            }

            /* 4. Menerapkan animasi pada ikon saat logo di-hover */
            .app-brand .icon {
                transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55); /* Transisi dengan efek 'bouncy' */
            }

            .app-brand:hover .icon {
                transform: rotate(-15deg) scale(1.1); /* Ikon sedikit berputar dan membesar */
            }

            /* Style untuk partikel koin/uang yang akan dibuat oleh JavaScript */
            .money-particle {
                position: absolute; /* Wajib agar bisa diposisikan di mana saja */
                pointer-events: none; /* Agar tidak bisa diklik */
                z-index: 9999; /* Pastikan selalu di paling depan */
                font-size: 1.5rem; /* Ukuran ikon */
                transition: transform 1s ease-out, opacity 1s ease-out;
            }

            /* Mendefinisikan animasi terbang keluar */
            @keyframes fly-out {
                from {
                    transform: translate(0, 0) scale(1) rotate(0deg);
                    opacity: 1;
                }
                to {
                    /* Nilai --x, --y, dan --r akan diisi oleh JavaScript secara acak */
                    transform: translate(var(--x), var(--y)) scale(0) rotate(var(--r));
                    opacity: 0;
                }
}

        </style>
</head>
<body>