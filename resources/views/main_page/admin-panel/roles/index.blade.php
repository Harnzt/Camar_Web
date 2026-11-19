@extends('main_page.admin-panel.layout')

@section('title', 'Role & Permission')
@section('page-title', 'Role & Permission')

@section('content')
@if($superAdminRole)
    <div class="alert alert-info">
        <strong>{{ $superAdminRole->name }}</strong> selalu memiliki semua permission dan tidak dapat dibatasi.
        Atur izin untuk Admin dan Auditor di bawah ini.
    </div>
@endif

<div class="permission-grid">
    @foreach($roles as $role)
        <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="panel role-card">
            @csrf @method('PUT')
            <div class="panel-heading">
                <h2>{{ $role->name }}</h2>
                <span class="muted">{{ $role->permissions->count() }} dari {{ $permissions->count() }} izin aktif</span>
            </div>
            @if($role->description)
                <p class="muted role-description">{{ $role->description }}</p>
            @endif
            <fieldset class="permission-list">
                <legend class="visually-hidden">Permission untuk {{ $role->name }}</legend>
                @foreach($permissions as $permission)
                    <label for="permission-{{ $role->id }}-{{ $permission->id }}">
                        <input
                            type="checkbox"
                            id="permission-{{ $role->id }}-{{ $permission->id }}"
                            name="permissions[]"
                            value="{{ $permission->id }}"
                            @checked($role->permissions->contains($permission))
                        >
                        <span><strong>{{ $permission->name }}</strong><small>{{ $permission->slug }}</small></span>
                    </label>
                @endforeach
            </fieldset>
            <button class="btn btn-primary">Simpan permission {{ $role->name }}</button>
        </form>
    @endforeach
</div>
@endsection
