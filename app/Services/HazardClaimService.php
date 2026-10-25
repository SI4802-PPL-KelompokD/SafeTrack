<?php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class HazardClaimService {
 // All response/verification services must call this inside their transaction.
 // Claim + audit commit together. Another admin cannot replace the owner.
 public function claim(int $reportId, User $actor): void {
  if(!$actor->is_active || $actor->trashed() || !in_array($actor->role,['admin','super_admin'],true)){abort(403);}
  DB::transaction(function()use($reportId,$actor){
   $report=DB::table('hazard_reports')->where('id',$reportId)->whereNull('deleted_at')->lockForUpdate()->first();
   abort_unless($report,404);
   if($report->assigned_admin_id && $report->assigned_admin_id!=$actor->id){
    throw ValidationException::withMessages(['report'=>'Laporan sudah ditangani admin lain.']);
   }
   if(!$report->assigned_admin_id){
    DB::table('hazard_reports')->where('id',$reportId)->update(['assigned_admin_id'=>$actor->id,'updated_at'=>now()]);
    DB::table('audit_logs')->insert(['actor_id'=>$actor->id,'action'=>'claim','entity_type'=>'hazard_report','entity_id'=>$reportId,'before_values'=>json_encode(['assigned_admin_id'=>null]),'after_values'=>json_encode(['assigned_admin_id'=>$actor->id]),'created_at'=>now(),'updated_at'=>now()]);
   }
  },3);
 }
}
