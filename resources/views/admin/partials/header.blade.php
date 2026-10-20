@php
    $user = auth()->user();
@endphp
<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-6 px-6">
        <a href="{{ route('web-admin.dashboard') }}" class="text-lg font-semibold text-slate-900">
            Roznama admin
        </a>

        <div class="flex items-center gap-6">
            <nav class="flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('web-admin.dashboard') }}" class="text-slate-600 hover:text-slate-900">Dashboard</a>
                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_DOCTORS))
                    <a href="{{ route('web-admin.doctors.index') }}" class="text-slate-600 hover:text-slate-900">Doctors</a>
                @endif
                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_ALL_LABS))
                    <a href="{{ route('web-admin.labs.index') }}" class="text-slate-600 hover:text-slate-900">Labs</a>
                @endif
                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_ALL_SCANS))
                    <a href="{{ route('web-admin.scans.index') }}" class="text-slate-600 hover:text-slate-900">Scans</a>
                @endif
                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_ALL_ADMINS))
                    <a href="{{ route('web-admin.admins.index') }}" class="text-slate-600 hover:text-slate-900">Admins</a>
                @endif
                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_ALL_ROLES))
                    <a href="{{ route('web-admin.roles.index') }}" class="text-slate-600 hover:text-slate-900">Roles</a>
                @endif
                @if ($user->hasAdminPermission(\App\Enums\AdminPermissionsEnum::VIEW_ALL_PERMISSIONS))
                    <a href="{{ route('web-admin.permissions.index') }}" class="text-slate-600 hover:text-slate-900">Permissions</a>
                @endif
            </nav>

            <form method="POST" action="{{ route('web-admin.logout') }}" class="border-l border-slate-200 pl-6">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-4 py-1.5 text-sm font-semibold text-rose-700 shadow-sm transition hover:border-rose-300 hover:bg-rose-100 hover:text-rose-800 focus:outline-none focus:ring-2 focus:ring-rose-300"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
</header>
