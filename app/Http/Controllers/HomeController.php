<?php

namespace App\Http\Controllers;

use App\Helper\TokenHelper;
use App\Models\Ebook;
use App\Models\Era;
use App\Models\JawabanUser;
use App\Models\Katalog;
use App\Models\LogAktivitas;
use App\Models\Materi;
use App\Models\MuseumUserVisit;
use App\Models\RiwayatPengembang;
use App\Models\SitusPeninggalan;
use App\Models\User;
use App\Models\VideoPeninggalan;
use App\Models\VirtualMuseum;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $greeting = $this->getGreeting();
        $user = Auth::user();

        // Check if profile is complete
        $profileComplete = ! empty($user->phone_number) && ! empty($user->address) && ! empty($user->date_of_birth);

        // Get user progress stats
        $userLevel = $user->level_sekarang;
        $userProgress = $user->progress_level_sekarang;

        // Get completed materi count
        $completedMateri = Materi::all()->filter(function ($materi) use ($user) {
            return $materi->getLinearLevel() < $user->level_sekarang + 1;
        })->count();
        $totalMateri = Materi::count();

        return view('guest.home', compact('greeting', 'profileComplete', 'userLevel', 'userProgress', 'completedMateri', 'totalMateri'));
    }

    /**
     * Get dynamic greeting based on current time
     */
    private function getGreeting()
    {
        $hour = Carbon::now()->hour;

        if ($hour >= 5 && $hour < 12) {
            return __('app.good_morning');
        } elseif ($hour >= 12 && $hour < 17) {
            return __('app.good_afternoon');
        } elseif ($hour >= 17 && $hour < 21) {
            return __('app.good_evening');
        } else {
            return __('app.good_night');
        }
    }

    public function panduan(Request $request)
    {
        // Get all guides from language file except the title and description keys
        $allGuides = __('guides');
        $guides = [];

        // Filter out non-guide keys (app_guide, learn_how_to_use)
        foreach ($allGuides as $key => $guide) {
            if (is_array($guide) && isset($guide['id'], $guide['title'], $guide['description'], $guide['steps'])) {
                $guides[] = $guide;
            }
        }

        return view('guest.panduan', compact('guides'));
    }

    /**
     * Display leaderboard of users based on posttest scores
     */
    public function statistik()
    {
        // Get current user for highlighting
        $currentUser = Auth::user();

        // Get all users with their posttest scores (0-100 scale per material)
        $users = User::where('role', 'user')
            ->with(['jawabanUser' => function ($query) {
                $query->where('jenis', 'posttest');
            }])
            ->get()
            ->map(function ($user) {
                // Group posttest answers by materi_id
                $scoresByMateri = $user->jawabanUser->groupBy('materi_id');

                // Calculate average score (0-100) across all materials
                $totalScore = 0;
                $materiCount = $scoresByMateri->count();

                foreach ($scoresByMateri as $materiId => $answers) {
                    $totalQuestions = $answers->count();
                    $correctAnswers = $answers->where('benar', true)->count();
                    $materiScore = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
                    $totalScore += $materiScore;
                }

                // Calculate average score (0-100) for the user
                $averageScore = $materiCount > 0 ? $totalScore / $materiCount : 0;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_photo' => $user->profile_photo,
                    'total_score' => round($averageScore),
                    'materi_count' => $materiCount,
                ];
            })
            ->sortByDesc('total_score')
            ->values();

        // Add ranking to each user
        $users = $users->map(function ($user, $index) {
            $user['rank'] = $index + 1;

            return $user;
        });

        // Find current user's position
        $currentUserData = $users->firstWhere('id', $currentUser->id) ?? [
            'id' => $currentUser->id,
            'name' => $currentUser->name,
            'profile_photo' => $currentUser->profile_photo,
            'total_score' => 0,
            'rank' => 0,
        ];
        $currentUserRank = $currentUserData['rank'];
        $userScore = $currentUserData['total_score'];

        // Get top 10 users
        $topUsers = $users->take(10);

        return view('guest.statistik', compact('topUsers', 'currentUser', 'currentUserRank', 'userScore'));
    }

    public function pengaturan(Request $request)
    {
        return view('guest.pengaturan');
    }

    public function pengembang(Request $request)
    {
        $riwayatPengembang = RiwayatPengembang::orderBy('tahun', 'desc')->get();

        return view('guest.pengembang', compact('riwayatPengembang'));
    }

    public function arMarker(Request $request)
    {
        return view('guest.ar-marker');
    }

    /**
     * Display user's test results report
     */
    public function rapor()
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        // Get all materials with user's test results
        $materials = Materi::with(['era', 'pretest', 'posttest'])
            ->leftJoin('era', 'materi.era_id', '=', 'era.era_id')
            ->orderByRaw('CASE WHEN materi.era_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('era.urutan')
            ->orderBy('materi.bab')
            ->orderBy('materi.urutan')
            ->select('materi.*')
            ->get()
            ->map(function ($materi) use ($user) {
                // Get pretest results
                $pretest = JawabanUser::where('user_id', $user->id)
                    ->where('materi_id', $materi->materi_id)
                    ->where('jenis', 'pretest')
                    ->get();

                // Get posttest results
                $posttest = JawabanUser::where('user_id', $user->id)
                    ->where('materi_id', $materi->materi_id)
                    ->where('jenis', 'posttest')
                    ->get();

                // Calculate scores on 0-100 scale
                $pretestCorrect = $pretest->where('benar', true)->count();
                $pretestTotal = $materi->pretest->count();
                $posttestCorrect = $posttest->where('benar', true)->count();
                $posttestTotal = $materi->posttest->count();

                // Calculate percentage scores (0-100)
                $pretestScore = $pretestTotal > 0 ? round(($pretestCorrect / $pretestTotal) * 100) : 0;
                $posttestScore = $posttestTotal > 0 ? round(($posttestCorrect / $posttestTotal) * 100) : 0;

                return [
                    'id' => $materi->materi_id,
                    'judul' => $materi->judul,
                    'pretest' => [
                        'total' => $pretestTotal,
                        'correct' => $pretestCorrect,
                        'score' => $pretestScore,
                        'max_score' => 100, // Always 100 for percentage
                    ],
                    'posttest' => [
                        'total' => $posttestTotal,
                        'correct' => $posttestCorrect,
                        'score' => $posttestScore,
                        'max_score' => 100, // Always 100 for percentage
                    ],
                    'completion' => $posttestScore, // Completion is same as posttest percentage
                ];
            });

        // Total score is the sum of all posttest scores (0-300 for 3 materials)
        $totalScore = $materials->sum('posttest.score');
        $maxTotalScore = 100 * $materials->count(); // 100 per material
        $overallPercentage = $totalScore / $materials->count(); // Average percentage

        return view('guest.statistik.rapor', [
            'materials' => $materials,
            'totalScore' => $totalScore,
            'maxTotalScore' => $maxTotalScore,
            'overallPercentage' => $overallPercentage,
        ]);
    }

    public function maps(Request $request)
    {
        return view('guest.maps');
    }

    /**
     * E-Learning Index Page
     */
    public function kunjungiPeninggalan(Request $request)
    {
        $user = Auth::user();

        // Get all materis ordered by era then bab.
        $materis = Materi::with(['era', 'pretest', 'posttest', 'ebook', 'situsPeninggalan'])
            ->leftJoin('era', 'materi.era_id', '=', 'era.era_id')
            ->orderByRaw('CASE WHEN materi.era_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('era.urutan')
            ->orderBy('materi.bab')
            ->orderBy('materi.urutan')
            ->select('materi.*')
            ->get();

        // Tandai status tiap materi (completed, available, locked) berdasarkan level user
        $materis = $materis->map(function ($materi) use ($user) {
            $materiLevel = $materi->getLinearLevel();
            if ($materiLevel < $user->level_sekarang + 1) {
                $materi->is_completed = true;
                $materi->is_available = true;
            } elseif ($materiLevel == $user->level_sekarang + 1) {
                $materi->is_completed = false;
                $materi->is_available = true;
            } else {
                $materi->is_completed = false;
                $materi->is_available = env('APP_DEMO_MODE', false) ? true : false;
            }

            return $materi;
        });

        $totalMateri = $materis->count();
        $completedMateri = $materis->where('is_completed', true)->count();
        $progressPercentage = $totalMateri > 0 ? round(($completedMateri / $totalMateri) * 100) : 0;

        $nextMateri = $materis->first(function ($materi) {
            return $materi->is_available && ! $materi->is_completed;
        });

        $riwayatAktivitas = LogAktivitas::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });

        $eraCards = $materis->groupBy(function ($materi) {
            return $materi->era_id ?? 0;
        })->map(function ($eraMateris, $eraId) {
            $era = $eraMateris->first()->era;
            $totalMateriEra = $eraMateris->count();
            $completedMateriEra = $eraMateris->where('is_completed', true)->count();
            $availableMateriEra = $eraMateris->where('is_available', true)->count();

            return (object) [
                'era_id' => (int) $eraId,
                'era' => $era,
                'judul' => $era ? $era->nama : 'Materi Lainnya',
                'kode' => $era ? $era->kode : null,
                'rentang_waktu' => $era ? $era->rentang_waktu : null,
                'total_materi' => $totalMateriEra,
                'completed_materi' => $completedMateriEra,
                'progress_percentage' => $totalMateriEra > 0 ? round(($completedMateriEra / $totalMateriEra) * 100) : 0,
                'is_available' => $availableMateriEra > 0,
            ];
        })->values();

        return view('guest.elearning', compact(
            'eraCards',
            'progressPercentage',
            'nextMateri',
            'riwayatAktivitas'
        ));
    }

    /**
     * E-Learning Materi List by Era
     */
    public function kunjungiPeninggalanEra(Request $request, int $era_id)
    {
        $user = Auth::user();

        $era = $era_id === 0 ? null : Era::findOrFail($era_id);

        $materisQuery = Materi::with(['era', 'pretest', 'posttest', 'ebook', 'situsPeninggalan'])
            ->orderBy('bab')
            ->orderBy('urutan')
            ->orderBy('materi_id');

        if ($era_id === 0) {
            $materisQuery->whereNull('era_id');
        } else {
            $materisQuery->where('era_id', $era_id);
        }

        $materis = $materisQuery->get();

        // Tandai status tiap materi (completed, available, locked) berdasarkan level user.
        $materis = $materis->map(function ($materi) use ($user) {
            $materiLevel = $materi->getLinearLevel();
            if ($materiLevel < $user->level_sekarang + 1) {
                $materi->is_completed = true;
                $materi->is_available = true;
            } elseif ($materiLevel == $user->level_sekarang + 1) {
                $materi->is_completed = false;
                $materi->is_available = true;
            } else {
                $materi->is_completed = false;
                $materi->is_available = env('APP_DEMO_MODE', false) ? true : false;
            }

            return $materi;
        });

        $totalMateri = $materis->count();
        $completedMateri = $materis->where('is_completed', true)->count();
        $progressPercentage = $totalMateri > 0 ? round(($completedMateri / $totalMateri) * 100) : 0;

        return view('guest.elearning.era-materi', compact(
            'era',
            'era_id',
            'materis',
            'progressPercentage'
        ));
    }

    /**
     * E-Learning Materi Detail Page
     */
    public function elearningMateri(Request $request, $materi_id)
    {
        $userAuth = Auth::user();
        $user = User::findOrFail($userAuth->id);
        $materi = Materi::with(['era', 'pretest', 'posttest', 'ebook', 'situsPeninggalan'])->findOrFail($materi_id);

        // Cek status progress user pada materi ini
        $materiLevel = $materi->getLinearLevel();
        $isAvailable = ($materiLevel == $user->level_sekarang + 1);
        $isCompleted = ($materiLevel < $user->level_sekarang + 1);

        // Progress step: 1=pretest, 2=ebook, 3=museum, 4=posttest
        $progress = $user->progress_level_sekarang;

        // In demo mode, all tabs are open for exploration without tracking progress
        if (env('APP_DEMO_MODE', false)) {
            $pretest_completed = false;
            $ebook_available = true;
            $all_ebooks_read = false;
            $museum_available = true;
            $all_museums_visited = false;
            $posttest_available = true;
            $posttest_completed = false;
        } else {
            $pretest_completed = $isCompleted || $progress >= User::PRE_TEST;
            $ebook_available = $pretest_completed;
            $all_ebooks_read = $isCompleted || $progress >= User::EBOOK;
            $museum_available = $all_ebooks_read;
            $all_museums_visited = $isCompleted || $progress >= User::VIRTUAL_LIVING_MUSEUM;
            $posttest_available = $all_museums_visited;
            $posttest_completed = $isCompleted || $progress >= User::POST_TEST;
        }

        return view('guest.elearning.materi', compact(
            'materi',
            'pretest_completed',
            'ebook_available',
            'all_ebooks_read',
            'museum_available',
            'all_museums_visited',
            'posttest_available',
            'posttest_completed'
        ));
    }

    /**
     * E-Learning Pretest Page
     */
    public function elearningPretest(Request $request, $materi_id)
    {
        $user = Auth::user();
        $materi = Materi::with('pretest')->findOrFail($materi_id);

        // Check if pretest is already completed
        $isCompleted = $this->isPretestCompleted($user->id, $materi_id);

        $questions = $materi->pretest;
        $totalQuestions = $questions->count();

        // Get results if completed
        $score = 0;
        $correctAnswers = 0;

        if ($isCompleted) {
            $userAnswers = JawabanUser::where('user_id', $user->id)
                ->where('materi_id', $materi_id)
                ->where('jenis', 'pretest')
                ->get();

            $correctAnswers = $userAnswers->where('benar', true)->count();
            $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0;
        }

        return view('guest.elearning.pretest', compact(
            'materi',
            'questions',
            'totalQuestions',
            'isCompleted',
            'score',
            'correctAnswers'
        ));
    }

    /**
     * Check if pretest is completed
     */
    private function isPretestCompleted($user_id, $materi_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return false;
        }
        $user = User::find($user_id);
        if (! $user) {
            return false;
        }

        // Jika materi sudah lewat level user, pasti sudah selesai
        if ($materi->getLinearLevel() < $user->level_sekarang + 1) {
            return true;
        }

        // Jika progress user pada materi ini sudah mencapai atau melewati step pretest
        return $user->progress_level_sekarang >= User::PRE_TEST;
    }

    /**
     * Submit Pretest Answers
     */
    public function submitPretest(Request $request, $materi_id)
    {
        $userAuth = Auth::user();
        $user = User::findOrFail($userAuth->id);
        $materi = Materi::with('pretest')->findOrFail($materi_id);

        // Validate the request
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|in:A,B,C,D,E',
        ], [
            'answers.required' => 'Mohon jawab semua pertanyaan.',
            'answers.*.required' => 'Setiap pertanyaan harus dijawab.',
            'answers.*.in' => 'Jawaban harus berupa A, B, C, atau D.',
        ]);

        $answers = $request->input('answers', []);
        $correctCount = 0;
        $totalQuestions = $materi->pretest->count();

        foreach ($materi->pretest as $question) {
            $userAnswer = $answers[$question->pretest_id] ?? null;

            // Skip saving if no answer provided
            if ($userAnswer === null) {
                continue;
            }

            $isCorrect = $userAnswer === $question->jawaban_benar;

            if ($isCorrect) {
                $correctCount++;
            }

            // Save answer
            JawabanUser::create([
                'user_id' => $user->id,
                'materi_id' => $materi_id,
                'jenis' => 'pretest',
                'soal_id' => $question->pretest_id,
                'jawaban_user' => $userAnswer,
                'benar' => $isCorrect,
                'poin' => $isCorrect ? 10 : 0,
                'answered_at' => now(),
            ]);
        }

        // Update progress materi
        // Seharusnya ini akan selalu True untuk sekarang
        // karena pretest tidak bisa diulang
        // Skip in demo mode — exploration only, no progress tracking
        if (! env('APP_DEMO_MODE', false) && $materi->shouldIncrementProgress($user, 1)) {
            $user->incrementProgressLevel();
            $this->logActivity($user->id, "Menyelesaikan pretest {$materi->judul}");
        }

        return redirect()->route('guest.elearning.pretest', $materi_id)
            ->with('success', 'Pretest berhasil diselesaikan!');
    }

    /**
     * Log user activity
     */
    private function logActivity($user_id, $aktivitas)
    {
        LogAktivitas::create([
            'user_id' => $user_id,
            'aktivitas' => $aktivitas,
            'created_at' => now(),
        ]);
    }

    /**
     * E-Learning Posttest Page
     */
    public function elearningPosttest(Request $request, $materi_id)
    {
        $user = Auth::user();
        $materi = Materi::with('posttest')->findOrFail($materi_id);

        // Check if pretest is completed (requirement for posttest)
        if (! $this->isPretestCompleted($user->id, $materi_id)) {
            return redirect()->route('guest.elearning.materi', $materi_id)
                ->with('error', 'Selesaikan pretest terlebih dahulu.');
        }

        // Check if posttest is already completed (hanya true jika sudah ada jawaban user di DB)
        $userAnswers = JawabanUser::where('user_id', $user->id)
            ->where('materi_id', $materi_id)
            ->where('jenis', 'posttest')
            ->get();
        $isCompleted = $userAnswers->count() > 0;

        $questions = $materi->posttest;
        $totalQuestions = $questions->count();

        // Get results if completed
        $score = 0;
        $correctAnswers = 0;

        if ($isCompleted) {
            $correctAnswers = $userAnswers->where('benar', true)->count();
            $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0;
        }

        return view('guest.elearning.posttest', compact(
            'materi',
            'questions',
            'totalQuestions',
            'isCompleted',
            'score',
            'correctAnswers'
        ));
    }

    /**
     * Submit Posttest Answers
     */
    public function submitPosttest(Request $request, $materi_id)
    {
        $userAuth = Auth::user();
        $user = User::findOrFail($userAuth->id);
        $materi = Materi::with('posttest')->findOrFail($materi_id);

        // Validate the request
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|in:A,B,C,D,E',
        ], [
            'answers.required' => 'Mohon jawab semua pertanyaan.',
            'answers.*.required' => 'Setiap pertanyaan harus dijawab.',
            'answers.*.in' => 'Jawaban harus berupa A, B, C, atau D.',
        ]);

        $answers = $request->input('answers', []);
        $correctCount = 0;
        $totalQuestions = $materi->posttest->count();

        foreach ($materi->posttest as $question) {
            $userAnswer = $answers[$question->posttest_id] ?? null;

            // Skip saving if no answer provided
            if ($userAnswer === null) {
                continue;
            }

            $isCorrect = $userAnswer === $question->jawaban_benar;

            if ($isCorrect) {
                $correctCount++;
            }

            // Save answer
            JawabanUser::create([
                'user_id' => $user->id,
                'materi_id' => $materi_id,
                'jenis' => 'posttest',
                'soal_id' => $question->posttest_id,
                'jawaban_user' => $userAnswer,
                'benar' => $isCorrect,
                'poin' => $isCorrect ? 10 : 0,
                'answered_at' => now(),
            ]);
        }

        // Increment user progress if needed
        // Skip in demo mode — exploration only, no progress tracking
        if (! env('APP_DEMO_MODE', false) && $materi->shouldIncrementProgress($user, 4)) {
            $user->incrementProgressLevel();
            $this->logActivity($user->id, "Menyelesaikan posttest {$materi->judul}");
        }

        // Calculate score and correct answers for result display
        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;

        return redirect()->route('guest.elearning.posttest', $materi_id)
            ->with([
                'success' => 'Posttest berhasil diselesaikan!',
                'score' => $score,
                'correctAnswers' => $correctCount,
            ]);
    }

    /**
     * Situs Detail Page
     */
    public function situsDetail(Request $request, $situs_id)
    {
        $user = Auth::user();
        $situs = SitusPeninggalan::with(['virtualMuseum', 'virtualMuseumObject', 'materi'])->findOrFail($situs_id);

        // Determine if this situs is unlocked based on user's level
        $level = $user->level_sekarang;
        $isUnlocked = $situs->materi_id
            ? $situs->materi->urutan <= $level
            : true;

        return view('guest.situs.detail', [
            'situs' => $situs,
            'is_unlocked' => $isUnlocked
        ]);
    }

    /**
     * VR 360 Panorama Viewer
     */
    public function situsPanorama(Request $request, $situs_id)
    {
        $user = Auth::user();
        $situs = SitusPeninggalan::findOrFail($situs_id);

        // Log activity
        if ($user) {
            $this->logActivity($user->id, 'Memulai pengalaman VR 360 Panorama untuk situs: ' . $situs->nama);
        }

        return view('guest.panorama.viewer', compact('situs'));
    }

    public function arMuseum(Request $request, $situs_id, $museum_id)
    {
        $userAuth = Auth::user();
        $user = User::findOrFail($userAuth->id);
        $situs = SitusPeninggalan::findOrFail($situs_id);
        $museum = VirtualMuseum::with('virtualMuseumObjects')->findOrFail($museum_id);
        // Verify that this museum belongs to the situs
        if ($museum->situs_id != $situs_id) {
            abort(404, 'Museum tidak ditemukan di situs ini.');
        }

        // Get model file size if it exists
        if ($museum->path_obj && Storage::disk('public')->exists($museum->path_obj)) {
            $museum->file_size = Storage::disk('public')->size($museum->path_obj);
        } else {
            $museum->file_size = 0;
        }

        // Log AR activity
        $this->logActivity($user->id, 'Memulai pengalaman AR untuk spot: '.$museum->nama.' di '.$situs->nama);

        // --- Museum Visit Tracking Logic ---
        // 1. Catat kunjungan user ke museum ini (jika belum ada)
        MuseumUserVisit::firstOrCreate([
            'user_id' => $user->id,
            'museum_id' => $museum->museum_id,
        ], [
            'visited_at' => now(),
        ]);

        // 2. Jika materi terkait ada, cek apakah semua museum pada materi ini sudah dikunjungi user
        if ($situs->materi_id) {
            $materiId = $situs->materi_id;
            $materi = Materi::findOrFail($materiId);
            // Ambil semua museum_id pada materi ini
            $allMuseumIds = VirtualMuseum::whereIn(
                'situs_id',
                SitusPeninggalan::where('materi_id', $materiId)->pluck('situs_id')
            )->pluck('museum_id')->toArray();

            // dd($allMuseumIds);

            // Ambil semua museum_id yang sudah dikunjungi user
            $visitedMuseumIds = MuseumUserVisit::where('user_id', $user->id)
                ->whereIn('museum_id', $allMuseumIds)
                ->pluck('museum_id')->unique()->toArray();

            // Jika semua museum sudah dikunjungi dan user progress di step museum, increment progress
            // Skip in demo mode — exploration only, no progress tracking
            if (count($allMuseumIds) > 0 && count($visitedMuseumIds) === count($allMuseumIds)) {
                if (! env('APP_DEMO_MODE', false) && $user->progress_level_sekarang == User::EBOOK && $user->level_sekarang + 1 == $materi->getLinearLevel()) {
                    $user->incrementProgressLevel();
                    $this->logActivity($user->id, 'Menuntaskan semua spot Virtual Living Museum pada materi ID: '.$materiId);
                }
            }
        }

        // Generate a secure token for AR session with user ID and expiration timestamp
        $arToken = TokenHelper::generate($user->id);

        // Debug log
        Log::debug('Generated AR Token:', [
            'user_id' => $user->id,
            'token' => $arToken,
        ]);

        return view('guest.ar.museum', compact('situs', 'museum', 'arToken'));
    }

    public function arMarkerKatalog()
    {
        $katalog = Katalog::first();
        //      download katalog
        if ($katalog->path_pdf && Storage::disk('public')->exists($katalog->path_pdf)) {
            return response()->download(storage_path('app/public/'.$katalog->path_pdf));
        }

        //      not found
        return redirect()->back()->with('error', 'Katalog tidak ditemukan');
    }

    /**
     * E-Learning E-book Page
     */
    public function elearningEbook($ebook_id)
    {
        $user = Auth::user();
        $ebook = Ebook::with('materi')->findOrFail($ebook_id);

        // Check if user has completed pretest for this materi
        $pretestCompleted = $this->isPretestCompleted($user->id, $ebook->materi_id);
        if (! $pretestCompleted) {
            return redirect()->route('guest.elearning.materi', $ebook->materi_id)
                ->with('error', 'Anda harus menyelesaikan pre-test terlebih dahulu untuk mengakses e-book ini.');
        }

        // Check if file exists
        if (! $ebook->path_file || ! file_exists(storage_path('app/public/'.$ebook->path_file))) {
            return redirect()->route('guest.elearning.materi', $ebook->materi_id)
                ->with('error', 'File e-book tidak ditemukan.');
        }

        // Log activity with ebook_id for tracking
        $this->logActivity($user->id, 'Telah membaca E-Book: '.$ebook->judul.' [ebook_id:'.$ebook->ebook_id.']');

        // Catatan: Progress level akan diupdate via AJAX saat user membaca halaman terakhir (lihat endpoint di bawah)

        return view('guest.elearning.ebook', compact('ebook'));
    }

    /**
     * Endpoint untuk AJAX: update progress_level_sekarang user jika sudah membaca semua halaman e-book
     */
    public function markEbookRead(Request $request, $ebook_id)
    {
        $userAuth = Auth::user();
        $user = User::findOrFail($userAuth->id);
        $ebook = Ebook::with('materi')->findOrFail($ebook_id);

        // Cek apakah user sudah pada step EBOOK dan materi yang benar
        $materi = $ebook->materi;
        // Skip in demo mode — exploration only, no progress tracking
        if (! env('APP_DEMO_MODE', false) && $materi && $materi->getLinearLevel() == $user->level_sekarang + 1 && $user->progress_level_sekarang == User::PRE_TEST) {
            // Increment progress ke EBOOK
            $user->incrementProgressLevel();
        }

        // Log aktivitas jika perlu
        $this->logActivity($user->id, 'Menuntaskan semua halaman E-Book: '.$ebook->judul.' [ebook_id:'.$ebook->ebook_id.']');

        return response()->json(['success' => true]);
    }

    /**
     * E-Learning Tugas Page
     */
    public function elearningTugas(Request $request, $materi_id)
    {
        $user = Auth::user();
        $materi = Materi::with(['tugas'])->findOrFail($materi_id);

        return view('guest.elearning.tugas', compact('materi'));
    }

    /**
     * Calculate materi progress
     */
    private function calculateMateriProgress($user_id, $materi_id)
    {
        $materi = Materi::with(['pretest', 'posttest', 'ebook'])->find($materi_id);

        $totalSteps = 0;
        $completedSteps = 0;

        // Count pretest
        if ($materi->pretest->count() > 0) {
            $totalSteps++;
            if ($this->isPretestCompleted($user_id, $materi_id)) {
                $completedSteps++;
            }
        }

        // Count ebooks (assume read if pretest completed)
        if ($materi->ebook->count() > 0) {
            $totalSteps++;
            if ($this->isPretestCompleted($user_id, $materi_id)) {
                $completedSteps++;
            }
        }

        // Count posttest
        if ($materi->posttest->count() > 0) {
            $totalSteps++;
            if ($this->isPosttestCompleted($user_id, $materi_id)) {
                $completedSteps++;
            }
        }

        $progressPercentage = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 100;
        $isCompleted = $this->isMateriCompleted($user_id, $materi_id);
        $isStarted = $this->isPretestCompleted($user_id, $materi_id) || $this->isPosttestCompleted($user_id, $materi_id);

        return [
            'progress_percentage' => $progressPercentage,
            'completed_steps' => $completedSteps,
            'total_steps' => $totalSteps,
            'is_completed' => $isCompleted,
            'is_started' => $isStarted,
        ];
    }

    /**
     * Check if posttest is completed
     */
    private function isPosttestCompleted($user_id, $materi_id)
    {
        $materi = Materi::find($materi_id);
        if (! $materi) {
            return false;
        }
        $user = User::find($user_id);
        if (! $user) {
            return false;
        }

        // Jika materi sudah lewat level user, pasti sudah selesai
        if ($materi->getLinearLevel() < $user->level_sekarang + 1) {
            return true;
        }

        // Jika progress user pada materi ini sudah mencapai atau melewati step posttest
        return $user->progress_level_sekarang >= User::POST_TEST;
    }

    /**
     * Check if materi is completed
     */
    private function isMateriCompleted($user_id, $materi_id)
    {
        if (! $materi_id) {
            return true;
        }

        $materi = Materi::with(['pretest', 'posttest'])->find($materi_id);
        if (! $materi) {
            return false;
        }

        // Check if both pretest and posttest are completed
        $pretestCompleted = $this->isPretestCompleted($user_id, $materi_id);
        $posttestCompleted = $this->isPosttestCompleted($user_id, $materi_id);

        return $pretestCompleted && $posttestCompleted;
    }

    /**
     * Display video peninggalan listing page
     */
    public function videoPeninggalan(Request $request)
    {
        $videos = VideoPeninggalan::all();

        return view('guest.video-peninggalan.index', compact('videos'));
    }

    /**
     * Display video peninggalan detail page
     */
    public function videoPeninggalanShow(Request $request, $id)
    {
        $video = VideoPeninggalan::findOrFail($id);

        return view('guest.video-peninggalan.show', compact('video'));
    }
}
