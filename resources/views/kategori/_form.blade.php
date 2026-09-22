@csrf

<div class="card" style="max-width: 40rem;">
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label" for="nama">Nama kategori <span class="text-danger">*</span></label>
            <input type="text" id="nama" name="nama" maxlength="100" required autofocus
                   value="{{ old('nama', $kategori->nama) }}" class="form-control @error('nama') is-invalid @enderror">
            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="1000"
                      class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi', $kategori->deskripsi) }}</textarea>
            @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-apotek"><i class="bi bi-save me-1"></i> Simpan</button>
            <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</div>
