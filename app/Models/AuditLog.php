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
        'item_deleted' => 'Deleted a pair',
        'item_restored' => 'Restored a pair',
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
        'system_backup_restored' => 'Restored a system backup',
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
        'triage_status' => 'Triage stage',
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
                Str::startsWith($this->action, ['item_']) => trim(($d['sku'] ?? '').(! empty($d['brand']) || ! empty($d['model']) ? ' — '.trim(($d['brand'] ?? '').' '.($d['model'] ?? '')) : '')),
                Str::startsWith($this->action, ['order_', 'payment_']) => $d['order_number'] ?? '',
                $this->action === 'delivery_updated' => $d['order_number'] ?? '',
                Str::startsWith($this->action, ['batch_']) => $d['batch_code'] ?? '',
                Str::startsWith($this->action, ['supplier_', 'staff_']) => $d['name'] ?? ($d['email'] ?? ''),
                Str::startsWith($this->action, ['expense_']) => $d['description'] ?? '',
                Str::startsWith($this->action, ['system_backup_']) => $d['file'] ?? '',
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

    /**
     * Concise headline for the audit table (verb + subject). No actor (its own
     * column) and no "from the X console" (the category/link already convey it),
     * so the list stays scannable. The full prose lives in `sentence`.
     */
    protected function headline(): Attribute
    {
        return Attribute::make(get: function () {
            $d = $this->details ?? [];

            return match ($this->action) {
                'user_login' => 'Signed in',
                'user_logout' => 'Signed out',
                'login_failed' => 'Failed sign-in attempt',
                'login_blocked' => 'Blocked sign-in',
                'item_added' => 'Added a pair to inventory',
                'item_updated' => 'Updated a pair',
                'item_triage_updated' => 'Updated pair triage',
                'item_deleted' => 'Deleted a pair',
                'item_restored' => 'Restored a pair',
                'order_awarded' => 'Awarded an order'.(! empty($d['customer_name']) ? ' to '.$d['customer_name'] : ''),
                'order_cancelled' => 'Cancelled an order',
                'payment_verified' => 'Verified a payment',
                'batch_intake_created' => 'Recorded a batch intake'.(! empty($d['batch_code']) ? ' — '.$d['batch_code'] : ''),
                'batch_updated' => 'Updated a batch',
                'batch_deleted' => 'Deleted a batch',
                'delivery_updated' => 'Updated a delivery',
                'staff_account_created' => 'Created a staff account',
                'staff_account_updated' => 'Updated a staff account',
                'staff_status_toggled' => ! empty($d['is_active']) ? 'Reactivated a staff account' : 'Deactivated a staff account',
                'supplier_created' => 'Added a supplier',
                'supplier_updated' => 'Updated a supplier',
                'supplier_deleted' => 'Deleted a supplier',
                'supplier_restored' => 'Restored a supplier',
                'expense_recorded' => 'Recorded an expense'.(! empty($d['category']) ? ' — '.$d['category'] : ''),
                'expense_deleted' => 'Deleted an expense',
                'expense_restored' => 'Restored an expense',
                'system_backup_created' => 'Created a system backup',
                'system_backup_restored' => 'Restored a system backup',
                'system_bootstrapped' => 'System initialized',
                default => $this->description,
            };
        });
    }

    /**
     * Up to three short fact chips for the audit table — the key numbers that
     * used to be buried in a wall of text, kept scannable.
     *
     * @return array<int, string>
     */
    protected function chips(): Attribute
    {
        return Attribute::make(get: function () {
            $d = $this->details ?? [];
            $money = fn ($v) => '₱'.number_format((float) $v, 2);

            $chips = match ($this->action) {
                'item_added' => array_filter([$d['sku'] ?? null, isset($d['price']) ? $money($d['price']) : null]),
                'item_updated', 'item_deleted', 'item_restored' => array_filter([$d['sku'] ?? null]),
                'item_triage_updated' => array_filter([$d['sku'] ?? null, ! empty($d['triage_status']) ? Str::headline($d['triage_status']) : null]),
                'order_awarded' => array_filter([
                    isset($d['items_count']) ? $d['items_count'].' '.($d['items_count'] == 1 ? 'pair' : 'pairs') : null,
                    isset($d['total_awarded_price']) ? $money($d['total_awarded_price']) : null,
                    ($d['order_type'] ?? '') === 'walkin_pos' ? 'POS walk-in' : 'Live stream',
                ]),
                'order_cancelled' => array_filter([$d['item_skus'] ?? null, ! empty($d['reason']) ? $d['reason'] : null]),
                'payment_verified' => array_filter([strtoupper($d['method'] ?? 'cash'), isset($d['amount']) ? $money($d['amount']) : null, $d['reference_no'] ?? null]),
                'batch_intake_created' => array_filter([
                    isset($d['total_pairs']) ? $d['total_pairs'].' pairs' : null,
                    isset($d['total_cost']) ? $money($d['total_cost']) : null,
                ]),
                'batch_updated', 'batch_deleted' => array_filter([$d['batch_code'] ?? null]),
                'delivery_updated' => array_filter([$d['order_number'] ?? null, ! empty($d['status']) ? Str::headline($d['status']) : null]),
                'staff_account_created', 'staff_account_updated', 'staff_status_toggled' => array_filter([$d['name'] ?? null, $d['role'] ?? null]),
                'supplier_created', 'supplier_updated', 'supplier_deleted', 'supplier_restored' => array_filter([$d['name'] ?? null]),
                'expense_recorded' => array_filter([isset($d['amount']) ? $money($d['amount']) : null, $d['reference_no'] ?? null]),
                'expense_deleted', 'expense_restored' => array_filter([isset($d['amount']) ? $money($d['amount']) : null]),
                'system_backup_created', 'system_backup_restored' => array_filter([$d['file'] ?? null]),
                default => [],
            };

            return array_slice(array_values($chips), 0, 3);
        });
    }

    /**
     * Full human-readable sentence for the detail panel. The actor and the
     * "from the X console" context are omitted — both are already present as
     * dedicated fields/columns — so the prose reads as a clean description.
     */
    protected function sentence(): Attribute
    {
        return Attribute::make(get: function () {
            $d = $this->details ?? [];

            return match ($this->action) {
                'user_login' => sprintf('Signed in%s.', ! empty($d['role']) ? ' as '.$d['role'] : ''),
                'user_logout' => 'Signed out.',
                'login_failed' => sprintf('Failed sign-in attempt for %s.', $d['email'] ?? 'an unknown account'),
                'login_blocked' => sprintf('Blocked sign-in for the deactivated account %s.', $d['email'] ?? 'unknown'),

                'item_added' => sprintf(
                    'Added pair %s to inventory%s.',
                    $d['sku'] ?? 'a pair',
                    isset($d['price']) ? ' at a target price of ₱'.number_format((float) $d['price'], 2) : '',
                ),
                'item_updated' => sprintf('Updated pair %s%s.', $this->subject, $this->changeClause($d, ['sku', 'brand', 'model'])),
                'item_triage_updated' => sprintf('Updated the triage of pair %s%s.', $d['sku'] ?? '', $this->changeClause($d)),
                'item_deleted' => sprintf('Deleted pair %s.', $d['sku'] ?? $this->subject),
                'item_restored' => sprintf('Restored pair %s to inventory.', $d['sku'] ?? $this->subject),

                'order_awarded' => sprintf(
                    'Awarded %s%s to %s%s on a %s.',
                    ($d['items_count'] ?? 1).(($d['items_count'] ?? 1) == 1 ? ' pair' : ' pairs'),
                    ! empty($d['item_skus']) ? ' ('.$d['item_skus'].')' : '',
                    $d['customer_name'] ?? 'a buyer',
                    isset($d['total_awarded_price']) ? ' for a total of ₱'.number_format((float) $d['total_awarded_price'], 2) : '',
                    ($d['order_type'] ?? 'live_stream') === 'walkin_pos' ? 'walk-in sale' : 'live stream',
                ),
                'order_cancelled' => sprintf(
                    'Cancelled an order%s%s, returning the pair(s) to available stock.',
                    ! empty($d['item_skus']) ? ' for '.$d['item_skus'] : '',
                    ! empty($d['reason']) ? ' (reason: '.$d['reason'].')' : '',
                ),
                'payment_verified' => sprintf(
                    'Verified a %s payment of ₱%s%s for order %s.',
                    strtoupper($d['method'] ?? 'cash'),
                    number_format((float) ($d['amount'] ?? 0), 2),
                    ! empty($d['reference_no']) ? ' (ref '.$d['reference_no'].')' : '',
                    $d['order_number'] ?? $this->subject,
                ),
                'delivery_updated' => sprintf(
                    'Updated the delivery for order %s%s.',
                    $d['order_number'] ?? $this->subject,
                    $this->changeClause($d, ['order_number']),
                ),

                'batch_intake_created' => sprintf(
                    'Recorded batch intake %s — %s pairs for ₱%s (₱%s avg).',
                    $d['batch_code'] ?? '',
                    $d['total_pairs'] ?? '0',
                    number_format((float) ($d['total_cost'] ?? 0), 2),
                    number_format((float) ($d['avg_cost'] ?? 0), 2),
                ),
                'batch_updated' => sprintf('Updated batch %s%s.', $d['batch_code'] ?? $this->subject, $this->changeClause($d, ['batch_code'])),
                'batch_deleted' => sprintf('Deleted batch %s.', $d['batch_code'] ?? $this->subject),

                'staff_account_created' => sprintf('Created a %s account for %s (%s).', $d['role'] ?? 'staff', $d['name'] ?? '', $d['email'] ?? ''),
                'staff_account_updated' => sprintf('Updated the account for %s%s.', $d['name'] ?? $this->subject, $this->changeClause($d, ['name'])),
                'staff_status_toggled' => sprintf('%s the account for %s.', ! empty($d['is_active']) ? 'Reactivated' : 'Deactivated', $d['name'] ?? $this->subject),

                'supplier_created' => sprintf('Added supplier %s.', $d['name'] ?? ''),
                'supplier_updated' => sprintf('Updated supplier %s%s.', $d['name'] ?? $this->subject, $this->changeClause($d, ['name'])),
                'supplier_deleted' => sprintf('Deleted supplier %s.', $d['name'] ?? ''),
                'supplier_restored' => sprintf('Restored supplier %s.', $d['name'] ?? ''),

                'expense_recorded' => sprintf(
                    'Recorded an expense of ₱%s under %s%s.',
                    number_format((float) ($d['amount'] ?? 0), 2),
                    $d['category'] ?? 'General',
                    ! empty($d['description']) ? ' — "'.$d['description'].'"' : '',
                ),
                'expense_deleted' => sprintf('Deleted the expense "%s" (₱%s).', $d['description'] ?? '', number_format((float) ($d['amount'] ?? 0), 2)),
                'expense_restored' => sprintf('Restored the expense "%s" (₱%s).', $d['description'] ?? '', number_format((float) ($d['amount'] ?? 0), 2)),

                'system_backup_created' => sprintf('Created a database backup (%s).', $d['file'] ?? 'backup file'),
                'system_backup_restored' => sprintf('Restored the database from a backup (%s).', $d['file'] ?? 'backup file'),

                default => sprintf('Performed "%s".', $this->description),
            };
        });
    }

    /** Meta line: actor role · IP · timestamp. */
    protected function contextLine(): Attribute
    {
        return Attribute::make(get: function () {
            return trim(implode(' · ', array_filter([
                $this->user?->role ? ucfirst($this->user->role) : null,
                $this->ip_address ? 'IP '.$this->ip_address : null,
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
                Str::startsWith($this->action, ['order_', 'payment_']) && ! empty($d['order_number']) => ['label' => 'View order', 'url' => route('orders.index', ['focus' => $d['order_number']])],
                $this->action === 'delivery_updated' && ! empty($d['order_number']) => ['label' => 'View delivery', 'url' => route('deliveries.index', ['search' => $d['order_number']])],
                Str::startsWith($this->action, ['item_']) && ! empty($d['sku']) => ['label' => 'View item', 'url' => route('items.index', ['search' => $d['sku']])],
                Str::startsWith($this->action, ['batch_']) && ! empty($d['batch_code']) => ['label' => 'View batch', 'url' => route('batches.index', ['search' => $d['batch_code']])],
                Str::startsWith($this->action, ['expense_']) && ! empty($d['description']) => ['label' => 'View expense', 'url' => route('expenses.index', ['search' => $d['description']])],
                Str::startsWith($this->action, ['staff_']) && ! empty($d['name']) => ['label' => 'View staff', 'url' => route('staff.index', ['search' => $d['name']])],
                Str::startsWith($this->action, ['supplier_']) && ! empty($d['name']) => ['label' => 'View supplier', 'url' => route('suppliers.index', ['search' => $d['name']])],
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
            $parts[] = (self::DETAIL_LABELS[$key] ?? Str::headline($key)).' '.self::formatDetailValue($key, $value);
        }

        return empty($parts) ? '' : ' — '.implode(', ', $parts);
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
            return '₱'.number_format((float) $value, 2);
        }

        return (string) $value;
    }
}
