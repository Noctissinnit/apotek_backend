@csrf

<div class="card" style="max-width: 48rem;">
    <div class="card-body row g-3">
        <div class="col-12">
            <label class="form-label" for="nama">Nama supplier <span class="text-danger">*</span></label>
            <input type="text" id="nama" name="nama" maxlength="150" required autofocus
                   value="{{ old('nama', $supplier->nama) }}" class="form-control @error('nama') is-invalid @enderror">
            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="nama_kontak">Nama kontak</label>
            <input type="text" id="nama_kontak" name="nama_kontak" maxlength="100"
                   value="{{ old('nama_kontak', $supplier->nama_kontak) }}" class="form-control @error('nama_kontak') is-invalid @enderror">
            @error('nama_kontak') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="telepon">Telepon</label>
            <input type="text" id="telepon" name="telepon" maxlength="30"
                   value="{{ old('telepon', $supplier->telepon) }}" class="form-control @error('telepon') is-invalid @enderror">
            @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="150"
                   value="{{ old('email', $supplier->email) }}" class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <div class="form-check form-switch mb-2">
                <input type="hidden" name="aktif" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="aktif" name="aktif" value="1"
                       @checked(old('aktif', $supplier->aktif))>
                <label class="form-check-label" for="aktif">Supplier aktif</label>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label" for="alamat">Alamat</label>
            <input type="text" id="alamat" name="alamat" maxlength="255"
                   value="{{ old('alamat', $supplier->alamat) }}" class="form-control @error('alamat') is-invalid @enderror">
            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-apotek"><i class="bi bi-save me-1"></i> Simpan</button>
            <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</div>
