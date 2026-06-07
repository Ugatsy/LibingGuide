@extends('layouts.public')

@section('title', 'Pre-Need Plans — Heritage Memorial Park')

@section('content')
    <section class="pt-32 pb-24 bg-stone-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-emerald-700 font-semibold text-sm tracking-widest uppercase mb-3">Pre-Need Plans</p>
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Plan Ahead with Confidence</h1>
                <p class="text-gray-600 max-w-2xl mx-auto">Explore our pre-need memorial and funeral plans. Compare features and pricing, and secure your family's peace of mind.</p>
                <div class="mt-6 inline-flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span>Plans <strong>include a lot plus services</strong> (burial, funeral, or memorial). If you just want to purchase a lot without services, browse <a href="{{ route('public.lots') }}" class="underline font-medium hover:text-amber-900">Memorial Lots</a> instead.</span>
                </div>
            </div>

            @if($plans->isEmpty())
                <div class="text-center py-16">
                    <p class="text-gray-500 text-lg">No plans available at this time.</p>
                </div>
            @endif

            @foreach($plans as $type => $typePlans)
                <div class="mb-16">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2 capitalize">{{ $type }} Plans</h2>
                    <div class="w-12 h-1 bg-emerald-600 mb-8"></div>
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($typePlans as $plan)
                            <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow flex flex-col">
                                @if($plan->image)
                                    <img src="{{ $plan->image }}" alt="{{ $plan->name }}" class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-gradient-to-br from-emerald-100 to-emerald-200 flex items-center justify-center">
                                        <svg class="w-16 h-16 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21a9.004 9.004 0 0 0 8.715-6.564M12 21a9.004 9.004 0 0 1-8.715-6.564M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.266 4.383L12 12l-7.266-4.617Z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="p-6 flex-1 flex flex-col">
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                                    <p class="text-gray-600 text-sm mb-4 flex-1">{{ $plan->description }}</p>
                                    @if($plan->features)
                                        <ul class="space-y-1 mb-6">
                                            @foreach(array_slice($plan->features, 0, 4) as $feature)
                                                <li class="flex items-start gap-2 text-sm text-gray-600">
                                                    <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7"/></svg>
                                                    {{ $feature }}
                                                </li>
                                            @endforeach
                                            @if(count($plan->features) > 4)
                                                <li class="text-sm text-gray-400">+{{ count($plan->features) - 4 }} more features</li>
                                            @endif
                                        </ul>
                                    @endif
                                    <div class="pt-4 border-t border-gray-100 mt-auto">
                                        <div class="flex items-center justify-between mb-4">
                                            <span class="text-2xl font-bold text-emerald-700">₱{{ number_format($plan->price, 2) }}</span>
                                            <span class="text-xs px-2 py-1 rounded-full bg-emerald-100 text-emerald-800 capitalize">{{ $plan->type }}</span>
                                        </div>
                                        <a href="{{ route('public.plans.detail', $plan) }}" class="block text-center w-full px-4 py-2.5 bg-emerald-700 text-white font-semibold rounded-lg hover:bg-emerald-600 transition-colors">View Details</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection
