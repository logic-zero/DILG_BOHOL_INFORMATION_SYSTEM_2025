<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use App\Models\Home_Image;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\Knowledge_Materials;
use App\Models\News;
use App\Models\PDMUs;
use App\Models\Lgu;
use App\Models\Faq;
use App\Models\Field_Officer;
use App\Models\Bohol_Issuance;
use App\Models\Provincial_Official;
use App\Models\Citizens_Charter;
use App\Models\User;
use App\Models\PageVisit;
use App\Models\Oragnizational_Structure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = PageVisit::whereDate('created_at', today())->count();
        $thisWeek = PageVisit::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $thisMonth = PageVisit::whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count();
        $total = PageVisit::count();

        $lastThirtyDays = PageVisit::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        foreach ($lastThirtyDays as $day) {
            $labels[] = Carbon::parse($day->date)->format('M d');
            $data[] = $day->count;
        }

        return Inertia::render('Admin/Dashboard', [
            'newsStats' => [
                'approved' => News::where('status', 1)->count(),
                'pending' => News::where('status', 0)->count(),
                'total' => News::count(),
            ],
            'organizational_structure' => Oragnizational_Structure::count(),
            'pdmu' => PDMUs::count(),
            'field_officers' => Field_Officer::count(),
            'lgu' => Lgu::count(),
            'faq' => Faq::count(),
            'issuances' => Bohol_Issuance::count(),
            'prov_officials' => Provincial_Official::count(),
            'citizens_charter' => Citizens_Charter::count(),
            'users' => User::count(),
            'knowledge_materials' => Knowledge_Materials::count(),
            'jobs' => Job::count(),
            'pageVisits' => [
                'today' => $today,
                'thisWeek' => $thisWeek,
                'thisMonth' => $thisMonth,
                'total' => $total,
                'graph' => [
                    'labels' => $labels,
                    'data' => $data,
                ],
            ],
        ]);
    }

    public function storeImage(Request $request)
    {
        $validated = $request->validate([
            'images' => 'required|array|min:1|max:3',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $uploadPath = public_path('home_images');
        File::ensureDirectoryExists($uploadPath);

        $existingImages = Home_Image::all();
        if ($existingImages->isNotEmpty()) {
            foreach ($existingImages as $image) {
                $storedImages = json_decode($image->images, true);
                foreach ($storedImages as $storedImage) {
                    $filePath = public_path('home_images/' . $storedImage);
                    if (File::exists($filePath)) {
                        File::delete($filePath);
                    }
                }
            }
            Home_Image::truncate();
        }

        $imageNames = [];
        foreach ($request->file('images') as $image) {
            $fileName = Str::random(20) . '.' . $image->extension();
            $image->move($uploadPath, $fileName);
            $imageNames[] = $fileName;
        }

        Home_Image::create([
            'images' => json_encode($imageNames),
        ]);

        return redirect()->back()->with('success', 'Home images updated successfully.');
    }

    public function storeAudio(Request $request)
    {
        $validated = $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,aac|max:10240',
        ]);

        $uploadPath = public_path('audios');
        File::ensureDirectoryExists($uploadPath);

        $existingAudio = Audio::first();

        if ($existingAudio) {
            $filePath = public_path('audios/' . $existingAudio->file);
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
            $existingAudio->delete();
        }

        $fileName = Str::random(20) . '.' . $request->file('audio')->extension();
        $request->file('audio')->move($uploadPath, $fileName);

        Audio::create([
            'file' => $fileName,
        ]);

        return redirect()->back()->with('success', 'Audio updated successfully.');
    }
}
