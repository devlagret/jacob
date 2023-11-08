<?php 
namespace App\Helpers;

use App\Models\CoreMember;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppHelper {
    /**
     * Get ascociative member data for selct
     * set strict to 1 if want filter aplied to central office
     * @param integer $filter
     * @param integer $strict
     * @return Collection
     */
    public static function member($filter=1,$strict = 0) {
        $member = CoreMember::select('member_id',DB::raw("CONCAT(member_no, ' - ', member_name) AS member_name"));
        if($filter&&(Auth::user()->branch_id!==0||$strict)){
            $member->where('branch_id',Auth::user()->branch_id);
        }
        return $member->get()->pluck('member_name','member_id');
    }
}
