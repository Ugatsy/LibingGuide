@extends('layouts.public')

@section('title', 'Heritage Memorial Park — A Place of Peace & Legacy')

@section('content')
    {{-- Hero Section --}}
    <section class="relative min-h-screen flex items-center justify-center bg-gray-900 overflow-hidden pt-16 lg:pt-20">
        <div class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1504253163759-c23fccaebb55?w=1920&q=80" alt="Memorial Park" class="w-full h-full object-cover opacity-60">
            <div class="absolute inset-0 bg-gradient-to-r from-gray-900/90 via-gray-900/60 to-transparent"></div>
        </div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32 w-full">
            <div class="max-w-2xl">
                <p class="inline-block px-4 py-1.5 bg-emerald-700/80 text-white text-sm font-medium rounded-full mb-6">Heritage Memorial Park</p>
                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-bold text-white leading-tight mb-6">
                    A Place of<br>
                    <span class="text-emerald-300">Peace & Legacy</span>
                </h1>
                <p class="text-lg sm:text-xl text-gray-200 mb-10 max-w-xl leading-relaxed">
                    Honoring lives, comforting families, and creating a lasting legacy for generations to come in Solano, Nueva Vizcaya.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('public.lots') }}" class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-lg transition-colors shadow-lg shadow-emerald-900/30">
                        Browse Memorial Lots
                        <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                    </a>
                    <a href="#about" class="inline-flex items-center px-6 py-3 border-2 border-white/40 hover:border-white text-white font-semibold rounded-lg transition-colors">
                        Learn More
                    </a>
                    <a href="#inquire" class="inline-flex items-center px-6 py-3 bg-white text-emerald-800 font-semibold rounded-lg transition-colors shadow-lg hover:bg-emerald-50">
                        Inquire Now
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
        </div>
    </section>

    {{-- About Section --}}
    <section id="about" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <p class="text-emerald-700 font-semibold text-sm tracking-widest uppercase mb-3">About Us</p>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-6">A Sanctuary of Life & Legacy</h2>
                    <div class="w-16 h-1 bg-emerald-600 mb-6"></div>
                    <p class="text-gray-600 leading-relaxed mb-4">
                        Heritage Memorial Park is a place where timeless beauty meets thoughtful design — a sanctuary where families can honor, remember, and celebrate the lives of their loved ones. Nestled in the heart of Solano, Nueva Vizcaya, our memorial park offers a serene and dignified environment for reflection and remembrance.
                    </p>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        We are committed to providing compassionate service and premium memorial solutions that honor your family's legacy for generations.
                    </p>
                    <div class="grid grid-cols-3 gap-6 pt-4 border-t border-gray-100">
                        <div>
                            <p class="text-3xl font-bold text-emerald-700">50+</p>
                            <p class="text-sm text-gray-500">Years of Service</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-emerald-700">500+</p>
                            <p class="text-sm text-gray-500">Families Served</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-emerald-700">100%</p>
                            <p class="text-sm text-gray-500">Dignified Care</p>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?w=800&q=80" alt="Peaceful memorial park" class="rounded-2xl shadow-2xl w-full h-[500px] object-cover">
                    <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-xl max-w-[200px]">
                        <p class="text-4xl font-bold text-emerald-700">20+</p>
                        <p class="text-sm text-gray-600 mt-1">Years of Compassionate Service</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Memorial Lots Section --}}
    <section id="lots" class="py-24 bg-stone-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-emerald-700 font-semibold text-sm tracking-widest uppercase mb-3">Memorial Lots</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Find a Peaceful Resting Place</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Browse our available memorial lots and choose a peaceful resting place that will honor your family's legacy for generations.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow group">
                    <div class="h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1505322022379-7c3353ee6295?w=600&q=80" alt="Garden Lot" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Garden Lots</h3>
                        <p class="text-gray-600 text-sm mb-4">Serene garden settings surrounded by lush greenery and seasonal blooms.</p>
                        <a href="{{ route('public.lots') }}" class="inline-flex items-center text-emerald-700 font-medium text-sm hover:text-emerald-600">
                            Browse Available Lots
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow group">
                    <div class="h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1464998857653-0e0e8d6ae0d0?w=600&q=80" alt="Family Lot" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Family Estates</h3>
                        <p class="text-gray-600 text-sm mb-4">Spacious family lots designed to keep loved ones together for eternity.</p>
                        <a href="{{ route('public.lots') }}" class="inline-flex items-center text-emerald-700 font-medium text-sm hover:text-emerald-600">
                            Browse Available Lots
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow group">
                    <div class="h-56 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?w=600&q=80" alt="Lawn Lot" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Lawn Lots</h3>
                        <p class="text-gray-600 text-sm mb-4">Beautiful lawn lots offering a tranquil and well-maintained environment.</p>
                        <a href="{{ route('public.lots') }}" class="inline-flex items-center text-emerald-700 font-medium text-sm hover:text-emerald-600">
                            Browse Available Lots
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Columbarium Section --}}
    <section id="columbarium" class="py-24 bg-gray-900 text-white relative overflow-hidden">
        <div class="absolute inset-0">
            <img src="https://images.unsplash.com/photo-1588137378633-dea1336ce1a0?w=1920&q=80" alt="Columbarium" class="w-full h-full object-cover opacity-30">
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/80 to-gray-900/60"></div>
        </div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <p class="inline-block px-4 py-1.5 bg-amber-500/80 text-white text-sm font-medium rounded-full mb-4">Coming Soon</p>
                    <p class="text-emerald-300 font-semibold text-sm tracking-widest uppercase mb-3">Columbarium</p>
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">Heritage Columbarium</h2>
                    <div class="w-16 h-1 bg-emerald-500 mb-6"></div>
                    <p class="text-gray-300 leading-relaxed mb-6">
                        Introducing <strong class="text-white">Heritage Columbarium</strong>, Nueva Vizcaya's first modern columbarium — a sanctuary of life and legacy thoughtfully created for families seeking a peaceful, dignified, and enduring place of remembrance.
                    </p>
                    <p class="text-gray-400 mb-8">Our columbarium is under development. Stay tuned for updates.</p>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('public.columbarium') }}" class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-500 text-white font-semibold rounded-lg transition-colors cursor-default opacity-70 pointer-events-none">
                            See Available Vaults
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                <div class="relative">
                    <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-8 text-center">
                        <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-amber-500/10 flex items-center justify-center">
                            <svg class="w-12 h-12 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                        <p class="text-4xl font-bold text-amber-300 mb-2">COMING SOON</p>
                        <p class="text-gray-400">Our columbarium is currently under development</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Inquire / Reserve Section --}}
    <section id="inquire" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-start">
                <div>
                    <p class="text-emerald-700 font-semibold text-sm tracking-widest uppercase mb-3">Inquire / Reserve</p>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">We'd Love to Hear from You</h2>
                    <div class="w-16 h-1 bg-emerald-600 mb-6"></div>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Whether you're just exploring options or ready to inquire about a specific memorial lot or plan, fill out the form below and our team will get back to you promptly.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <div><p class="font-medium text-gray-900">Heritage Memorial Park</p><p class="text-sm text-gray-500">Solano, Nueva Vizcaya</p></div>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <div><p class="font-medium text-gray-900">Contact Number</p><p class="text-sm text-gray-500">Available upon request</p></div>
                        </div>
                    </div>
                </div>
                <div class="bg-stone-50 rounded-2xl p-8 shadow-sm">
                    @if(session('success'))
                        <div class="bg-emerald-100 text-emerald-800 p-4 rounded-lg mb-6 font-medium">{{ session('success') }}</div>
                    @endif
                    <form method="POST" action="{{ route('public.inquire') }}" x-data="{ showLotPicker: false }" class="space-y-4">
                        @csrf
                        <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                            <p class="text-sm font-medium text-gray-700 mb-3">What brings you here?</p>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="inquiry_type" value="general" @change="showLotPicker = false" checked class="text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-sm text-gray-700">Just inquiring</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="inquiry_type" value="specific" @change="showLotPicker = true" class="text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-sm text-gray-700">Interested in a specific lot</span>
                                </label>
                            </div>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label><input type="text" name="full_name" value="{{ old('full_name') }}" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label><input type="text" name="contact_number" value="{{ old('contact_number') }}" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></div>
                            <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></div>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Address</label><input type="text" name="address" value="{{ old('address') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></div>
                        <div x-show="showLotPicker" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lot Interest</label>
                            <select name="lot_interest" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Select lot type...</option>
                                <option value="Garden Lot" @selected(old('lot_interest') === 'Garden Lot')>Garden Lot</option>
                                <option value="Family Estate" @selected(old('lot_interest') === 'Family Estate')>Family Estate</option>
                                <option value="Lawn Lot" @selected(old('lot_interest') === 'Lawn Lot')>Lawn Lot</option>
                            </select>
                        </div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Message</label><textarea name="message" rows="4" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('message') }}</textarea></div>
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 px-6 rounded-lg transition-colors shadow-lg">Submit Inquiry</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- Careers Section --}}
    <section id="careers" class="py-24 bg-stone-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1573497620053-ea5300f94f21?w=800&q=80" alt="Team" class="rounded-2xl shadow-2xl w-full h-[450px] object-cover">
                    <div class="absolute -top-4 -right-4 bg-emerald-600 text-white p-4 rounded-2xl shadow-lg">
                        <p class="text-2xl font-bold">We're</p>
                        <p class="text-2xl font-bold">Hiring!</p>
                    </div>
                </div>
                <div>
                    <p class="text-emerald-700 font-semibold text-sm tracking-widest uppercase mb-3">Careers</p>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">We Are Hiring</h2>
                    <p class="text-4xl font-bold text-emerald-700 mb-6">Sales Counselors!</p>
                    <div class="w-16 h-1 bg-emerald-600 mb-6"></div>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        We want you to be part of our growing team. Whether you are starting fresh or looking for a flexible role, this could be your next big opportunity. Join us in providing compassionate service to families in our community.
                    </p>
                    <a href="#" class="inline-flex items-center px-6 py-3 bg-emerald-700 hover:bg-emerald-600 text-white font-semibold rounded-lg transition-colors shadow-lg">
                        Apply Now
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7V3m0 0L9 7m4-4h4M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-4"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
