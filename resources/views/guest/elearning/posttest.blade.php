<x-elearning-layout>
    {{-- Header Section --}}
    <div class="bg-primary px-6 py-6 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <button class="back-button rounded-full p-2 transition-colors hover:bg-white/10">
                    <i class="fas fa-arrow-left text-xl"></i>
                </button>
                <div class="flex-1">
                    <h1 class="text-lg font-bold">{{ __('elearning.posttest.posttest_selesai') }}</h1>
                    <p class="text-sm opacity-90">{{ $materi->judul }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Content Section --}}
    <div class="min-h-screen bg-gray-50 px-6 py-6">
        @if ($isCompleted)
            {{-- Completed State --}}
            <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                        <i class="fas fa-check text-2xl text-green-600"></i>
                    </div>
                    <h3 class="mb-2 text-xl font-bold text-gray-900">{{ __('elearning.posttest.posttest_selesai') }}</h3>
                    <p class="mb-6 text-gray-600">{{ __('elearning.posttest.telah_menyelesaikan_seluruh_materi') }}</p>
                    <div class="mb-6 grid grid-cols-2 gap-4">
                        <div class="rounded-lg bg-blue-50 p-4">
                            <div class="text-2xl font-bold text-blue-600">{{ $score }}%</div>
                            <div class="text-sm text-blue-600">{{ __('elearning.posttest.nilai') }}</div>
                        </div>
                        <div class="rounded-lg bg-green-50 p-4">
                            <div class="text-2xl font-bold text-green-600">{{ $correctAnswers }}/{{ $totalQuestions }}
                            </div>
                            <div class="text-sm text-green-600">{{ __('elearning.posttest.jawaban_benar') }}</div>
                        </div>
                    </div>
                </div>
                {{-- Evaluasi Soal --}}
                <div class="mt-8">
                    <h4 class="mb-4 text-lg font-semibold text-gray-800">{{ __('elearning.posttest.evaluasi_jawaban_anda') }}</h4>
                    <div class="space-y-6">
                        @php
                            $userAnswers = \App\Models\JawabanUser::where('user_id', Auth::id())
                                ->where('materi_id', $materi->materi_id)
                                ->where('jenis', 'posttest')
                                ->get()
                                ->keyBy('soal_id');
                        @endphp
                        @foreach ($questions as $idx => $question)
                            @php
                                $jawabanUser = $userAnswers[$question->posttest_id] ?? null;
                                $userChoice = $jawabanUser->jawaban_user ?? null;
                                $isCorrect = $jawabanUser ? $jawabanUser->benar : false;
                            @endphp
                            <div
                                class="@if ($isCorrect) border-green-300 bg-green-50 @else border-red-300 bg-red-50 @endif rounded-xl border p-4">
                                <div class="mb-2 flex items-center">
                                    <span class="mr-2 font-bold">{{ __('elearning.posttest.soal') }} {{ $idx + 1 }}:</span>
                                    <span class="flex-1 text-gray-800">{{ $question->pertanyaan }}</span>
                                    @if ($isCorrect)
                                        <span
                                            class="ml-2 rounded bg-green-500 px-2 py-1 text-xs font-semibold text-white">{{ __('elearning.posttest.jawaban_benar') }}</span>
                                    @else
                                        <span
                                            class="ml-2 rounded bg-red-500 px-2 py-1 text-xs font-semibold text-white">{{ __('elearning.posttest.jawaban_salah') }}</span>
                                    @endif
                                </div>
                                <div class="mt-2 space-y-2">
                                    @foreach (['A' => $question->pilihan_a, 'B' => $question->pilihan_b, 'C' => $question->pilihan_c, 'D' => $question->pilihan_d] as $key => $option)
                                        @php
                                            $isUser = $userChoice === $key;
                                            $isBenar = $question->jawaban_benar === $key;
                                        @endphp
                                        <div
                                            class="@if ($isBenar) border-green-500 bg-green-50 @elseif($isUser) border-red-500 bg-red-50 @else border-gray-200 @endif flex items-center rounded-lg border p-3 transition-all duration-200">
                                            <span
                                                class="option-letter @if ($isBenar) bg-green-500 text-white @elseif($isUser) bg-red-500 text-white @else bg-gray-100 text-gray-600 @endif mr-4 flex h-8 w-8 items-center justify-center rounded-full font-bold">
                                                {{ $key }}
                                            </span>
                                            <span class="flex-1">{{ $option }}</span>
                                            {{-- Icon logic --}}
                                            @if ($isCorrect)
                                                @if ($isUser && $isBenar)
                                                    <span class="ml-2"><i
                                                            class="fas fa-check text-lg text-green-600"></i></span>
                                                @endif
                                            @else
                                                @if ($isUser && !$isBenar)
                                                    <span class="ml-2"><i
                                                            class="fas fa-times text-lg text-red-600"></i></span>
                                                @endif
                                                @if ($isBenar)
                                                    <span class="ml-2"><i
                                                            class="fas fa-check text-lg text-green-600"></i></span>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                    @if ($question->pilihan_e)
                                        @php
                                            $isUser = $userChoice === 'E';
                                            $isBenar = $question->jawaban_benar === 'E';
                                        @endphp
                                        <div
                                            class="@if ($isBenar) border-green-500 bg-green-50 @elseif($isUser) border-red-500 bg-red-50 @else border-gray-200 @endif flex items-center rounded-lg border p-3 transition-all duration-200">
                                            <span
                                                class="option-letter @if ($isBenar) bg-green-500 text-white @elseif($isUser) bg-red-500 text-white @else bg-gray-100 text-gray-600 @endif mr-4 flex h-8 w-8 items-center justify-center rounded-full font-bold">
                                                E
                                            </span>
                                            <span class="flex-1">{{ $question->pilihan_e }}</span>
                                            @if ($isCorrect)
                                                @if ($isUser && $isBenar)
                                                    <span class="ml-2"><i
                                                            class="fas fa-check text-lg text-green-600"></i></span>
                                                @endif
                                            @else
                                                @if ($isUser && !$isBenar)
                                                    <span class="ml-2"><i
                                                            class="fas fa-times text-lg text-red-600"></i></span>
                                                @endif
                                                @if ($isBenar)
                                                    <span class="ml-2"><i
                                                            class="fas fa-check text-lg text-green-600"></i></span>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-8 text-center">
                    <a href="{{ route('guest.elearning') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 font-medium text-white transition-colors hover:bg-blue-700">
                        <i class="fas fa-list mr-2"></i>
                        {{ __('elearning.posttest.kembali_ke_daftar_materi') }}
                    </a>
                    <a href="{{ route('guest.elearning.materi', $materi->materi_id) }}"
                        class="ml-2 inline-flex items-center justify-center rounded-lg bg-gray-100 px-6 py-3 font-medium text-gray-700 transition-colors hover:bg-gray-200">
                        <i class="fas fa-eye mr-2"></i>
                        {{ __('elearning.posttest.lihat_materi') }}
                    </a>
                </div>
            </div>
        @else
            {{-- Quiz Instructions --}}
            <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-xl font-bold text-gray-900">{{ __('elearning.posttest.instruksi_posttest') }}</h3>
                <div class="space-y-3 text-gray-700">
                    <div class="flex items-start space-x-3">
                        <div
                            class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-green-100">
                            <span class="text-xs font-medium text-green-600">1</span>
                        </div>
                        <p>{{ __('elearning.posttest.posttest_terdiri_dari', ['count' => $totalQuestions]) }}</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div
                            class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-green-100">
                            <span class="text-xs font-medium text-green-600">2</span>
                        </div>
                        <p>{{ __('elearning.posttest.soal_menguji_pemahaman') }}</p>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div
                            class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-green-100">
                            <span class="text-xs font-medium text-green-600">3</span>
                        </div>
                        <p>{{ __('elearning.posttest.setelah_selesai_materi_completed') }}</p>
                    </div>
                </div>
                <div class="mt-6 border-t border-gray-200 pt-6">
                    <button onclick="startQuiz()"
                        class="w-full rounded-lg bg-green-600 py-3 font-medium text-white transition-colors hover:bg-green-700">
                        <i class="fas fa-play mr-2"></i>
                        {{ __('elearning.posttest.mulai_posttest') }}
                    </button>
                </div>
            </div>
            {{-- Quiz Container (Hidden Initially) --}}
            <div id="quiz-container" class="hidden">
                <form id="quiz-form" method="POST"
                    action="{{ route('guest.elearning.posttest.submit', $materi->materi_id) }}">
                    @csrf
                    {{-- Progress Bar --}}
                    <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-600">{{ __('elearning.posttest.progres') }}</span>
                            <span class="text-sm font-medium text-green-600" id="progress-text">1 {{ __('elearning.posttest.dari') }}
                                {{ $totalQuestions }}</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-200">
                            <div class="h-2 rounded-full bg-green-600 transition-all duration-300" id="progress-bar"
                                style="width: {{ 100 / $totalQuestions }}%"></div>
                        </div>
                    </div>
                    {{-- Questions --}}
                    @foreach ($questions as $index => $question)
                        <div class="quiz-question {{ $index === 0 ? '' : 'hidden' }} mb-6 rounded-2xl bg-white p-6 shadow-sm"
                            data-question="{{ $index }}">
                            <div class="mb-6">
                                <div class="mb-4 flex items-center space-x-3"></div>
                                <p class="leading-relaxed text-gray-800">{{ $question->pertanyaan }}</p>
                            </div>
                            <div class="space-y-3">
                                @foreach (['A' => $question->pilihan_a, 'B' => $question->pilihan_b, 'C' => $question->pilihan_c, 'D' => $question->pilihan_d] as $key => $option)
                                    <label
                                        class="answer-option flex cursor-pointer items-start space-x-3 rounded-lg border border-gray-200 p-4 transition-all duration-300 hover:bg-gray-50"
                                        data-answer="{{ $key }}"
                                        data-correct="{{ $question->jawaban_benar }}">
                                        <input type="radio" name="answers[{{ $question->posttest_id }}]"
                                            value="{{ $key }}"
                                            class="mt-1 text-green-600 focus:ring-green-500" required>
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    class="option-letter flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-sm font-medium text-gray-600">{{ $key }}</span>
                                                <span class="text-gray-800">{{ $option }}</span>
                                            </div>
                                        </div>
                                        <div class="answer-icon opacity-0 transition-opacity duration-300">
                                            <i class="fas fa-check text-green-600"></i>
                                        </div>
                                    </label>
                                @endforeach
                                @if ($question->pilihan_e)
                                    <label
                                        class="answer-option flex cursor-pointer items-start space-x-3 rounded-lg border border-gray-200 p-4 transition-all duration-300 hover:bg-gray-50"
                                        data-answer="E" data-correct="{{ $question->jawaban_benar }}">
                                        <input type="radio" name="answers[{{ $question->posttest_id }}]"
                                            value="E" class="mt-1 text-green-600 focus:ring-green-500" required>
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    class="option-letter flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-sm font-medium text-gray-600">E</span>
                                                <span class="text-gray-800">{{ $question->pilihan_e }}</span>
                                            </div>
                                        </div>
                                        <div class="answer-icon opacity-0 transition-opacity duration-300">
                                            <i class="fas fa-check text-green-600"></i>
                                        </div>
                                    </label>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    {{-- Navigation --}}
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <div class="flex justify-between">
                            <button type="button" id="prev-btn" onclick="previousQuestion()"
                                class="hidden rounded-lg bg-gray-100 px-6 py-3 font-medium text-gray-600 transition-colors hover:bg-gray-200">
                                <i class="fas fa-arrow-left mr-2"></i>
                                {{ __('elearning.posttest.sebelumnya') }}
                            </button>
                            <div class="flex space-x-3">
                                @if ($totalQuestions > 1)
                                    <button type="button" id="next-btn" onclick="nextQuestion()"
                                        class="rounded-lg bg-green-600 px-6 py-3 font-medium text-white transition-colors hover:bg-green-700">
                                        {{ __('elearning.posttest.selainnya') }} <i class="fas fa-arrow-right ml-2"></i>
                                    </button>
                                @endif
                                <button type="button" id="submit-btn" onclick="submitQuiz()"
                                    class="@if ($totalQuestions > 1) hidden @endif rounded-lg bg-green-600 px-6 py-3 font-medium text-white transition-colors hover:bg-green-700">
                                    {{ __('elearning.posttest.selesai') }} <i class="fas fa-check ml-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>

    {{-- Bottom Notification --}}
    <div id="answer-notification"
        class="fixed bottom-0 left-0 right-0 z-50 translate-y-full transform transition-transform duration-300">
        <div class="mx-auto w-full lg:max-w-xl">
            <div id="notification-content" class="mx-6 mb-6 rounded-xl p-4 shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div id="notification-icon" class="flex h-8 w-8 items-center justify-center rounded-full">
                            <i class="fas fa-check text-white"></i>
                        </div>
                        <div>
                            <div id="notification-title" class="font-semibold text-white"></div>
                            <div id="notification-subtitle" class="text-sm text-white/80"></div>
                        </div>
                    </div>
                    <button onclick="continueToNext()" id="continue-btn"
                        class="rounded-lg bg-white/20 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-white/30">
                        {{ __('elearning.posttest.selainnya') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (!$isCompleted)
        <script>
            let currentQuestion = 0;
            let isAnswered = false;
            const totalQuestions = {{ $totalQuestions }};
            const questions = document.querySelectorAll('.quiz-question');

            function startQuiz() {
                document.querySelector('.bg-white.rounded-2xl.shadow-sm.p-6.mb-6').style.display = 'none';
                document.getElementById('quiz-container').classList.remove('hidden');
            }

            function showQuestion(index) {
                isAnswered = false;
                questions.forEach((q, i) => {
                    q.classList.toggle('hidden', i !== index);
                });
                // Reset question state
                const currentQuestionEl = questions[index];
                const options = currentQuestionEl.querySelectorAll('.answer-option');
                options.forEach(option => {
                    option.classList.remove('bg-green-100', 'bg-red-100', 'border-green-500', 'border-red-500');
                    option.classList.add('border-gray-200');
                    const icon = option.querySelector('.answer-icon');
                    const letter = option.querySelector('.option-letter');
                    icon.style.opacity = '0';
                    letter.classList.remove('bg-green-500', 'bg-red-500', 'text-white');
                    letter.classList.add('bg-gray-100', 'text-gray-600');
                });
                // Update progress
                const progress = ((index + 1) / totalQuestions) * 100;
                document.getElementById('progress-bar').style.width = progress + '%';
                document.getElementById('progress-text').textContent = `${index + 1} ${__('elearning.posttest.dari')} ${totalQuestions}`;
                // Update navigation buttons
                const prevBtn = document.getElementById('prev-btn');
                const nextBtn = document.getElementById('next-btn');
                const submitBtn = document.getElementById('submit-btn');
                prevBtn.classList.toggle('hidden', index === 0);
                nextBtn.classList.toggle('hidden', index === totalQuestions - 1);
                submitBtn.classList.toggle('hidden', index !== totalQuestions - 1);
                // Disable next/submit until answered
                if (totalQuestions === 1) {
                    nextBtn.disabled = true;
                    submitBtn.disabled = true;
                } else {
                    nextBtn.disabled = true;
                    submitBtn.disabled = true;
                }
            }

            function nextQuestion() {
                if (currentQuestion < totalQuestions - 1) {
                    currentQuestion++;
                    showQuestion(currentQuestion);
                }
            }

            function previousQuestion() {
                if (currentQuestion > 0) {
                    currentQuestion--;
                    showQuestion(currentQuestion);
                }
            }

            function continueToNext() {
                // Not used in this version
            }

            function submitQuiz() {
                // Check if all questions are answered
                const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
                if (answeredQuestions < totalQuestions) {
                    alert(`{{ __('elearning.posttest.mohon_jawab_semua') }}`.replace(':answered', answeredQuestions).replace(':total', totalQuestions));
                    return;
                }
                // Submit the form
                document.getElementById('quiz-form').submit();
            }

            // Hide notification (not used)
            function hideNotification() {
                const notification = document.getElementById('answer-notification');
                notification.classList.remove('translate-y-0');
                notification.classList.add('translate-y-full');
            }

            // Handle answer selection: enable next/submit, no feedback
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('input[type="radio"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (isAnswered) return;
                        isAnswered = true;

                        const currentQuestionEl = questions[currentQuestion];
                        const nextBtn = document.getElementById('next-btn');
                        const submitBtn = document.getElementById('submit-btn');

                        if (totalQuestions === 1) {
                            // Only one question: enable submitBtn
                            submitBtn.disabled = false;
                        } else {
                            // Enable next or submit button
                            if (currentQuestion === totalQuestions - 1) {
                                submitBtn.disabled = false;
                            } else {
                                nextBtn.disabled = false;
                            }
                        }
                    });
                });
            });
        </script>
    @endif
</x-elearning-layout>