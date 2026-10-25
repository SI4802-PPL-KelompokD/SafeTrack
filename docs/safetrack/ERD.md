# Relasi database SafeTrack

Diagram ringkas per modul. Lihat migrations untuk seluruh foreign key dan atribut. Relasi user berulang diringkas agar terbaca.

## Identitas

```mermaid
erDiagram
 companies ||--o{ departments : memiliki
 departments ||--o{ employee_registry : mendaftarkan
 employment_statuses ||--o{ employee_registry : menggolongkan
 employee_registry ||--o| users : registrasi
 departments ||--o{ users : departemen_saat_ini
```

## Hazard

```mermaid
erDiagram
 users ||--o{ hazard_reports : melaporkan
 hazard_categories ||--o{ hazard_reports : kategori
 hazard_reports ||--o{ hazard_resolutions : diajukan
 hazard_reports ||--o{ hazard_status_histories : riwayat
 hazard_reports ||--o{ hazard_photos : bukti
 hazard_resolutions ||--o{ resolution_photos : bukti_perbaikan
```

## Inspeksi

```mermaid
erDiagram
 work_areas ||--o{ inspection_questions : checklist
 work_areas ||--o{ daily_inspections : diperiksa
 shifts ||--o{ daily_inspections : shift
 daily_inspections ||--o{ inspection_answers : jawaban
 inspection_questions ||--o{ inspection_answers : referensi
 inspection_answers ||--o{ inspection_findings : temuan
 inspection_answers ||--o{ inspection_photos : foto
 inspection_findings ||--o{ finding_photos : perbaikan
```

## Safety Talk dan SAP

```mermaid
erDiagram
 safety_talks ||--o{ safety_talk_reads : dibaca
 safety_talks ||--o{ safety_talk_attendees : dihadiri
 safety_talks ||--o{ safety_talk_attachments : lampiran
 users ||--o{ safety_talk_reads : pembaca
 users ||--o{ sap_targets : target
 users ||--o{ sap_entries : memperoleh
 sap_point_rules ||--o{ sap_entries : aturan
 users ||--o{ sap_monthly_summaries : rekap
 users ||--o{ reward_redemptions : menukar
 rewards ||--o{ reward_redemptions : hadiah
```

Notifications memakai struktur polymorphic bawaan Laravel. audit_logs menyimpan jenis/ID entitas serta snapshot sebelum/sesudah. sap_entries memakai activity/source_id untuk sumber lintas modul: integritasnya diperiksa service, bukan FK langsung.
