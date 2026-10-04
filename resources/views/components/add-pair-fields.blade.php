@props([
    'lockedBatch' => null,
    'batches' => collect(),
])

@if($lockedBatch)
    <input type="hidden" name="batch_id" value="{{ $lockedBatch->id }}">
    <div class="flex items-center justify-between rounded-xl bg-neutral-100 dark:bg-neutral-800/70 px-3.5 py-2.5 text-xs">
        <span class="text-neutral-500">Batch</span>
        <span class="font-mono font-semibold text-neutral-800 dark:text-neutral-200">{{ $lockedBatch->batch_code }}</span>
    </div>
@else
    <div>
        <label class="app-label">Batch <span class="app-req">*</span></label>
        <select name="batch_id" required class="app-select">
            @foreach($batches as $b)
                <option value="{{ $b->id }}">{{ $b->batch_code }} — {{ $b->supplier?->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-[11px] text-neutral-500">Every pair belongs to a shipment (batch).</p>
    </div>
@endif

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="app-label">Brand <span class="app-req">*</span></label>
        <input type="text" name="brand" required placeholder="Li-Ning, Nike..." class="app-input">
    </div>
    <div>
        <label class="app-label">Model Name <span class="app-req">*</span></label>
        <input type="text" name="model" required placeholder="Way of Wade 10..." class="app-input">
    </div>
</div>

<div class="grid grid-cols-3 gap-3">
    <div>
        <label class="app-label">Size <span class="app-req">*</span></label>
        <input type="text" name="size" required placeholder="US 10.5" class="app-input font-mono">
    </div>
    <div>
        <label class="app-label">Condition <span class="app-req">*</span></label>
        <select name="condition" required class="app-select">
            <option value="Pristine">Pristine</option>
            <option value="Good" selected>Good</option>
            <option value="Fair">Fair</option>
            <option value="Needs Repair">Needs Repair</option>
        </select>
    </div>
    <div>
        <label class="app-label">Status <span class="app-req">*</span></label>
        <select name="status" required class="app-select">
            <option value="available" selected>Available</option>
            <option value="reserved">Reserved</option>
            <option value="sold">Sold</option>
        </select>
    </div>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="app-label">Target Listed Price (₱) <span class="app-req">*</span></label>
        <input type="number" step="0.01" min="0" name="listed_price" required placeholder="3500.00" class="app-input font-mono">
        <p class="mt-1 text-[11px] text-neutral-500">Tier auto-assigns &mdash; T1 &lt; ₱1k &middot; T2 ₱1k&ndash;2k &middot; T3 ₱2k+</p>
    </div>
    <div>
        <label class="app-label">Repair Cost (₱) <span class="app-optional">(optional)</span></label>
        <input type="number" step="0.01" min="0" name="repair_cost" value="0.00" class="app-input font-mono">
    </div>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="app-label">Custom SKU <span class="app-optional">(optional)</span></label>
        <input type="text" name="sku" placeholder="Auto if empty" class="app-input font-mono uppercase">
    </div>
    <div>
        <label class="app-label">Category <span class="app-optional">(optional)</span></label>
        <input type="text" name="category" placeholder="Basketball, Running..." class="app-input">
    </div>
</div>
