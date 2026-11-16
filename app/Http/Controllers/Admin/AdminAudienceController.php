<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoterConversion;
use App\Models\PromoterProfile;
use App\Models\PromoterVisit;
use App\Models\WaitlistEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAudienceController extends Controller
{
    public function index(Request $request): View
    {
        $waitlist = WaitlistEntry::query()
            ->latest('joined_at')
            ->paginate(25, ['*'], 'waitlist_page');
        $promoters = PromoterProfile::query()
            ->withCount(['visits', 'conversions'])
            ->latest()
            ->paginate(25, ['*'], 'promoter_page');

        return view('admin.audience.index', [
            'waitlist' => $waitlist,
            'promoters' => $promoters,
            'waitlistCount' => WaitlistEntry::query()->count(),
            'promoterCount' => PromoterProfile::query()->count(),
            'visitCount' => PromoterVisit::query()->count(),
            'conversionCount' => PromoterConversion::query()->count(),
        ]);
    }
}
