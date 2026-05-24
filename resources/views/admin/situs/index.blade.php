<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="sm:flex sm:items-center sm:justify-between">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Kelola Situs Peninggalan</h1>
                        <p class="mt-2 text-sm sm:text-base text-gray-600">Kelola semua situs peninggalan dalam sistem</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <a href="{{ route('admin.situs.create') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            <span class="hidden sm:inline">Tambah Situs</span>
                            <span class="sm:hidden">Tambah</span>
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            <span class="hidden sm:inline">Kembali ke Dashboard</span>
                            <span class="sm:hidden">Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-lg rounded-lg border border-gray-200">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-xl sm:text-2xl text-blue-600"></i>
                            </div>
                            <div class="ml-3 sm:ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Situs</dt>
                                    <dd class="text-lg sm:text-xl font-semibold text-gray-900">{{ $situs->total() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-lg border border-gray-200">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-cube text-xl sm:text-2xl text-green-600"></i>
                            </div>
                            <div class="ml-3 sm:ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Objek Virtual</dt>
                                    <dd class="text-lg sm:text-xl font-semibold text-gray-900">{{ $situs->sum(function($s) { return $s->virtualMuseumObject->count(); }) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-lg border border-gray-200">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-book text-xl sm:text-2xl text-purple-600"></i>
                            </div>
                            <div class="ml-3 sm:ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Materi Terkait</dt>
                                    <dd class="text-lg sm:text-xl font-semibold text-gray-900">{{ $situs->pluck('materi_id')->filter()->unique()->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-lg border border-gray-200">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-xl sm:text-2xl text-orange-600"></i>
                            </div>
                            <div class="ml-3 sm:ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Ditambahkan Oleh</dt>
                                    <dd class="text-lg sm:text-xl font-semibold text-gray-900">{{ $situs->pluck('user_id')->unique()->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Active Alert -->
            @if(isset($selectedMateri))
                <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-4 rounded-xl shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 mr-3">
                            <i class="fas fa-filter text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-blue-900">Menampilkan Filtered Situs</p>
                            <p class="text-xs text-blue-700">Hanya menampilkan situs untuk materi: <strong class="text-blue-900">"{{ $selectedMateri->judul }}"</strong></p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('admin.situs') }}" class="inline-flex items-center justify-center px-4 py-2 border border-blue-300 rounded-lg shadow-sm text-sm font-medium text-blue-700 bg-white hover:bg-blue-50 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            <span>Hapus Filter</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Content -->
            @if($situs->count() > 0)
                <div class="bg-white shadow-lg overflow-hidden sm:rounded-lg border border-gray-200">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Daftar Situs Peninggalan</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Kelola dan pantau semua situs peninggalan yang terdaftar
                                </p>
                            </div>
                            <div class="mt-3 sm:mt-0 text-sm text-gray-500">
                                Total: {{ $situs->total() }} situs
                            </div>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Situs
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Lokasi
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Materi
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Virtual Living Museum
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Objek Virtual
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ditambahkan Oleh
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($situs as $site)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            @if($site->thumbnail)
                                                <div class="flex-shrink-0 h-10 w-10 mr-3">
                                                    <img class="h-10 w-10 rounded-lg object-cover"
                                                         src="{{ asset('storage/' . $site->thumbnail) }}"
                                                         alt="{{ $site->nama }}">
                                                </div>
                                            @else
                                                <div class="flex-shrink-0 h-10 w-10 mr-3 bg-gray-200 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-landmark text-gray-400"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $site->nama }}</div>
                                                <div class="text-sm text-gray-500">{{ Str::limit($site->deskripsi, 50) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ Str::limit($site->alamat, 40) }}</div>
                                        <div class="text-xs text-gray-500">
                                            <i class="fas fa-map-pin mr-1"></i>
                                            {{ $site->lat }}, {{ $site->lng }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $site->materi->judul ?? '-' }}</div>
                                        @if($site->materi && $site->materi->kategori)
                                            <div class="text-xs text-gray-500">{{ $site->materi->kategori }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($site->virtualMuseum->count() > 0)
                                            @if($site->virtualMuseum->count() == 1)
                                                <a href="{{ route('admin.virtual-museum.show', $site->virtualMuseum->first()->museum_id) }}" class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                                                    <i class="fas fa-building mr-1"></i>
                                                    {{ Str::limit($site->virtualMuseum->first()->nama, 15) }}
                                                </a>
                                            @else
                                                <div class="flex flex-col items-center space-y-1">
                                                    <a href="{{ route('admin.situs.show', $site->situs_id) }}" class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors" title="Lihat semua museum di situs ini">
                                                        <i class="fas fa-building mr-1"></i>
                                                        {{ $site->virtualMuseum->count() }} museums
                                                    </a>
                                                    <div class="text-xs text-gray-500">
                                                        @foreach($site->virtualMuseum->take(2) as $museum)
                                                            <div class="truncate">{{ Str::limit($museum->nama, 12) }}</div>
                                                        @endforeach
                                                        @if($site->virtualMuseum->count() > 2)
                                                            <div class="text-gray-400">+{{ $site->virtualMuseum->count() - 2 }} lainnya</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <a href="{{ route('admin.virtual-museum.create') }}?situs_id={{ $site->situs_id }}" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                                                <i class="fas fa-plus mr-1"></i>
                                                Tambah Museum
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $site->virtualMuseumObject->count() > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $site->virtualMuseumObject->count() }} objek
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">{{ $site->user->name ?? '-' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="{{ route('admin.situs.show', $site->situs_id) }}" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.situs.edit', $site->situs_id) }}" class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.situs.destroy', $site->situs_id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus situs ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="lg:hidden">
                        <div class="divide-y divide-gray-200">
                            @foreach($situs as $site)
                            <div class="p-4">
                                <div class="flex items-start space-x-3">
                                    <!-- Thumbnail Image -->
                                    <div class="flex-shrink-0">
                                        @if($site->thumbnail)
                                            <div class="h-12 w-12 rounded-lg overflow-hidden">
                                                <img class="h-12 w-12 object-cover"
                                                     src="{{ asset('storage/' . $site->thumbnail) }}"
                                                     alt="{{ $site->nama }}">
                                            </div>
                                        @else
                                            <div class="h-12 w-12 rounded-lg bg-blue-600 flex items-center justify-center">
                                                <i class="fas fa-landmark text-white text-sm"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <!-- Name and Description -->
                                        <div class="mb-2">
                                            <h4 class="text-sm font-medium text-gray-900">{{ $site->nama }}</h4>
                                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($site->deskripsi, 80) }}</p>
                                        </div>

                                        <!-- Location -->
                                        <div class="mb-2">
                                            <p class="text-xs text-gray-600">
                                                <i class="fas fa-map-pin mr-1"></i>
                                                {{ Str::limit($site->alamat, 50) }}
                                            </p>
                                        </div>

                                        <!-- Status Info -->
                                        <div class="flex flex-wrap gap-2 mb-3">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                {{ $site->materi->kategori ?? 'Tidak ada kategori' }}
                                            </span>
                                            @if($site->virtualMuseum->count() > 0)
                                                @if($site->virtualMuseum->count() == 1)
                                                    <a href="{{ route('admin.virtual-museum.show', $site->virtualMuseum->first()->museum_id) }}" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                                                        <i class="fas fa-building mr-1"></i>
                                                        {{ Str::limit($site->virtualMuseum->first()->nama, 12) }}
                                                    </a>
                                                @else
                                                    <a href="{{ route('admin.situs.show', $site->situs_id) }}" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors" title="Lihat semua museum di situs ini">
                                                        <i class="fas fa-building mr-1"></i>
                                                        {{ $site->virtualMuseum->count() }} museums
                                                    </a>
                                                @endif
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                                    <i class="fas fa-building mr-1"></i>
                                                    Belum ada museum
                                                </span>
                                            @endif
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $site->virtualMuseumObject->count() > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $site->virtualMuseumObject->count() }} objek
                                            </span>
                                        </div>

                                        <!-- Meta Info -->
                                        <div class="space-y-1 text-xs text-gray-500 mb-3">
                                            <div class="flex items-center">
                                                <i class="fas fa-book mr-2 text-gray-400"></i>
                                                Materi: {{ $site->materi->judul ?? 'Tidak ada' }}
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-user-plus mr-2 text-gray-400"></i>
                                                Ditambahkan oleh: {{ $site->user->name ?? 'Tidak ada' }}
                                            </div>
                                        </div>

                                        <!-- Mobile Actions -->
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.situs.show', $site->situs_id) }}"
                                               class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                                <i class="fas fa-eye mr-1"></i>
                                                Detail
                                            </a>
                                            <a href="{{ route('admin.situs.edit', $site->situs_id) }}"
                                               class="flex-1 inline-flex items-center justify-center px-3 py-2 border border-blue-300 shadow-sm text-xs font-medium rounded text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors">
                                                <i class="fas fa-edit mr-1"></i>
                                                Edit
                                            </a>
                                            <form action="{{ route('admin.situs.destroy', $site->situs_id) }}" method="POST" class="flex-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus situs ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="w-full inline-flex items-center justify-center px-3 py-2 border border-red-300 shadow-sm text-xs font-medium rounded text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($situs->hasPages())
                        <div class="bg-gray-50 px-4 py-4 sm:px-6 border-t border-gray-200">
                            {{ $situs->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            @else
                <!-- Empty State -->
                <div class="rounded-2xl bg-white p-12 text-center shadow-sm border border-gray-200">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                        <i class="fas fa-map-marker-alt text-2xl text-gray-400"></i>
                    </div>
                    @if(isset($selectedMateri))
                        <h3 class="mb-2 text-lg font-medium text-gray-900">Tidak Ada Situs Ditemukan</h3>
                        <p class="text-gray-600 mb-4">Tidak ada situs peninggalan yang terikat dengan materi <strong>"{{ $selectedMateri->judul }}"</strong>.</p>
                        <div class="flex justify-center space-x-3">
                            <a href="{{ route('admin.situs.create', ['materi_id' => $selectedMateri->materi_id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                <span>Tambah Situs ke Materi Ini</span>
                            </a>
                            <a href="{{ route('admin.situs') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <i class="fas fa-times mr-2"></i>
                                <span>Hapus Filter</span>
                            </a>
                        </div>
                    @else
                        <h3 class="mb-2 text-lg font-medium text-gray-900">Belum Ada Situs Peninggalan</h3>
                        <p class="text-gray-600 mb-4">Mulai menambahkan situs peninggalan untuk memperkaya konten virtual living museum.</p>
                        <a href="{{ route('admin.situs.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            <span class="hidden sm:inline">Tambah Situs Pertama</span>
                            <span class="sm:hidden">Tambah Situs</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
