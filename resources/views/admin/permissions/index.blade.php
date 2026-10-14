@extends('layouts.admin')

@section('title', 'Permissions')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">Permissions</h1>
        <p class="mt-1 text-sm text-slate-500">Platform admin permissions. These are assignable to roles.</p>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Users</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($permissions as $permission)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $permission['name'] }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $permission['authorized'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-6 py-10 text-center text-slate-500">No permissions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
