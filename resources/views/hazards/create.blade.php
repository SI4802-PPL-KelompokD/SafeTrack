@extends('layouts.safetrack')

@section('title', 'Buat Laporan Bahaya')

@section('content')

<div class="page-head">
    <div>
        <h1>Buat Laporan Bahaya</h1>
        <p>Laporkan temuan bahaya yang ditemukan di lingkungan kerja.</p>
    </div>

    <a href="{{ route('hazards.index') }}" class="btn">
        Kembali
    </a>
</div>

<div class="panel" style="padding: 24px;">

    <form
        action="{{ route('hazards.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        {{-- UUID untuk mencegah laporan ganda --}}
        <input
            type="hidden"
            name="client_request_id"
            value="{{ old('client_request_id', (string) \Illuminate\Support\Str::uuid()) }}"
        >

        <div class="form-grid">

            {{-- Judul --}}
            <label class="full">
                Judul Laporan *
                <input
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    placeholder="Masukkan judul laporan"
                    required
                >

                @error('title')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            {{-- Lokasi --}}
            <label class="full">
                Lokasi Kejadian *
                <input
                    type="text"
                    name="location"
                    value="{{ old('location') }}"
                    placeholder="Contoh: Area Produksi"
                    required
                >

                @error('location')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            {{-- Deskripsi --}}
            <label class="full">
                Deskripsi Kejadian *
                <textarea
                    name="description"
                    rows="4"
                    placeholder="Jelaskan bahaya yang ditemukan"
                    required
                >{{ old('description') }}</textarea>

                @error('description')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            {{-- Kategori Temuan --}}
            <label>
                Kategori Temuan *
                <select name="category_id" required>
                    <option value="">Pilih kategori</option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected(old('category_id') == $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                @error('category_id')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            {{-- Departemen Pelapor --}}
            <label>
                Departemen Pelapor *
                <select name="reporter_department_id" required>
                    <option value="">Pilih departemen</option>

                    @foreach ($departments as $department)
                        <option
                            value="{{ $department->id }}"
                            @selected(old('reporter_department_id') == $department->id)
                        >
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>

                @error('reporter_department_id')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            {{-- Departemen Perbaikan --}}
            <label>
                Departemen Perbaikan *
                <select name="repair_department_id" required>
                    <option value="">Pilih departemen</option>

                    @foreach ($departments as $department)
                        <option
                            value="{{ $department->id }}"
                            @selected(old('repair_department_id') == $department->id)
                        >
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>

                @error('repair_department_id')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            {{-- Tindakan --}}
            <label class="full">
                Tindakan Sementara *
                <textarea
                    name="immediate_action"
                    rows="4"
                    placeholder="Tuliskan tindakan yang sudah dilakukan"
                    required
                >{{ old('immediate_action') }}</textarea>

                @error('immediate_action')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

            {{-- Foto Bukti --}}
            <label class="full">
                Foto Bukti *
                <input
                    type="file"
                    name="photos[]"
                    accept="image/*"
                    required
                >

                <small>
                    Unggah satu foto bukti kejadian.
                </small>

                @error('photos')
                    <small class="field-error">{{ $message }}</small>
                @enderror

                @error('photos.0')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </label>

        </div>

        {{-- Tombol --}}
        <div class="form-actions">
            <a
                href="{{ route('hazards.index') }}"
                class="btn"
            >
                Batal
            </a>

            <button
                type="submit"
                class="btn primary"
            >
                Kirim Laporan
            </button>
        </div>

    </form>

</div>

@endsection