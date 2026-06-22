@extends('main_page.admin-panel.layout')

@section('title', 'Role & Permission')
@section('page-title', 'Role & Permission')

@section('content')
<div class="permission-grid">
    @foreach($roles as $role)
        <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="panel role-card">
            @csrf @method('PUT')
            <div class="panel-heading"><div><span class="panel-kicker">Role sistem</span><h2>{{ $role->name }}</h2></div><span>{{ $role->permissions->count() }} izin</span></div>
            <p class="muted">{{ $role->description }}</p>
            <div class="permission-list">
                @foreach($permissions as $permission)
                    <label>
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                            @checked($role->slug === 'super_admin' || $role->permissions->contains($permission))
                            @disabled($role->slug === 'super_admin')>
                        <span><strong>{{ $permission->name }}</strong><small>{{ $permission->slug }}</small></span>
                    </label>
                @endforeach
            </div>
            @if($role->slug !== 'super_admin')
                <button class="btn btn-primary">Simpan Permission</button>
            @else
                <div class="alert alert-info">Super admin selalu memiliki semua permission.</div>
            @endif
        </form>
    @endforeach
</div>
@endsection
