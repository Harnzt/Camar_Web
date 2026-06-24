@extends('main_page.admin-panel.layout')

@section('title', 'Kelola Admin')
@section('page-title', 'Kelola Administrator')

@section('content')
<section class="admin-manager">
    <div class="admin-manager-toolbar">
        <div>
            <span class="panel-kicker">Kontrol akses</span>
            <h2>Daftar Administrator</h2>
            <p>Kelola profil, status akses, password, dan riwayat login administrator CAMAR.</p>
        </div>
        <button type="button" class="btn btn-primary" data-open-modal="create-admin-modal">
            <i class="fas fa-plus"></i> Tambah Admin
        </button>
    </div>

    <form method="GET" action="{{ route('admin.admins.index') }}" class="admin-search">
        <i class="fas fa-magnifying-glass"></i>
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama, email, atau role..."
            aria-label="Cari administrator"
        >
        @if(request('search'))
            <a href="{{ route('admin.admins.index') }}" aria-label="Hapus pencarian">
                <i class="fas fa-xmark"></i>
            </a>
        @endif
        <button type="submit" class="btn btn-light">Cari</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-management-table">
            <thead>
                <tr>
                    <th>Administrator</th>
                    <th>Role</th>
                    <th>Login Terakhir</th>
                    <th>Status</th>
                    <th class="actions-heading">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                    <tr>
                        <td>
                            <div class="admin-identity">
                                <img src="{{ $admin->profile_photo_url }}" alt="{{ $admin->name }}">
                                <div>
                                    <strong>{{ $admin->name }}</strong>
                                    <span>{{ $admin->email }}</span>
                                    @if($admin->is(Auth::user()))
                                        <small>Akun Anda</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-chip {{ $admin->role }}">{{ $admin->role_label }}</span>
                        </td>
                        <td>
                            @if($admin->latestAdminLogin)
                                <strong class="login-date">{{ $admin->latestAdminLogin->logged_in_at->format('d M Y') }}</strong>
                                <small>{{ $admin->latestAdminLogin->logged_in_at->format('H:i') }} · {{ $admin->latestAdminLogin->ip_address ?? '-' }}</small>
                            @else
                                <span class="muted">Belum ada riwayat</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.admins.status', $admin) }}" class="status-toggle-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $admin->status === 'verified' ? 'suspended' : 'verified' }}">
                                <button
                                    type="submit"
                                    class="status-toggle {{ $admin->status === 'verified' ? 'is-active' : '' }}"
                                    title="{{ $admin->status === 'verified' ? 'Nonaktifkan akun' : 'Aktifkan akun' }}"
                                    @disabled($admin->is(Auth::user()))
                                >
                                    <span></span>
                                </button>
                                <span>{{ $admin->status === 'verified' ? 'Aktif' : 'Nonaktif' }}</span>
                            </form>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <button
                                    type="button"
                                    class="icon-action edit"
                                    title="Edit administrator"
                                    data-admin-action="edit"
                                    data-admin-id="{{ $admin->id }}"
                                    data-admin-name="{{ $admin->name }}"
                                    data-admin-email="{{ $admin->email }}"
                                    data-admin-role="{{ $admin->role }}"
                                    data-update-url="{{ route('admin.admins.update', $admin) }}"
                                ><i class="fas fa-pen"></i></button>
                                <button
                                    type="button"
                                    class="icon-action logs"
                                    title="Lihat login log"
                                    data-admin-action="logs"
                                    data-admin-name="{{ $admin->name }}"
                                    data-logs-url="{{ route('admin.admins.login-logs', $admin) }}"
                                ><i class="fas fa-clipboard-list"></i></button>
                                <button
                                    type="button"
                                    class="icon-action password"
                                    title="Ganti password"
                                    data-admin-action="password"
                                    data-admin-name="{{ $admin->name }}"
                                    data-admin-email="{{ $admin->email }}"
                                    data-password-url="{{ route('admin.admins.password', $admin) }}"
                                ><i class="fas fa-key"></i></button>
                                <button
                                    type="button"
                                    class="icon-action delete"
                                    title="Hapus administrator"
                                    data-admin-action="delete"
                                    data-admin-name="{{ $admin->name }}"
                                    data-admin-email="{{ $admin->email }}"
                                    data-delete-url="{{ route('admin.admins.destroy', $admin) }}"
                                    @disabled($admin->is(Auth::user()))
                                ><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="admin-empty">
                                <i class="fas fa-user-shield"></i>
                                <strong>Administrator tidak ditemukan</strong>
                                <span>Coba ubah kata kunci pencarian atau tambahkan akun baru.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $admins->links() }}
</section>

<dialog class="admin-modal" id="create-admin-modal">
    <form method="POST" action="{{ route('admin.admins.store') }}" class="admin-modal-card">
        @csrf
        <div class="admin-modal-header">
            <div><span class="panel-kicker">Akun baru</span><h3>Tambah Administrator</h3></div>
            <button type="button" class="modal-close" data-close-modal><i class="fas fa-xmark"></i></button>
        </div>
        <div class="admin-modal-body">
            <div class="field-group">
                <label for="create-name">Nama lengkap</label>
                <input id="create-name" name="name" required maxlength="100" value="{{ old('name') }}" placeholder="Nama administrator">
            </div>
            <div class="field-group">
                <label for="create-email">Email</label>
                <input id="create-email" type="email" name="email" required value="{{ old('email') }}" placeholder="admin@camar.id">
            </div>
            <div class="field-group">
                <label for="create-role">Role</label>
                <select id="create-role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <div class="field-group">
                <label for="create-password">Password awal</label>
                <div class="password-field">
                    <input id="create-password" type="password" name="password" required minlength="8" autocomplete="new-password">
                    <button type="button" data-toggle-password="create-password"><i class="fas fa-eye"></i></button>
                </div>
                <small>Minimal 8 karakter.</small>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn btn-light" data-close-modal>Batal</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Simpan</button>
        </div>
    </form>
</dialog>

<dialog class="admin-modal" id="edit-admin-modal">
    <form method="POST" class="admin-modal-card" id="edit-admin-form">
        @csrf
        @method('PATCH')
        <div class="admin-modal-header">
            <div><span class="panel-kicker">Ubah akun</span><h3 id="edit-admin-title">Edit Administrator</h3></div>
            <button type="button" class="modal-close" data-close-modal><i class="fas fa-xmark"></i></button>
        </div>
        <div class="admin-modal-body">
            <div class="field-group">
                <label for="edit-name">Nama lengkap</label>
                <input id="edit-name" name="name" required maxlength="100">
            </div>
            <div class="field-group">
                <label for="edit-email">Email</label>
                <input id="edit-email" type="email" name="email" required>
            </div>
            <div class="field-group">
                <label for="edit-role">Role</label>
                <select id="edit-role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn btn-light" data-close-modal>Batal</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Update</button>
        </div>
    </form>
</dialog>

<dialog class="admin-modal" id="password-admin-modal">
    <form method="POST" class="admin-modal-card" id="password-admin-form">
        @csrf
        @method('PATCH')
        <div class="admin-modal-header">
            <div><span class="panel-kicker">Keamanan akun</span><h3 id="password-admin-title">Ganti Password</h3></div>
            <button type="button" class="modal-close" data-close-modal><i class="fas fa-xmark"></i></button>
        </div>
        <div class="admin-modal-body">
            <div class="account-summary">
                <i class="fas fa-shield-halved"></i>
                <div><strong id="password-admin-name"></strong><span id="password-admin-email"></span></div>
            </div>
            <div class="field-group">
                <label for="new-password">Password baru</label>
                <div class="password-field">
                    <input id="new-password" type="password" name="password" required minlength="8" autocomplete="new-password">
                    <button type="button" data-toggle-password="new-password"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="field-group">
                <label for="new-password-confirmation">Konfirmasi password</label>
                <div class="password-field">
                    <input id="new-password-confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                    <button type="button" data-toggle-password="new-password-confirmation"><i class="fas fa-eye"></i></button>
                </div>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn btn-light" data-close-modal>Batal</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Ganti Password</button>
        </div>
    </form>
</dialog>

<dialog class="admin-modal admin-modal-wide" id="login-logs-modal">
    <div class="admin-modal-card">
        <div class="admin-modal-header">
            <div><span class="panel-kicker">Aktivitas keamanan</span><h3 id="login-logs-title">Login Log</h3></div>
            <button type="button" class="modal-close" data-close-modal><i class="fas fa-xmark"></i></button>
        </div>
        <div class="admin-modal-body">
            <div id="login-logs-loading" class="modal-loading-state"><i class="fas fa-circle-notch fa-spin"></i> Memuat riwayat login...</div>
            <div class="login-log-table-wrap" id="login-logs-content" hidden>
                <table class="login-log-table">
                    <thead><tr><th>Waktu Login</th><th>Waktu Logout</th><th>IP Address</th><th>Perangkat</th></tr></thead>
                    <tbody id="login-logs-body"></tbody>
                </table>
                <div class="login-log-pagination" id="login-logs-pagination"></div>
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn btn-light" data-close-modal>Tutup</button>
        </div>
    </div>
</dialog>

<dialog class="admin-modal admin-modal-danger" id="delete-admin-modal">
    <form method="POST" class="admin-modal-card" id="delete-admin-form">
        @csrf
        @method('DELETE')
        <div class="admin-modal-body delete-confirmation">
            <div class="delete-icon"><i class="fas fa-trash"></i></div>
            <h3>Hapus akun administrator?</h3>
            <p>Akun <strong id="delete-admin-name"></strong> (<span id="delete-admin-email"></span>) akan dihapus dan tidak dapat lagi mengakses panel admin.</p>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn btn-light" data-close-modal>Batal</button>
            <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Hapus Akun</button>
        </div>
    </form>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[character]);

    const openModal = (modal) => {
        if (modal && !modal.open) modal.showModal();
    };

    document.querySelectorAll('[data-open-modal]').forEach((button) => {
        button.addEventListener('click', () => openModal(document.getElementById(button.dataset.openModal)));
    });

    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => button.closest('dialog').close());
    });

    document.querySelectorAll('.admin-modal').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) modal.close();
        });
    });

    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.innerHTML = `<i class="fas fa-${visible ? 'eye' : 'eye-slash'}"></i>`;
        });
    });

    document.querySelectorAll('[data-admin-action="edit"]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('edit-admin-form').action = button.dataset.updateUrl;
            document.getElementById('edit-admin-title').textContent = `Edit ${button.dataset.adminName}`;
            document.getElementById('edit-name').value = button.dataset.adminName;
            document.getElementById('edit-email').value = button.dataset.adminEmail;
            document.getElementById('edit-role').value = button.dataset.adminRole;
            openModal(document.getElementById('edit-admin-modal'));
        });
    });

    document.querySelectorAll('[data-admin-action="password"]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('password-admin-form').action = button.dataset.passwordUrl;
            document.getElementById('password-admin-name').textContent = button.dataset.adminName;
            document.getElementById('password-admin-email').textContent = button.dataset.adminEmail;
            document.getElementById('new-password').value = '';
            document.getElementById('new-password-confirmation').value = '';
            openModal(document.getElementById('password-admin-modal'));
        });
    });

    const renderLoginLogs = async (url, name) => {
        const modal = document.getElementById('login-logs-modal');
        const loading = document.getElementById('login-logs-loading');
        const content = document.getElementById('login-logs-content');
        const body = document.getElementById('login-logs-body');
        const pagination = document.getElementById('login-logs-pagination');

        document.getElementById('login-logs-title').textContent = `Login Log · ${name}`;
        loading.hidden = false;
        loading.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Memuat riwayat login...';
        content.hidden = true;
        openModal(modal);

        try {
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('Riwayat login gagal dimuat.');
            const payload = await response.json();
            const logs = payload.logs.data;

            body.innerHTML = logs.length
                ? logs.map((log) => `
                    <tr>
                        <td><strong>${escapeHtml(log.logged_in_at || '-')}</strong></td>
                        <td>${log.logged_out_at ? escapeHtml(log.logged_out_at) : '<span class="session-active">Masih aktif</span>'}</td>
                        <td><code>${escapeHtml(log.ip_address)}</code></td>
                        <td>${escapeHtml(log.device)}</td>
                    </tr>
                `).join('')
                : '<tr><td colspan="4"><div class="admin-empty compact"><span>Belum ada riwayat login.</span></div></td></tr>';

            pagination.innerHTML = '';
            payload.logs.links.forEach((link) => {
                if (!link.url) return;
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `page-link-button ${link.active ? 'active' : ''}`;
                button.textContent = link.label.replace('&laquo;', '‹').replace('&raquo;', '›');
                button.addEventListener('click', () => renderLoginLogs(link.url, name));
                pagination.appendChild(button);
            });

            loading.hidden = true;
            content.hidden = false;
        } catch (error) {
            loading.innerHTML = `<i class="fas fa-triangle-exclamation"></i> ${error.message}`;
        }
    };

    document.querySelectorAll('[data-admin-action="logs"]').forEach((button) => {
        button.addEventListener('click', () => renderLoginLogs(button.dataset.logsUrl, button.dataset.adminName));
    });

    document.querySelectorAll('[data-admin-action="delete"]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('delete-admin-form').action = button.dataset.deleteUrl;
            document.getElementById('delete-admin-name').textContent = button.dataset.adminName;
            document.getElementById('delete-admin-email').textContent = button.dataset.adminEmail;
            openModal(document.getElementById('delete-admin-modal'));
        });
    });
});
</script>
@endsection
