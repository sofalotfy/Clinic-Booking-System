<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-6">
        <a href="{{ route('web-admin.dashboard') }}" class="text-lg font-semibold text-slate-900">
            {{ config('app.name') }} Admin
        </a>

        <nav class="flex items-center gap-6 text-sm font-medium">
            <a href="{{ route('web-admin.dashboard') }}" class="text-slate-600 hover:text-slate-900">Dashboard</a>
            <a href="{{ route('web-admin.doctors.index') }}" class="text-slate-600 hover:text-slate-900">Doctors</a>

            <form method="POST" action="{{ route('web-admin.logout') }}">
                @csrf
                <button type="submit" class="text-slate-600 hover:text-slate-900">Logout</button>
            </form>
        </nav>
    </div>
</header>