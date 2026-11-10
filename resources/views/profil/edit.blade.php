@extends('layouts.dashboard')

@section('title', 'Profil Pengguna - NutriMate')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profil.css') }}">
@endpush

@section('content')
<div class="profile-page">
    <div class="profile-container">

        <div class="profile-header">
            <div class="profile-header-icon">
                <i class="fas fa-user-edit"></i>
            </div>
            <div>
                <span class="profile-eyebrow">PENGATURAN AKUN</span>
                <h1>Profil Pengguna</h1>
                <p>Kenali kebutuhanmu, temukan rekomendasi makanan yang sesuai.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="profile-alert profile-alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="profile-alert profile-alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Periksa kembali data kamu.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form id="profileForm"
              action="{{ route('profil.store') }}"
              method="POST">
            @csrf

            <section class="profile-card">
                <div class="profile-card-heading">
                    <div class="section-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h2>Informasi Pribadi</h2>
                        <p>Informasi dasar tentang dirimu.</p>
                    </div>
                </div>

                <div class="profile-form-grid">
                    <div class="profile-field">
                        <label for="usia">Usia <span>*</span></label>
                        <div class="profile-input-wrap">
                            <i class="fas fa-calendar-alt"></i>
                            <input type="number"
                                   id="usia"
                                   name="usia"
                                   min="1"
                                   max="120"
                                   value="{{ old('usia', $profil->usia ?? '') }}"
                                   placeholder="Masukkan usia"
                                   required>
                            <span class="input-unit">tahun</span>
                        </div>
                        <small>Masukkan usiamu saat ini.</small>
                    </div>

                    <div class="profile-field">
                        <label>Jenis Kelamin <span>*</span></label>

                        <div class="gender-options">
                            <label class="gender-option">
                                <input type="radio"
                                       name="jenis_kelamin"
                                       value="Laki-laki"
                                       {{ old('jenis_kelamin', $profil->jenis_kelamin ?? '') == 'Laki-laki' ? 'checked' : '' }}
                                       required>
                                <span class="gender-option-content">
                                    <i class="fas fa-mars"></i>
                                    <span>Laki-laki</span>
                                </span>
                            </label>

                            <label class="gender-option">
                                <input type="radio"
                                       name="jenis_kelamin"
                                       value="Perempuan"
                                       {{ old('jenis_kelamin', $profil->jenis_kelamin ?? '') == 'Perempuan' ? 'checked' : '' }}>
                                <span class="gender-option-content">
                                    <i class="fas fa-venus"></i>
                                    <span>Perempuan</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </section>

            <section class="profile-card">
                <div class="profile-card-heading">
                    <div class="section-icon">
                        <i class="fas fa-weight"></i>
                    </div>
                    <div>
                        <h2>Informasi Tubuh</h2>
                        <p>Data pengukuran tubuh untuk menghitung kebutuhan energi.</p>
                    </div>
                </div>

                <div class="profile-form-grid">
                    <div class="profile-field">
                        <label for="berat_badan">Berat Badan <span>*</span></label>
                        <div class="profile-input-wrap">
                            <i class="fas fa-weight"></i>
                            <input type="number"
                                   id="berat_badan"
                                   name="berat_badan"
                                   min="1"
                                   max="500"
                                   step="0.1"
                                   value="{{ old('berat_badan', $profil->berat_badan ?? '') }}"
                                   placeholder="Contoh: 55"
                                   required>
                            <span class="input-unit">kg</span>
                        </div>
                        <small>Gunakan berat badan terbaru.</small>
                    </div>

                    <div class="profile-field">
                        <label for="tinggi_badan">Tinggi Badan <span>*</span></label>
                        <div class="profile-input-wrap">
                            <i class="fas fa-ruler-vertical"></i>
                            <input type="number"
                                   id="tinggi_badan"
                                   name="tinggi_badan"
                                   min="1"
                                   max="300"
                                   step="0.1"
                                   value="{{ old('tinggi_badan', $profil->tinggi_badan ?? '') }}"
                                   placeholder="Contoh: 160"
                                   required>
                            <span class="input-unit">cm</span>
                        </div>
                        <small>Masukkan tinggi badan dalam sentimeter.</small>
                    </div>
                </div>
            </section>

            <section class="profile-card">
                <div class="profile-card-heading">
                    <div class="section-icon">
                        <i class="fas fa-running"></i>
                    </div>
                    <div>
                        <h2>Aktivitas Fisik</h2>
                        <p>Pilih tingkat aktivitas yang paling menggambarkan keseharianmu.</p>
                    </div>
                </div>

                @php
                    $aktivitasTerpilih = old(
                        'aktivitas_fisik',
                        $profil->aktivitas_fisik ?? ''
                    );

                    // Data lama (5 tingkat) dipetakan ke 3 tingkat
                    // sesuai Tabel 2.3 Faktor Tingkat Aktivitas.
                    $petaAktivitasLama = [
                        'Sedentary'    => 'Ringan',
                        'Sangat Berat' => 'Berat',
                    ];

                    $aktivitasTerpilih = $petaAktivitasLama[$aktivitasTerpilih]
                        ?? $aktivitasTerpilih;

                    $daftarAktivitas = [
                        'Ringan' => [
                            'icon' => 'fa-walking',
                            'judul' => 'Ringan (Sedentary)',
                            'deskripsi' => 'Lebih banyak duduk, aktivitas ringan, atau olahraga 1–3 hari per minggu.'
                        ],
                        'Sedang' => [
                            'icon' => 'fa-biking',
                            'judul' => 'Sedang',
                            'deskripsi' => 'Aktivitas cukup aktif atau olahraga 3–5 hari per minggu.'
                        ],
                        'Berat' => [
                            'icon' => 'fa-dumbbell',
                            'judul' => 'Berat',
                            'deskripsi' => 'Aktivitas berat, olahraga intensif secara rutin, atau pekerjaan fisik berat.'
                        ],
                    ];
                @endphp

                <div class="activity-options">
                    @foreach ($daftarAktivitas as $nilai => $aktivitas)
                        <label class="activity-option">
                            <input type="radio"
                                   name="aktivitas_fisik"
                                   value="{{ $nilai }}"
                                   {{ $aktivitasTerpilih == $nilai ? 'checked' : '' }}
                                   required>

                            <span class="activity-option-content">
                                <span class="activity-icon">
                                    <i class="fas {{ $aktivitas['icon'] }}"></i>
                                </span>

                                <span class="activity-description">
                                    <strong>{{ $aktivitas['judul'] }}</strong>
                                    <small>{{ $aktivitas['deskripsi'] }}</small>
                                </span>

                                <span class="activity-check">
                                    <i class="fas fa-check"></i>
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <div class="profile-submit-area">
                <div class="profile-submit-note">
                    <i class="fas fa-shield-alt"></i>
                    <span>Pastikan data yang kamu masukkan sudah benar.</span>
                </div>

                <button type="submit" id="submitProfile" class="profile-submit-button">
                    <i class="fas fa-save"></i>
                    <span>Simpan Profil</span>
                    <i class="fas fa-arrow-right button-arrow"></i>
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/profil.js') }}" defer></script>
@endpush