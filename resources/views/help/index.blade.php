@extends('layouts.app')

@section('title', 'Help & Guide')

@section('content')
<div class="space-y-6">

    <div class="app-card p-5 lg:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Guide</div>
            <h1 class="text-xl md:text-2xl font-bold tracking-tight text-[#1D1D1F] dark:text-white mt-0.5">How to use The Shoe Boy</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Take an interactive tour of the whole system, or read short instructions for every part below.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0 self-start sm:self-auto">
            <button type="button" onclick="window.appTour && window.appTour.start('', 'essentials')" class="app-btn app-btn-secondary">
                <span>Quick tour</span>
            </button>
            <button type="button" onclick="window.appTour && window.appTour.start('', 'full')" class="app-btn app-btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Full tour</span>
            </button>
        </div>
    </div>

    <div class="lg:grid lg:grid-cols-4 lg:gap-6 lg:items-start">

        {{-- Sticky "On this page" sidebar (desktop) --}}
        <aside class="hidden lg:block lg:col-span-1">
            <div class="sticky app-card p-3" style="top: calc(var(--app-header-h, 3.5rem) + 1rem)">
                <div class="px-2 pt-1 pb-2 text-[11px] font-semibold uppercase tracking-wider text-neutral-500">On this page</div>
                <nav class="max-h-[68vh] overflow-y-auto space-y-0.5 pr-1">
                    @foreach($sections as $section)
                    <button type="button" data-toc="{{ $section['id'] }}"
                            class="app-help-toc w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[13px] text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-40 shrink-0"></span>
                        <span class="truncate">{{ $section['title'] }}</span>
                    </button>
                    @endforeach
                </nav>
                <div class="mt-2 pt-2 border-t border-neutral-100 dark:border-neutral-800">
                    <button type="button" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                            class="app-help-toc w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-[13px] text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        <span>Back to top</span>
                    </button>
                </div>
            </div>
        </aside>

        {{-- Content --}}
        <div class="lg:col-span-3 space-y-5">

            {{-- "On this page" pills (mobile/tablet) --}}
            <div class="lg:hidden app-card p-3">
                <div class="flex gap-2 overflow-x-auto pb-1 -mb-1">
                    @foreach($sections as $section)
                    <button type="button" data-toc="{{ $section['id'] }}"
                            class="app-help-toc shrink-0 px-3 py-1.5 rounded-full text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
                        {{ $section['title'] }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                @foreach($sections as $section)
                <section id="{{ $section['id'] }}" data-help-section
                         style="scroll-margin-top: calc(var(--app-header-h, 3.5rem) + 1rem)"
                         class="app-card p-5 lg:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                                <svg style="width:1.125rem;height:1.125rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $section['icon'] ?? '' }}"/></svg>
                            </span>
                            <h2 class="font-bold text-base text-[#1D1D1F] dark:text-white">{{ $section['title'] }}</h2>
                        </div>
                        @if(!empty($section['url']))
                        <a href="{{ $section['url'] }}"
                           onclick="try{localStorage.setItem('shoeboy.tour.pending','{{ $section['tourKey'] }}')}catch(e){}"
                           class="app-btn app-btn-secondary app-btn-sm shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Show me</span>
                        </a>
                        @else
                        <button type="button" onclick="window.appTour && window.appTour.start('', 'full')"
                                class="app-btn app-btn-secondary app-btn-sm shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Show me</span>
                        </button>
                        @endif
                    </div>
                    <ol class="mt-4 space-y-3">
                        @foreach(($section['steps'] ?? []) as $i => $step)
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-neutral-100 dark:bg-neutral-800 text-[11px] font-bold text-neutral-600 dark:text-neutral-300">{{ $i + 1 }}</span>
                            <span class="text-sm leading-relaxed text-neutral-600 dark:text-neutral-300">{!! $step !!}</span>
                        </li>
                        @endforeach
                    </ol>
                </section>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        (function () {
            var sections = Array.prototype.slice.call(document.querySelectorAll('[data-help-section]'));
            if (!sections.length) return;
            var links = Array.prototype.slice.call(document.querySelectorAll('[data-toc]'));

            function setActive(id) {
                links.forEach(function (l) {
                    l.classList.toggle('is-active', l.getAttribute('data-toc') === id);
                });
            }

            function spy() {
                var header = document.querySelector('header');
                var line = (header ? header.offsetHeight : 56) + 28;
                var current = sections[0].id;
                for (var i = 0; i < sections.length; i++) {
                    if (sections[i].getBoundingClientRect().top - line <= 0) current = sections[i].id;
                }
                if ((window.innerHeight + window.scrollY) >= document.body.scrollHeight - 2) {
                    current = sections[sections.length - 1].id;
                }
                setActive(current);
            }

            function flash(el) {
                if (!el) return;
                el.classList.remove('app-help-target');
                void el.offsetWidth;
                el.classList.add('app-help-target');
                clearTimeout(el._helpTimer);
                el._helpTimer = setTimeout(function () { el.classList.remove('app-help-target'); }, 1600);
            }

            function jump(id, ev) {
                var el = document.getElementById(id);
                if (!el) return;
                if (ev) ev.preventDefault();
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                try { history.replaceState(null, '', '#' + id); } catch (e) {}
                flash(el);
                setActive(id);
            }

            links.forEach(function (l) {
                l.addEventListener('click', function (ev) { jump(l.getAttribute('data-toc'), ev); });
            });

            var ticking = false;
            window.addEventListener('scroll', function () {
                if (ticking) return;
                ticking = true;
                window.requestAnimationFrame(function () { spy(); ticking = false; });
            }, { passive: true });
            window.addEventListener('resize', spy);
            spy();

            if (location.hash) {
                var id = location.hash.slice(1);
                if (document.getElementById(id)) setTimeout(function () { jump(id); }, 120);
            }
        })();
    </script>

</div>
@endsection
