# Build Conventions (READ FIRST)

This is a Laravel 13 multi-tenant Umroh/Haji travel app. Follow these conventions EXACTLY so every module matches. Use the **Hotel module** as the reference implementation:
- Controller: `app/Http/Controllers/HotelController.php`
- Request: `app/Http/Requests/Hotel/HotelRequest.php`
- Views: `resources/views/hotels/{index,create,edit,_form}.blade.php`

## Stack / structure
- PHP 8.3+, Laravel 13. Streamlined skeleton (no Kernel.php; middleware aliases in `bootstrap/app.php`).
- Blade + Tailwind v3 (PostCSS) + Alpine.js. Chart.js + SweetAlert2 bundled in `resources/js/app.js`.
- DB: MySQL (MAMP, port 8889). Indonesian locale.

## Enums (app/Enums) — all use trait `HasEnumHelpers` giving `options()`, `values()`, `labelFor()`, `colorFor()`. Each has `label()` and `badgeColor()`.
`Role`(super_admin,admin,agent,pilgrim,guide), `PackageType`, `PackageStatus`, `RegistrationStatus`, `PaymentType`, `PaymentStatus`, `DocumentStatus`, `AttendanceStatus`, `EquipmentStatus`, `CommissionStatus`, `Gender`, `AnnouncementAudience`.

## Services (app/Services, all singletons)
- `BranchContext` — `scope(Builder $q, 'branch_id')` applies branch isolation; `currentBranchId()`, `currentBranch()`, `selectableBranches()`, `allowedBranchIds()` (null=all for super_admin), `isCrossBranch()`.
- `AccessContext` — `registrations()`, `pilgrims()` return role-scoped query Builders; `agentId()`, `guideId()`, `pilgrimId()`.
- `FileVault` — `store(UploadedFile $f, string $dir): array{path,original_name,mime_type,size}` to PRIVATE disk, blocks svg/php/exe/js. `download($path,$name)`, `delete($path)`, `exists($path)`. Allowed: jpg,jpeg,png,webp,pdf.
- `ActivityLogService` — `created($model,$desc)`, `updated(...)`, `deleted(...)`, `log($action,$desc,$subject=null,$props=[])`.
- `AccountProvisioner` — `provision(Role $role, string $name, ?string $email, ?int $branchId, ?string $phone=null, ?string $password=null): array{user:User, password:?string}`. Use inside a DB::transaction when "Buatkan akun login" is checked. Put generated password in `->with('generated_password', $pw)`.
- `SettingService` — `setting('key')` global helper.

## User model role helpers (null-safe): `isStaff()` (super_admin|admin), `isSuperAdmin()`, `isAdmin()`, `isAgent()`, `isPilgrim()`, `isGuide()`, `roleLabel()`, relations `branch()`, `pilgrim()`, `agent()`, `guide()`.

## Policies — registered in `AppServiceProvider::POLICIES`. Use `$this->authorize('ability', $model)` / `Gate::allows()` / `@can`. Branch trait `ChecksBranchAccess::sameBranch($user,$branchId)`.

## Global helpers (app/Helpers/helpers.php)
`currency($n)` → "Rp 1.000.000", `terbilang($n)`, `format_date($d,'d M Y')`, `format_datetime($d)`, `wa_link($phone,$text=null)`, `setting($key,$default)`, `percent($part,$total)`, `initials($name)`. ALL null-safe.

## Blade components (resources/views/components)
- `<x-app-layout>` with optional `<x-slot name="header">`. Pushes scripts via `@push('scripts')` (layout has `@stack('scripts')`).
- `<x-page-header title subtitle>` + `<x-slot name="actions">`.
- `<x-breadcrumb :items="[['label'=>..,'route'=>..(optional)]]" />`
- `<x-card title subtitle padding>` + optional `<x-slot name="actions">`.
- `<x-stats-card label value icon color sub>` colors: teal,blue,green,amber,purple,red. icons below.
- `<x-table :headers="[...]">` then `<tr>`s.
- `<x-badge color>` colors: gray,green,red,amber,blue,purple,teal,pink. Use `$enum?->badgeColor() ?? 'gray'`.
- `<x-input name label type value required hint class>` (type textarea/color/file/number/date supported; handles old() + @error).
- `<x-select name label :options="[val=>label]" :selected required placeholder hint>`.
- `<x-empty-state icon title message>` + optional `<x-slot name="action">`.
- `<x-action-link :href icon variant>` variants: primary,secondary,ghost,danger.
- `<x-delete-form :action :confirm :label>` (SweetAlert confirm via data-confirm, sends DELETE).
- `<x-chart id type :data :options :height>` — Chart.js. data/options are PHP arrays → JSON.
- `<x-icon name class>` icons: dashboard,package,hotel,plane,bank,users,user,guide,doc,cash,calendar,list,bus,box,percent,megaphone,building,cog,history,logout,chevron,check,download,dot.

## Controller pattern (MANDATORY per method)
1. `$this->authorize('ability', $modelOrClass)` (or rely on Policy).
2. Validate via a Form Request in `app/Http/Requests/<Module>/`.
3. Use `ActivityLogService` on create/update/delete.
4. Branch-scope every index list with `BranchContext::scope()` or `AccessContext`.
5. Eager-load relations to avoid N+1 (`->with([...])`, `withCount`).
6. Redirect with `->with('success', '...')`. NO inline validation without a Form Request.
7. Index must be null-safe and handle empty data with `<x-empty-state>`.

## Form Request pattern
`authorize()`: if `$this->route('model')` instanceof Model → `Gate::allows('update',$m)` else `Gate::allows('create', Model::class)`. Convert checkboxes in `passedValidation()` with `$this->boolean('is_active')`.

## Routes
Add to `routes/modules.php` inside the `auth` group. Use `Route::resource(...)->whereNumber('param')->middleware('role:...')`. DO NOT put `/create` collisions — `whereNumber` on the `{model}` param prevents `/create` being caught by `/{model}`. Name routes by resource (e.g. `agents.index`). The sidebar already references these route names: branches, packages, hotels, airlines, bank-accounts, agents, guides, pilgrims, registrations, documents, payments, manasik, manifests, equipment, commissions, announcements, users, activity-logs.

## Null-safety (CRITICAL — audit requires zero 500s on empty/null data)
- Always `$model->relation?->field ?? '-'`, `optional()`, or guard.
- Enum access: `$m->status?->label() ?? '-'`, `$m->status?->badgeColor() ?? 'gray'`.
- Views must render with empty collections (use `@forelse ... @empty <x-empty-state/>`).
- A user may have NO linked profile (agent/guide/pilgrim) — handle gracefully.

## Money: store decimals as decimal:2 casts. Use `currency()` to display. Quota/booking must use `DB::transaction` + `lockForUpdate()`.
