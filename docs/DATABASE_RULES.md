# Database Rules — The Shoe Boy

A human-readable companion to the migrations. It explains the **intended** shape
of the data, the lifecycles, the money rules, and what is **not** intended.

- **Engine:** SQLite by default (`database/database.sqlite`); MySQL 8+ supported.
- **Convention:** timestamps are stored in the app timezone (`APP_TIMEZONE`, default `Asia/Manila`).
- **Money:** all amounts are `DECIMAL(10,2)`; the currency is ₱ (PHP).

---

## 1. Core principles

1. **One row in `items` = exactly one physical pair.** It has a unique `sku`
   (e.g. `B04-001`) and belongs to one `batch`.
2. **Cost of goods lives on the batch, not in expenses.**
   `batches.total_cost` is the bale purchase price; the per-pair COGS is derived
   as `total_cost / total_pairs` (`average_item_cost`). Additional, separate
   costs (freight, restoration, packaging, utilities) go in `expenses`.
3. **A pair can be sold to only one order at a time**, enforced in code by an
   atomic conditional update on `items.status` (not by a DB-unique index, so a
   pair may be re-sold after a cancel/release).
4. **Two independent axes on a pair:** *sale status* (`status`) and *physical
   processing* (`triage_status`). They never overwrite each other.
5. **Soft deletes** for `suppliers`, `batches`, `items`, `expenses` — deletions
   are recoverable and shown with an "Undo" action.
6. **Everything important is audited** in `audit_logs`.

---

## 2. Lifecycles

### 2.1 Order (`orders.status`)
```
reserved ──payment verified──▶ paid ──delivery completed──▶ fulfilled
   │
   └──cancel / reservation expired──▶ cancelled      (pair released to available)
```
- `reserved` — pairs are locked for a buyer; `expires_at` is set (default 120 min).
- `paid` — payment verified; pairs become `sold`; delivery is created.
- `fulfilled` — the delivery was completed.
- `cancelled` — released; pairs return to `available`. Paid/fulfilled orders
  **cannot** be cancelled.

### 2.2 Pair sale status (`items.status`)
```
available ──award/reserve──▶ reserved ──payment──▶ sold
     ▲                              │
     └──────── cancel / release ────┘
```
`Item.status` is the sale guard: only `available` pairs can be awarded.

### 2.3 Pair triage (`items.triage_status`)
```
washing ──▶ under_repair ──▶ available (Ready)
```
- New pairs default to **`washing`**.
- Only a pair that is `triage_status = available` (Ready) **and** `status =
  available` can be awarded/sold.
- Note the naming collision: `triage_status = 'available'` means **"Ready to
  sell"**, while `status = 'available'` means **"not claimed by any order"**.

### 2.4 Payment (`payments`)
- Exactly **one payment per order** (`order_id` is unique).
- `amount` **must equal** `orders.awarded_price` (to the centavo).
- `method` is `gcash` or `cash`.
- A `gcash` payment needs a `reference_no`, and that reference is **globally
  unique** across all GCash payments (anti-replay).
- Cash payments get a generated `reference_no` like `CASH-<order_number>`.

### 2.5 Delivery (`deliveries`)
- Exactly **one delivery per order** (`order_id` is unique).
- `method` is `pickup` or `jnt_delivery`.
- `status` is `pending → shipped → completed`.
- A J&T delivery **may** be saved without a waybill **only while `pending`**;
  once `shipped` or `completed`, `tracking_number` is required.
- Completing a delivery sets the order to `fulfilled`.
- Walk-in POS orders are fulfilled on the spot: delivery is `pickup` +
  `completed` immediately.

---

## 3. Money rules

| Rule | Formula / statement |
| :--- | :--- |
| Average pair cost (COGS) | `batches.total_cost / batches.total_pairs` |
| Price tier | Tier 1 `< ₱1,000` · Tier 2 `₱1,000–₱1,999.99` · Tier 3 `≥ ₱2,000` (derived from `listed_price` on save) |
| Profit per pair | `awarded_price − average_item_cost − repair_cost` |
| Batch net proceeds | `realized revenue − batch.total_cost − Σ(batch-linked expenses)` |
| Overall net balance | `(gross sales − total COGS) − Σ(all expenses)` |
| Revenue counts where | order `status` is `paid` or `fulfilled` only |

**Not intended:** recording the bale purchase itself as a batch-linked "Sack
Purchase" expense. That double-counts COGS (it is already in
`batches.total_cost`). "Sack Purchase" is only for genuine overhead that is not
already captured on a batch.

---

## 4. Table reference

### `users`
| Column | Type / constraint | Notes |
| :--- | :--- | :--- |
| `name` | string | |
| `email` | string, **unique** | login |
| `password` | string | hashed |
| `role` | enum `owner` \| `staff`, default `staff` | gate for owner-only routes |
| `contact_number` | string, nullable | |
| `is_active` | bool, default `true` | deactivated users are logged out by middleware |

### `suppliers`
| Column | Type / constraint |
| :--- | :--- |
| `name` | string |
| `contact_number` | string, nullable |
| `notes` | text, nullable |
| `deleted_at` | soft delete |

### `batches`
| Column | Type / constraint | Notes |
| :--- | :--- | :--- |
| `supplier_id` | FK → `suppliers` (cascade) | |
| `batch_code` | string(30), **unique** | e.g. `B04` |
| `date_acquired` | date | |
| `total_sacks` | unsigned int, default 1 | |
| `total_pairs` | unsigned int, default 24 | bale pair count, may exceed serialized items |
| `total_cost` | decimal | **the COGS basis** |
| `deleted_at` | soft delete | batches with linked pairs/expenses are protected from deletion |

### `items`
| Column | Type / constraint | Notes |
| :--- | :--- | :--- |
| `batch_id` | FK → `batches` (cascade) | |
| `sku` | string(50), **unique** | e.g. `B04-001` |
| `brand`, `model` | string | |
| `price_tier` | string, default `Tier 1` | auto-derived on save |
| `listed_price` | decimal | drives `price_tier` |
| `condition`, `size` | string | |
| `status` | enum `available` \| `reserved` \| `sold`, default `available` | sale guard |
| `triage_status` | string, default `available` | `washing` \| `under_repair` \| `available` (Ready) |
| `repair_cost` | decimal, default 0 | |
| `category` | string, nullable | |
| `deleted_at` | soft delete | only `available` pairs may be deleted |

### `customers`
| Column | Type / constraint |
| :--- | :--- |
| `name` | string |
| `messenger_contact` | string (indexed) |
| `phone` | string, nullable |
| `shipping_address` | text, nullable |

### `orders`
| Column | Type / constraint | Notes |
| :--- | :--- | :--- |
| `order_number` | string(50), **unique** | |
| `customer_id` | FK → `customers` (cascade) | |
| `staff_id` | FK → `users` (cascade) | who awarded it |
| `awarded_price` | decimal | sum of the pairs' awarded prices |
| `status` | enum `reserved` \| `paid` \| `fulfilled` \| `cancelled`, default `reserved` | |
| `order_type` | enum `live_stream` \| `walkin_pos`, default `live_stream` | |
| `date_awarded` | timestamp, default now | |
| `expires_at` | timestamp, nullable | reservation deadline |
| `notes` | text, nullable | |

### `order_items` (pivot)
| Column | Type / constraint | Notes |
| :--- | :--- | :--- |
| `order_id` | FK → `orders` (cascade) | |
| `item_id` | FK → `items` (cascade) | **not** DB-unique (re-sale allowed) |
| `awarded_price` | decimal | per-pair agreed price |

### `payments`
| Column | Type / constraint | Notes |
| :--- | :--- | :--- |
| `order_id` | FK → `orders`, **unique** (cascade) | one payment per order |
| `amount` | decimal | must equal `orders.awarded_price` |
| `method` | enum `gcash` \| `cash` | |
| `reference_no` | string(100), nullable | required + globally unique for GCash |
| `verified_by` | FK → `users` (cascade) | |
| `date_paid` | timestamp, default now | |

### `deliveries`
| Column | Type / constraint | Notes |
| :--- | :--- | :--- |
| `order_id` | FK → `orders`, **unique** (cascade) | one delivery per order |
| `method` | enum `pickup` \| `jnt_delivery`, default `pickup` | |
| `tracking_number` | string(100), nullable | required for J&T once not `pending` |
| `status` | enum `pending` \| `shipped` \| `completed`, default `pending` | |
| `date_completed` | timestamp, nullable | |

### `expenses`
| Column | Type / constraint | Notes |
| :--- | :--- | :--- |
| `batch_id` | FK → `batches`, nullable, null-on-delete | optional link to a batch |
| `category` | string(100) | Shop / batch cost type |
| `description` | string(255) | |
| `reference_no` | string(100), nullable | receipt no. |
| `amount` | decimal | |
| `date` | date | |
| `deleted_at` | soft delete | |

### `audit_logs`
| Column | Type / constraint | Notes |
| :--- | :--- | :--- |
| `user_id` | FK → `users`, nullable, null-on-delete | actor |
| `action` | string(100) | e.g. `order_awarded` |
| `auditable_type` / `auditable_id` | string / bigint, nullable | subject |
| `details` | json, nullable | before/after context |
| `ip_address` | string(45), nullable | |

---

## 5. Intended vs not intended — quick checklist

**Intended**
- A pair's `status` and `triage_status` both considered before selling (Ready + available).
- Bale price on `batches.total_cost`; extra costs in `expenses`.
- GCash `reference_no` unique; payment `amount` == order total.
- J&T without a waybill only while `pending`.
- Walk-in POS → order `fulfilled`, delivery `pickup` + `completed`.
- Soft-deleted records recoverable; audit trail for sensitive actions.

**Not intended**
- A batch-linked "Sack Purchase" expense duplicating `batches.total_cost`.
- Two GCash payments sharing a reference.
- A payment amount different from the order total.
- A J&T delivery shipped/completed without a tracking number.
- An order with more than one payment or more than one delivery.
- Sold pairs whose order is not `paid`/`fulfilled`.
- Deleting a batch that still has pairs/expenses, or a non-available pair.

---

## 6. Seeder expectations (`DatabaseSeeder`)

The demo dataset is meant to look like a real, coherent shop:

- 2 users (1 owner, 1 staff), 2 suppliers, 2 batches.
- The full triage pipeline appears (some `washing`, some `under_repair`, some Ready).
- Some pairs `sold` (all linked to `paid`/`fulfilled` orders), some `reserved`
  (linked to `reserved` orders), the rest `available`.
- Payments match their order totals; walk-in POS orders use pickup/completed.
- J&T deliveries that are shipped carry a waybill.
- One **cancelled** order whose pair was released back to `available`.
- **No** batch-linked "Sack Purchase" expense (see §3).
- Audit log populated with the actions that produced the data.
