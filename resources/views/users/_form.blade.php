@csrf

<div class="card" style="max-width: 40rem;">
    <div class="card-body row g-3">
        <div class="col-12">
            <label class="form-label" for="name">Nama <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" maxlength="100" required autofocus
                   value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-12">
            <label class="form-label" for="email">Email <span class="text-danger">*</span></label>
            <input type="email" id="email" name="email" maxlength="150" required autocomplete="off"
                   value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
                <option value="kasir" @selected(old('role', $user->role) === 'kasir')>Kasir</option>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
            </select>
            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">Kasir: jualan & lihat obat. Admin: semua fitur.</div>
        </div>
        <div class="col-md-6 d-flex align-items-center">
            <div class="form-check form-switch">
                <input type="hidden" name="aktif" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="aktif" name="aktif" value="1"
                       @checked(old('aktif', $user->aktif))>
                <label class="form-check-label" for="aktif">Akun aktif (boleh login)</label>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label" for="password">
                Password @unless($user->exists)<span class="text-danger">*</span>@endunless
            </label>
            <input type="password" id="password" name="password" autocomplete="new-password"
                   class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">
                Minimal 8 karakter, berisi huruf dan angka.
                @if($user->exists) Kosongkan kalau tidak ingin mengganti password. @endif
            </div>
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-apotek"><i class="bi bi-save me-1"></i> Simpan</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</div>
