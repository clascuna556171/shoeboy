<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'details',
        'ip_address',
    ];

    protected const DESCRIPTIONS = [
        'user_login' => 'Signed in',
        'user_logout' => 'Signed out',
        'login_failed' => 'Failed sign-in attempt',
        'login_blocked' => 'Blocked sign-in',
        'item_added' => 'Added a pair to inventory',
        'item_updated' => 'Updated a pair',
        'item_triage_updated' => 'Updated pair triage',
        'order_awarded' => 'Awarded an order',
        'order_cancelled' => 'Cancelled an order',
        'payment_verified' => 'Verified a payment',
        'batch_intake_created' => 'Recorded a batch intake',
        'batch_updated' => 'Updated a batch',
        'batch_deleted' => 'Deleted a batch',
        'delivery_updated' => 'Updated a delivery',
        'staff_account_created' => 'Created a staff account',
        'staff_account_updated' => 'Updated a staff account',
        'staff_status_toggled' => 'Changed staff access',
        'supplier_created' => 'Added a supplier',
        'supplier_updated' => 'Updated a supplier',
        'supplier_deleted' => 'Deleted a supplier',
        'supplier_restored' => 'Restored a supplier',
        'expense_recorded' => 'Recorded an expense',
        'expense_deleted' => 'Deleted an expense',
        'expense_restored' => 'Restored an expense',
        'system_backup_created' => 'Created a system backup',
        'system_bootstrapped' => 'System initialized',
    ];

    protected const CATEGORY_BADGE = [
        'security' => 'badge-security',
        'inventory' => 'badge-inventory',
        'sales' => 'badge-sales',
        'finance' => 'badge-finance',
        'admin' => 'badge-admin',
        'system' => 'badge-system',
    ];

    protected const CATEGORY_DOT = [
        'security' => 'bg-indigo-400',
        'inventory' => 'bg-emerald-400',
        'sales' => 'bg-rose-400',
        'finance' => 'bg-amber-400',
        'admin' => 'bg-violet-400',
        'system' => 'bg-neutral-400',
    ];

    protected const DETAIL_LABELS = [
        'name' => 'Name',
        'email' => 'Email',
        'role' => 'Role',
        'is_active' => 'Active',
        'status' => 'Status',
        'sku' => 'SKU',
        'brand' => 'Brand',
        'model' => 'Model',
        'condition' => 'Condition',
        'size' => 'Size',
        'category' => 'Category',
        'price' => 'Price',
        'listed_price' => 'Target price',
        'repair_cost' => 'Repair cost',
        'amount' => 'Amount',
        'description' => 'Description',
        'reference_no' => 'Reference',
        'batch_code' => 'Batch',
        'total_pairs' => 'Pairs',
        'total_sacks' => 'Sacks',
        'total_cost' => 'Total cost',
        'avg_cost' => 'Avg cost',
        'item_skus' => 'Pairs',
        'items_count' => 'Pairs',
        'customer_name' => 'Customer',
        'total_awarded_price' => 'Total',
        'reason' => 'Reason',
        'order_number' => 'Order',
        'method' => 'Method',
        'tracking' => 'Tracking',
        'contact_number' => 'Contact',
        'notes' => 'Notes',
        'file' => 'File',
    ];

    private const CURRENCY_KEYS = [
        'price', 'listed_price', 'repair_cost', 'amount', 'total_cost',
        'avg_cost', 'total_awarded_price',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Short label (used as a fallback / heading). */
    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn () => self::DESCRIPTIONS[$this->action] ?? Str::headline($this->action),
        );
    }

    /** Grouping category used to pick an icon/colour. */
    protected function category(): Attribute
    {
        return Attribute::make(get: function () {
            return match (true) {
                Str::startsWith($this->action, ['user_login', 'user_logout', 'login_']) => 'security',
                Str::startsWith($this->action, ['item_', 'batch_']) => 'inventory',
                Str::startsWith($this->action, ['order_', 'payment_', 'delivery_']) => 'sales',
                Str::startsWith($this->action, ['expense_']) => 'finance',
                Str::startsWith($this->action, ['staff_', 'supplier_']) => 'admin',
                default => 'system',
            };
        });
    }

    /** Friendly name of the console/page where the action happened. */
    protected function where(): Attribute
    {
        return Attribute::make(get: function () {
            return match (true) {
                Str::startsWith($this->action, ['user_login', 'user_logout', 'login_']) => 'Sign-in',
                Str::startsWith($this->action, ['item_']) => 'Inventory',
                Str::startsWith($this->action, ['batch_']) => 'Batches',
                Str::startsWith($this->action, ['order_', 'payment_']) => 'Orders console',
                Str::startsWith($this->action, ['delivery_']) => 'Deliveries',
                Str::startsWith($this->action, ['expense_']) => 'Expenses',
                Str::startsWith($this->action, ['staff_']) => 'Staff Management',
                Str::startsWith($this->action, ['supplier_']) => 'Suppliers',
                default => 'the system',
            };
        });
    }

    /** The affected entity in plain language. */
    protected function subject(): Attribute
    {
        return Attribute::make(get: function () {
            $d = $this->details ?? [];

            return match (true) {
                $this->action === 'item_added' => $d['sku'] ?? '',
                Str::startsWith($this->action, ['item_']) => trim(($d['sku'] ?? '') . (! empty($d['brand']) || ! empty($d['model']) ? ' — ' . trim(($d['brand'] ?? '') . ' ' . ($d['model'] ?? '')) : '')),
                Str::startsWith($this->action, ['order_', 'payment_']) => $d['order_number'] ?? '',
                $this->action === 'delivery_updated' => $d['order_number'] ?? '',
                Str::startsWith($this->action, ['batch_']) => $d['batch_code'] ?? '',
                Str::startsWith($this->action, ['supplier_', 'staff_']) => $d['name'] ?? ($d['email'] ?? ''),
                Str::startsWith($this->action, ['expense_']) => $d['description'] ?? '',
                $this->action === 'system_backup_created' => $d['file'] ?? '',
                default => '',
            };
        });
    }

    /** Readable "Label: value" list from the stored details. */
    protected function detailItems(): Attribute
    {
        return Attribute::make(get: function () {
            $out = [];
            foreach (($this->details ?? []) as $key => $value) {
                $out[] = [
                    'key' => $key,
                    'label' => self::DETAIL_LABELS[$key] ?? Str::headline($key),
                    'value' => self::formatDetailValue($key, $value),
                ];
            }

            return $out;
        });
    }

    /** Full human-readable sentence describing the event. */
    protected function sentence(): Attribute
    {
        return Attribute::make(get: function () {
            $d = $this->details ?? [];
            $actor = $this->user?->name ?? 'System';

            return match ($this->action) {
                'user_login' => sprintf('%s signed in%s.', $actor, ! empty($d['role']) ? ' as ' . $d['role'] : ''),
                'user_logout' => sprintf('%s signed out.', $actor),
                'login_failed' => sprintf('Failed sign-in attempt for %s.', $d['email'] ?? 'an unknown account'),
                'login_blocked' => sprintf('Blocked sign-in for the deactivated account %s.', $d['email'] ?? ($this->user?->name ?? 'unknown')),

                'item_added' => sprintf(
                    '%s added pair %s to inventory%s from the Inventory console.',
                    $actor,
                    $d['sku'] ?? 'a pair',
                    isset($d['price']) ? ' at a target price of ₱' . number_format((float) $d['price'], 2) : '',
                ),
                'item_updated' => sprintf('%s updated pair %s from the Inventory console%s.', $actor, $this->subject, $this->changeClause($d, ['sku', 'brand', 'model'])),
                'item_triage_updated' => sprintf('%s updated the triage of pair %s from the Inventory console%s.', $actor, $d['sku'] ?? '', $this->changeClause($d)),

                'order_awarded' => sprintf(
                    '%s awarded %s%s to %s%s on a %s.',
                    $actor,
                    ($d['items_count'] ?? 1) . (($d['items_count'] ?? 1) == 1 ? ' pair' : ' pairs'),
                    ! empty($d['item_skus']) ? ' (' . $d['item_skus'] . ')' : '',
                    $d['customer_name'] ?? 'a buyer',
                    isset($d['total_awarded_price']) ? ' for a total of ₱' . number_format((float) $d['total_awarded_price'], 2) : '',
                    ($d['order_type'] ?? 'live_stream') === 'walkin_pos' ? 'walk-in sale' : 'live stream',
                ),
                'order_cancelled' => sprintf(
                    '%s cancelled an order%s%s, returning the pair(s) to available stock.',
                    $actor,
                    ! empty($d['item_skus']) ? ' for ' . $d['item_skus'] : '',
                    ! empty($d['reason']) ? ' (reason: ' . $d['reason'] . ')' : '',
                ),
                'payment_verified' => sprintf(
                    '%s verified a %s payment of ₱%s%s for order %s.',
                    $actor,
                    strtoupper($d['method'] ?? 'cash'),
                    number_format((float) ($d['amount'] ?? 0), 2),
                    ! empty($d['reference_no']) ? ' (ref ' . $d['reference_no'] . ')' : '',
                    $d['order_number'] ?? $this->subject,
                ),
                'delivery_updated' => sprintf(
                    '%s updated the delivery for order %s from the Deliveries console%s.',
                    $actor,
                    $d['order_number'] ?? $this->subject,
                    $this->changeClause($d, ['order_number']),
                ),

                'batch_intake_created' => sprintf(
                    '%s recorded batch intake %s — %s pairs for ₱%s (₱%s avg) from the Batches console.',
                    $actor,
                    $d['batch_code'] ?? '',
                    $d['total_pairs'] ?? '0',
                    number_format((float) ($d['total_cost'] ?? 0), 2),
                    number_format((float) ($d['avg_cost'] ?? 0), 2),
                ),
                'batch_updated' => sprintf('%s updated batch %s from the Batches console%s.', $actor, $d['batch_code'] ?? $this->subject, $this->changeClause($d, ['batch_code'])),
                'batch_deleted' => sprintf('%s deleted batch %s from the Batches console.', $actor, $d['batch_code'] ?? $this->subject),

                'staff_account_created' => sprintf('%s created a %s account for %s (%s) from Staff Management.', $actor, $d['role'] ?? 'staff', $d['name'] ?? '', $d['email'] ?? ''),
                'staff_account_updated' => sprintf('%s updated the account for %s from Staff Management%s.', $actor, $d['name'] ?? $this->subject, $this->changeClause($d, ['name'])),
                'staff_status_toggled' => sprintf('%s %s the account for %s from Staff Management.', $actor, ! empty($d['is_active']) ? 'reactivated' : 'deactivated', $d['name'] ?? $this->subject),

                'supplier_created' => sprintf('%s added supplier %s from the Suppliers console.', $actor, $d['name'] ?? ''),
                'supplier_updated' => sprintf('%s updated supplier %s from the Suppliers console%s.', $actor, $d['name'] ?? $this->subject, $this->changeClause($d, ['name'])),
                'supplier_deleted' => sprintf('%s deleted supplier %s from the Suppliers console.', $actor, $d['name'] ?? ''),
                'supplier_restored' => sprintf('%s restored supplier %s.', $actor, $d['name'] ?? ''),

                'expense_recorded' => sprintf(
                    '%s recorded an expense of ₱%s under %s%s from the Expenses console.',
                    $actor,
                    number_format((float) ($d['amount'] ?? 0), 2),
                    $d['category'] ?? 'General',
                    ! empty($d['description']) ? ' — "' . $d['description'] . '"' : '',
                ),
                'expense_deleted' => sprintf('%s deleted the expense "%s" (₱%s).', $actor, $d['description'] ?? '', number_format((float) ($d['amount'] ?? 0), 2)),
                'expense_restored' => sprintf('%s restored the expense "%s" (₱%s).', $actor, $d['description'] ?? '', number_format((float) ($d['amount'] ?? 0), 2)),

                'system_backup_created' => sprintf('The system created a database backup (%s).', $d['file'] ?? 'backup file'),

                default => sprintf('%s performed "%s" from %s.', $actor, $this->description, $this->where),
            };
        });
    }

    /** Meta line: actor role · IP · timestamp. */
    protected function contextLine(): Attribute
    {
        return Attribute::make(get: function () {
            return trim(implode(' · ', array_filter([
                $this->user?->role ? ucfirst($this->user->role) : null,
                $this->ip_address ? 'IP ' . $this->ip_address : null,
                $this->created_at?->format('M d, Y g:i A'),
            ])));
        });
    }

    /** Tailwind badge classes for the entry's category. */
    protected function categoryBadgeClass(): Attribute
    {
        return Attribute::make(get: fn () => self::CATEGORY_BADGE[$this->category] ?? self::CATEGORY_BADGE['system']);
    }

    /** Tailwind dot classes for the entry's category. */
    protected function categoryDotClass(): Attribute
    {
        return Attribute::make(get: fn () => self::CATEGORY_DOT[$this->category] ?? self::CATEGORY_DOT['system']);
    }

    /** Optional deep-link back to the affected record/page. */
    protected function link(): Attribute
    {
        return Attribute::make(get: function () {
            $d = $this->details ?? [];

            return match (true) {
                Str::startsWith($this->action, ['order_', 'payment_']) && ! empty($d['order_number'])
                    => ['label' => 'View order', 'url' => route('orders.index', ['focus' => $d['order_number']])],
                $this->action === 'delivery_updated' && ! empty($d['order_number'])
                    => ['label' => 'View delivery', 'url' => route('deliveries.index', ['search' => $d['order_number']])],
                Str::startsWith($this->action, ['item_']) && ! empty($d['sku'])
                    => ['label' => 'View item', 'url' => route('items.index', ['search' => $d['sku']])],
                Str::startsWith($this->action, ['batch_']) && ! empty($d['batch_code'])
                    => ['label' => 'View batch', 'url' => route('batches.index', ['search' => $d['batch_code']])],
                Str::startsWith($this->action, ['expense_']) && ! empty($d['description'])
                    => ['label' => 'View expense', 'url' => route('expenses.index', ['search' => $d['description']])],
                Str::startsWith($this->action, ['staff_']) && ! empty($d['name'])
                    => ['label' => 'View staff', 'url' => route('staff.index', ['search' => $d['name']])],
                Str::startsWith($this->action, ['supplier_']) && ! empty($d['name'])
                    => ['label' => 'View supplier', 'url' => route('suppliers.index', ['search' => $d['name']])],
                default => null,
            };
        });
    }

    /**
     * Build a ", key: value, ..." clause from details, skipping ignored keys.
     *
     * @param  array<string, mixed>  $details
     * @param  array<int, string>  $ignore
     */
    protected function changeClause(array $details, array $ignore = []): string
    {
        $parts = [];
        foreach ($details as $key => $value) {
            if (in_array($key, $ignore, true) || $value === null || $value === '') {
                continue;
            }
            $parts[] = (self::DETAIL_LABELS[$key] ?? Str::headline($key)) . ' ' . self::formatDetailValue($key, $value);
        }

        return empty($parts) ? '' : ' — ' . implode(', ', $parts);
    }

    protected static function formatDetailValue(string $key, mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return implode(', ', array_map(fn ($v) => (string) $v, $value));
        }

        if (in_array($key, self::CURRENCY_KEYS, true) && is_numeric($value)) {
            return '₱' . number_format((float) $value, 2);
        }

        return (string) $value;
    }
}
