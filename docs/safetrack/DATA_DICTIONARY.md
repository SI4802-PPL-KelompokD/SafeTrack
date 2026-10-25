# Kamus data SafeTrack

Sumber: migrations Laravel. ID bigint unsigned; seluruh tabel domain punya id, created_at, updated_at. Waktu disimpan UTC; tampilkan Asia/Jakarta.

## companies

```php
$t->string('code',30)->unique();
$t->string('name',255);
$t->boolean('is_active')->default(true);
```

## departments

```php
$t->foreignId('company_id')->constrained('companies')->restrictOnDelete();
$t->string('code',30);
$t->string('name',255);
$t->boolean('is_hse')->default(false);
$t->boolean('is_active')->default(true);
$t->unique(['company_id','code']);
```

## employment_statuses

```php
$t->string('code',30)->unique();
$t->string('name',255);
```

## employee_registry

```php
$t->string('nik',40)->unique();
$t->string('full_name',255);
$t->foreignId('department_id')->constrained('departments')->restrictOnDelete();
$t->foreignId('employment_status_id')->constrained('employment_statuses')->restrictOnDelete();
$t->boolean('is_active')->default(true);
```

## hazard_categories

```php
$t->string('code',30)->unique();
$t->string('name',255);
$t->boolean('is_active')->default(true);
```

## shifts

```php
$t->string('code',30)->unique();
$t->string('name',255);
$t->time('starts_at');
$t->time('ends_at');
$t->boolean('is_active')->default(true);
```

## work_areas

```php
$t->foreignId('department_id')->constrained('departments')->restrictOnDelete();
$t->string('name',255);
$t->boolean('is_active')->default(true);
$t->unique(['department_id','name']);
```

## inspection_questions

```php
$t->foreignId('work_area_id')->constrained('work_areas')->restrictOnDelete();
$t->text('question');
$t->unsignedInteger('sort_order')->default(0);
$t->boolean('is_active')->default(true);
```

## hazard_reports

```php
$t->uuid('client_request_id')->unique();
$t->foreignId('reporter_id')->constrained('users')->restrictOnDelete();
$t->foreignId('reporter_department_id')->constrained('departments')->restrictOnDelete();
$t->foreignId('repair_department_id')->nullable()->constrained('departments')->restrictOnDelete();
$t->foreignId('category_id')->constrained('hazard_categories')->restrictOnDelete();
$t->foreignId('work_area_id')->nullable()->constrained('work_areas')->restrictOnDelete();
$t->string('title',200);
$t->string('location',255);
$t->text('description');
$t->text('immediate_action')->nullable();
$t->enum('risk_level',["low", "medium", "high"]);
$t->enum('status',["pending_verification", "rejected", "open", "in_progress", "closed"])->default('pending_verification');
$t->foreignId('assigned_admin_id')->nullable()->constrained('users')->restrictOnDelete();
$t->foreignId('repair_pic_id')->nullable()->constrained('users')->restrictOnDelete();
$t->foreignId('verified_by')->nullable()->constrained('users')->restrictOnDelete();
$t->dateTime('verified_at')->nullable();
$t->text('verification_notes')->nullable();
$t->dateTime('reported_at');
$t->dateTime('closed_at')->nullable();
$t->foreignId('deleted_by')->nullable()->constrained('users')->restrictOnDelete();
$t->text('deletion_reason')->nullable();
$t->softDeletes();
$t->index(['status','reported_at']);
$t->index(['reporter_department_id','reported_at']);
$t->index(['reporter_id','reported_at']);
```

## hazard_resolutions

```php
$t->foreignId('hazard_report_id')->constrained('hazard_reports')->restrictOnDelete();
$t->foreignId('submitted_by')->constrained('users')->restrictOnDelete();
$t->foreignId('department_id')->constrained('departments')->restrictOnDelete();
$t->uuid('client_request_id')->unique();
$t->text('description');
$t->enum('status',["pending", "accepted", "rejected"])->default('pending');
$t->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
$t->dateTime('reviewed_at')->nullable();
$t->text('review_notes')->nullable();
$t->dateTime('submitted_at');
```

## hazard_status_histories

```php
$t->foreignId('hazard_report_id')->constrained('hazard_reports')->restrictOnDelete();
$t->string('from_status',40)->nullable();
$t->string('to_status',40);
$t->foreignId('actor_id')->constrained('users')->restrictOnDelete();
$t->text('reason')->nullable();
$t->index(['hazard_report_id','created_at']);
```

## daily_inspections

```php
$t->uuid('client_request_id')->unique();
$t->foreignId('inspector_id')->constrained('users')->restrictOnDelete();
$t->foreignId('department_id')->constrained('departments')->restrictOnDelete();
$t->foreignId('work_area_id')->constrained('work_areas')->restrictOnDelete();
$t->foreignId('shift_id')->constrained('shifts')->restrictOnDelete();
$t->date('inspection_date');
$t->enum('status',["submitted", "reviewed", "closed"])->default('submitted');
$t->text('notes')->nullable();
$t->foreignId('reviewed_by')->nullable()->constrained('users')->restrictOnDelete();
$t->dateTime('reviewed_at')->nullable();
$t->text('review_notes')->nullable();
$t->index(['inspector_id','inspection_date']);
$t->index(['work_area_id','inspection_date','shift_id']);
```

## inspection_answers

```php
$t->foreignId('daily_inspection_id')->constrained('daily_inspections')->restrictOnDelete();
$t->foreignId('inspection_question_id')->constrained('inspection_questions')->restrictOnDelete();
$t->text('question_snapshot');
$t->enum('answer',["yes", "no", "na"]);
$t->text('notes')->nullable();
$t->unique(['daily_inspection_id','inspection_question_id'],'inspection_answer_question_unique');
```

## inspection_findings

```php
$t->foreignId('inspection_answer_id')->constrained('inspection_answers')->restrictOnDelete();
$t->foreignId('pic_id')->nullable()->constrained('users')->restrictOnDelete();
$t->text('description');
$t->enum('status',["open", "in_progress", "closed"])->default('open');
$t->date('due_date')->nullable();
$t->text('corrective_action')->nullable();
$t->foreignId('closed_by')->nullable()->constrained('users')->restrictOnDelete();
$t->dateTime('closed_at')->nullable();
```

## safety_talks

```php
$t->uuid('client_request_id')->unique();
$t->foreignId('created_by')->constrained('users')->restrictOnDelete();
$t->foreignId('department_id')->nullable()->constrained('departments')->restrictOnDelete();
$t->string('topic',200);
$t->longText('content');
$t->dateTime('held_at')->nullable();
$t->enum('status',["draft", "published", "archived"])->default('draft');
$t->dateTime('published_at')->nullable();
$t->foreignId('verified_by')->nullable()->constrained('users')->restrictOnDelete();
$t->dateTime('verified_at')->nullable();
$t->index(['status','published_at']);
```

## safety_talk_reads

```php
$t->foreignId('safety_talk_id')->constrained('safety_talks')->restrictOnDelete();
$t->foreignId('user_id')->constrained('users')->restrictOnDelete();
$t->dateTime('read_at');
$t->unique(['safety_talk_id','user_id']);
```

## safety_talk_attendees

```php
$t->foreignId('safety_talk_id')->constrained('safety_talks')->restrictOnDelete();
$t->foreignId('user_id')->constrained('users')->restrictOnDelete();
$t->unique(['safety_talk_id','user_id']);
```

## hazard_photos

```php
$t->foreignId('hazard_report_id')->constrained('hazard_reports')->restrictOnDelete();
$t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
$t->string('disk',40)->default('local');
$t->string('path',500);
$t->string('original_name',255);
$t->string('mime_type',100);
$t->unsignedBigInteger('size_bytes');
```

## resolution_photos

```php
$t->foreignId('hazard_resolution_id')->constrained('hazard_resolutions')->restrictOnDelete();
$t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
$t->string('disk',40)->default('local');
$t->string('path',500);
$t->string('original_name',255);
$t->string('mime_type',100);
$t->unsignedBigInteger('size_bytes');
```

## inspection_photos

```php
$t->foreignId('inspection_answer_id')->constrained('inspection_answers')->restrictOnDelete();
$t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
$t->string('disk',40)->default('local');
$t->string('path',500);
$t->string('original_name',255);
$t->string('mime_type',100);
$t->unsignedBigInteger('size_bytes');
```

## finding_photos

```php
$t->foreignId('inspection_finding_id')->constrained('inspection_findings')->restrictOnDelete();
$t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
$t->string('disk',40)->default('local');
$t->string('path',500);
$t->string('original_name',255);
$t->string('mime_type',100);
$t->unsignedBigInteger('size_bytes');
```

## safety_talk_attachments

```php
$t->foreignId('safety_talk_id')->constrained('safety_talks')->restrictOnDelete();
$t->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
$t->string('disk',40)->default('local');
$t->string('path',500);
$t->string('original_name',255);
$t->string('mime_type',100);
$t->unsignedBigInteger('size_bytes');
```

## sap_point_rules

```php
$t->string('activity',40);
$t->unsignedInteger('points');
$t->date('effective_from');
$t->date('effective_until')->nullable();
$t->unique(['activity','effective_from']);
```

## sap_targets

```php
$t->foreignId('user_id')->constrained('users')->restrictOnDelete();
$t->date('period');
$t->string('activity',40);
$t->unsignedInteger('target_count');
$t->foreignId('created_by')->constrained('users')->restrictOnDelete();
$t->unique(['user_id','period','activity']);
```

## sap_entries

```php
$t->foreignId('user_id')->constrained('users')->restrictOnDelete();
$t->foreignId('point_rule_id')->constrained('sap_point_rules')->restrictOnDelete();
$t->string('activity',40);
$t->unsignedBigInteger('source_id');
$t->integer('points');
$t->dateTime('earned_at');
$t->date('period');
$t->boolean('is_void')->default(false);
$t->text('void_reason')->nullable();
$t->unique(['activity','source_id','user_id']);
$t->index(['user_id','period','is_void']);
```

## sap_monthly_summaries

```php
$t->foreignId('user_id')->constrained('users')->restrictOnDelete();
$t->date('period');
$t->foreignId('department_id')->constrained('departments')->restrictOnDelete();
$t->boolean('is_hse_snapshot');
$t->unsignedInteger('hazard_count')->default(0);
$t->unsignedInteger('inspection_count')->default(0);
$t->unsignedInteger('resolution_count')->default(0);
$t->unsignedInteger('safety_talk_count')->default(0);
$t->integer('total_points')->default(0);
$t->boolean('target_met')->nullable();
$t->dateTime('calculated_at');
$t->unique(['user_id','period']);
```

## rewards

```php
$t->string('name',255);
$t->unsignedInteger('required_points');
$t->text('description')->nullable();
$t->boolean('is_active')->default(true);
```

## reward_redemptions

```php
$t->foreignId('user_id')->constrained('users')->restrictOnDelete();
$t->foreignId('reward_id')->constrained('rewards')->restrictOnDelete();
$t->unsignedInteger('points_spent');
$t->enum('status',["requested", "approved", "rejected", "delivered"])->default('requested');
$t->foreignId('processed_by')->nullable()->constrained('users')->restrictOnDelete();
$t->dateTime('processed_at')->nullable();
$t->text('notes')->nullable();
```

## audit_logs

```php
$t->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete();
$t->string('action',60);
$t->string('entity_type',100);
$t->unsignedBigInteger('entity_id');
$t->json('before_values')->nullable();
$t->json('after_values')->nullable();
$t->string('ip_address',45)->nullable();
$t->text('user_agent')->nullable();
$t->index(['entity_type','entity_id','created_at']);
```

## users dan tabel Laravel

users: employee_registry_id (unique FK), department_id (FK), username/email (unique), name, password (hash), role [super_admin,admin,employee], is_active, profile_photo_path, email_verified_at, remember_token, deleted_by, deletion_reason, deleted_at, timestamps. super_admin_slot adalah generated column dengan unique index; jangan ditulis aplikasi.

Tabel bawaan: password_reset_tokens, sessions, cache, cache_locks, jobs, job_batches, failed_jobs. notifications mengikuti skema database notification Laravel. Semua ditentukan dalam migration masing-masing.
