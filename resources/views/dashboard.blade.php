<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

            @php $role = auth()->user()->role?->name; @endphp

            @switch($role)
                @case('super_admin')
                    @include('dashboard.super-admin')
                    @break
                @case('engr')
                    @include('dashboard.engr')
                    @break
                @default
                    @include('dashboard.rcc-staff')
            @endswitch
        </div>
    </div>
</x-app-layout>
