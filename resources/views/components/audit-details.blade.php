@props(['log'])

@if($log->detail_items)
    <dl {{ $attributes->merge(['class' => 'grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1.5 text-xs']) }}>
        @foreach($log->detail_items as $detail)
            <div class="flex items-center justify-between gap-3">
                <dt class="text-neutral-500">{{ $detail['label'] }}</dt>
                <dd class="text-right font-medium text-neutral-700 dark:text-neutral-200">{{ $detail['value'] }}</dd>
            </div>
        @endforeach
    </dl>
@endif
