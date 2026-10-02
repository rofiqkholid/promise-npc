<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashedId;

class ProductHistoryProblem extends Model
{
    use HasHashedId;

    use HasFactory;

    protected $table = 'npc_product_history_problems';

    protected $fillable = [
        'product_id',
        'problem_description',
        'npc_part_id_finder',
        'created_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function partFinder()
    {
        return $this->belongsTo(NpcPart::class, 'npc_part_id_finder');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function clearPartHistoryProblemsOnRollback($part)
    {
        if (!$part) return;

        // 1. Get problem descriptions created during THIS part/PO
        $hpList = self::where('npc_part_id_finder', $part->id)->get();
        $descriptions = $hpList->pluck('problem_description')->toArray();

        // 2. Delete ProductHistoryProblem created during THIS part/PO
        self::where('npc_part_id_finder', $part->id)->delete();

        // 3. Clean up NpcChecksheetDetail for history problems created on this part/PO
        if ($part->checksheet) {
            if (!empty($descriptions)) {
                foreach ($descriptions as $desc) {
                    $cleanDesc = strtolower(trim($desc));
                    $part->checksheet->details()->get()->each(function($detail) use ($cleanDesc) {
                        $dClean = strtolower(trim(preg_replace('/^\[.*?\]\s*/', '', $detail->point_check)));
                        if ($dClean === $cleanDesc) {
                            $detail->delete();
                        }
                    });
                }
            }

            // Also delete any history detail created in this checksheet that does NOT belong to a previous PO
            $part->checksheet->details()->get()->each(function($detail) use ($part) {
                $pcLow = strtolower(trim($detail->point_check));
                if (str_contains($pcLow, 'history') || str_contains($pcLow, 'problem') || str_starts_with($detail->point_check, '[')) {
                    $cleanDesc = strtolower(trim(preg_replace('/^\[.*?\]\s*/', '', $detail->point_check)));
                    $isFromPreviousPo = self::where('product_id', optional($part->product)->id)
                        ->where('npc_part_id_finder', '!=', $part->id)
                        ->whereRaw('LOWER(problem_description) = ?', [$cleanDesc])
                        ->exists();

                    if (!$isFromPreviousPo) {
                        $detail->delete();
                    }
                }
            });
        }
    }
}
