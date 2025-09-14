@extends('layouts.app')
@section('content')
    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <section class="my-account container">
            <h2 class="page-title">Alamat</h2>
            <div class="row">
                <div class="col-lg-3">
                    @include('user.account-nav')
                </div>
                <div class="col-lg-9">
                    <div class="page-content my-account__address">
                        <div class="row">
                            <div class="col-12 text-right mb-3">
                                <a href="{{ route('user.address.index') }}" class="btn btn-sm btn-danger">Kembali</a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-5">
                                    <div class="card-header">
                                        <h5>Tambah Alamat Baru</h5>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('user.address.store') }}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-floating my-3">
                                                        <input type="text" class="form-control" name="name"
                                                            value="{{ old('name') }}">
                                                        <label for="name">Nama Lengkap *</label>
                                                        @error('name')
                                                            <span class="text-red">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating my-3">
                                                        <input type="text" class="form-control" name="phone"
                                                            value="{{ old('phone') }}">
                                                        <label for="phone">Nomor Handphone *</label>
                                                        @error('phone')
                                                            <span class="text-red">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-floating my-3">
                                                        <input type="text" class="form-control" name="zip"
                                                            value="{{ old('zip') }}">
                                                        <label for="zip">Kode Pos *</label>
                                                        @error('zip')
                                                            <span class="text-red">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-floating mt-3 mb-3">
                                                        <input type="text" class="form-control" name="state"
                                                            value="{{ old('state') }}">
                                                        <label for="state">Kecamatan *</label>
                                                        @error('state')
                                                            <span class="text-red">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-floating my-3">
                                                        <input type="text" class="form-control" name="city"
                                                            value="{{ old('city') }}">
                                                        <label for="city">Kota *</label>
                                                        @error('city')
                                                            <span class="text-red">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating my-3">
                                                        <input type="text" class="form-control" name="address"
                                                            value="{{ old('address') }}">
                                                        <label for="address">Nomor Rumah, Desa *</label>
                                                        @error('address')
                                                            <span class="text-red">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating my-3">
                                                        <input type="text" class="form-control" name="locality"
                                                            value="{{ old('locality') }}">
                                                        <label for="locality">Nama Jalan *</label>
                                                        @error('locality')
                                                            <span class="text-red">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-floating my-3">
                                                        <input type="text" class="form-control" name="landmark"
                                                            value="{{ old('landmark') }}">
                                                        <label for="landmark">Petunjuk</label>
                                                        @error('landmark')
                                                            <span class="text-red">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-floating my-3">
                                                    <select class="form-select py-0 px-2" name="type">
                                                        <option value="">Pilih Tipe Alamat</option>
                                                        <option value="Rumah"
                                                            {{ old('type') == 'Rumah' ? 'selected' : '' }}>Rumah</option>
                                                        <option value="Kantor"
                                                            {{ old('type') == 'Kantor' ? 'selected' : '' }}>Kantor</option>
                                                        <option value="Lainnya"
                                                            {{ old('type') == 'Lainnya' ? 'selected' : '' }}>Lainnya
                                                        </option>
                                                    </select>
                                                    <label for="type">Tipe Alamat *</label>
                                                    @error('type')
                                                        <span class="text-red">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="isdefault"
                                                            name="isdefault" value="1"
                                                            {{ old('isdefault') ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="isdefault">
                                                            Jadikan Alamat Utama
                                                        </label>
                                                        @error('isdefault')
                                                            <span class="text-red">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-12 text-right">
                                                    <button type="submit" class="btn btn-success">Kirim</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
