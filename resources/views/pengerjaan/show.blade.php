@extends('layouts.app-new')
@section('title', 'Mengerjakan: ' . $hasilUjian->ujian->judul)

@section('content')

{{-- Timer (Sudah ada) --}}
<div 
    x-data="timerData('{{ $endTime->toIso8601String() }}')" 
    x-init="initTimer()"
    class="card shadow-sm mb-4 sticky-top"
>
    <div class="card-body p-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $hasilUjian->ujian->judul }}</h5>
        <div class="bg-danger text-white px-3 py-2 rounded shadow-sm">
            <i class="bi bi-alarm-fill"></i>
            <span class="fw-bold fs-5" x-text="displayTime">00:00</span>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form id="form-ujian" action="{{ route('pengerjaan.submit', $hasilUjian) }}" method="POST">
            @csrf
            
            @foreach ($hasilUjian->ujian->soals as $key => $soal)
                <div class="mb-4 pb-3 border-bottom">
                    <p class="fw-bold">{{ $key + 1 }}. {{ $soal->pertanyaan }}</p>
                    
                    {{-- Tampilkan gambar (Ini seharusnya sudah berfungsi setelah Langkah 1) --}}
                    @if ($soal->image_path)
                    <div class="mb-3">
                        <img src="{{ Storage::url($soal->image_path) }}" alt="Gambar Soal" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                    @endif

                    {{-- 
                    ================================================================
                    PERUBAHAN: Tampilkan input berdasarkan Tipe Soal
                    ================================================================
                    --}}
                    <div class="ps-4">
                        @if ($soal->type == 'pilihan_ganda')
                            {{-- Input Pilihan Ganda --}}
                            @foreach ($soal->pilihanJawabans as $pilihan)
                                <div class="form-check">
                                    {{-- PERUBAHAN NAMA INPUT --}}
                                    <input class="form-check-input" type="radio" name="jawaban[{{ $soal->id }}][pilihan_id]" id="pilihan-{{ $pilihan->id }}" value="{{ $pilihan->id }}" required>
                                    <label class="form-check-label" for="pilihan-{{ $pilihan->id }}">
                                        {{ $pilihan->teks_pilihan }}
                                    </label>
                                </div>
                            @endforeach
                        
                        @elseif ($soal->type == 'esai')
                            {{-- Input Esai --}}
                            <div class="mb-3">
                                <label for="jawaban-{{ $soal->id }}" class="form-label text-muted">Tulis jawaban esai Anda:</label>
                                {{-- PERUBAHAN NAMA INPUT --}}
                                <textarea class{
type: uploaded file
fileName: laravel-file-storage.pdf
fullContent:
--- PAGE 1 ---

File Upload & Storage in Laravel 12
A deep dive into filesystem, uploads, disks and best practices
Pengembangan Sistem Informasi Berbasis Web Lanjut
Program Studi Sistem Informasi
October 27, 2025
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
222
 1/33

--- PAGE 2 ---

Agenda
1 Introduction to Filesystem in Laravel
2 Configuration
3 Working With Disks
4 Retrieving Files
5 Storing Files
File Uploads (User Uploads)
7 Deleting Files & Directories
8 Testing File Uploads
9 Best Practices
10 Advanced Topics
11 Summary
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
222
 2/33

--- PAGE 3 ---

Introduction to Filesystem in Laravel
What is File Storage in Laravel?
• Laravel uses the Flysystem PHP package for filesystem abstraction.
• Allows you to switch drivers (local, S3, FTP, etc) with the same API.
• Unified way to upload, retrieve, delete, move files.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
3/33

--- PAGE 4 ---

Introduction to Filesystem in Laravel
Why Use a Filesystem Abstraction?
• Decouples storage logic from application logic.
• Makes it easier to switch storage types $(local\rightarrow cloud)$ without rewriting.
• Helps you manage visibility, temporary URLs, metadata consistently.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
4/33

--- PAGE 5 ---

Configuration
Configuration: config/filesystems.php
• The file 'config/filesystems.php' is where you define "disks".
 • Each "disk" represents a specific driver + storage location.
• You can define many disks, even multiple with the same driver.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
222
 $5/33$

--- PAGE 6 ---

Configuration
Local Driver (default)
• Uses the 'local' driver: stores files on the server filesystem.
• Example: Storage::disk('local )->put('example.txt', 'Contents');
 • Default root for 'local' driver is 'storage/app/private'.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
$6/33$

--- PAGE 7 ---

Configuration
Public Disk
• The 'public' disk is intended for files that should be publicly accessible.
• By default: driver = 'local', $root=`storage/app/public^{\prime}.$
• You'll typically run the artisan command: php artisan storage:link to create a
 symbolic link ' $public/storage^{\prime}\rightarrow`storage/app/public^{\prime}$.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
7/33

--- PAGE 8 ---

Configuration
Driver Prerequisites
For S3 driver: you must install 'league/flysystem-aws-s3-v3 .
• For FTP driver: install 'league/flysystem-ftp'.
For SFTP: install 'league/flysystem-sftp-v3'.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
8/33

--- PAGE 9 ---

Configuration
Scoped & Read-only Filesystems
• Scoped disks: Prefix all paths automatically.
• Read-only disks: Disallow write operations.
• Example configuration for scoped disk: 's3-videos' => [ 'driver' =>
'scoped', 'disk' => ' $s3^{\prime}$, 'prefix' => 'path/to/videos', ],
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
9/33

--- PAGE 10 ---

Obtaining Disk Instances
Working With Disks
• Use the facade: Storage. Eg: Storage::put(...) uses default disk.
• To specify a disk: Storage::disk('s3 )->put(...)
• On-demand disks: you may build a disk at runtime.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
10/33

--- PAGE 11 ---

Retrieving Files
Retrieving Files: get, exists, missing
Storage::get('file.jpg') returns the contents.
• Storage::disk( $\prime s3^{\prime}$)->exists( file.jpg') checks existence.
• Storage::disk( $\prime s3^{\prime}$)->missing('file.jpg') checks non-existence.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
11/33

--- PAGE 12 ---

Retrieving Files
Downloading Files
• Use Storage::download('file.jpg') to force a download response.
• You may also specify the download name and headers.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
12/33

--- PAGE 13 ---

File URLs & Temporary URLs
Retrieving Files
• Storage::url('file.jpg') returns a public URL depending on the disk driver.
• Storage::temporaryUrl('file.jpg', now()->addMinutes(5)) generates a
 temporary access link (useful for expiring downloads).
• When using S3 or compatible drivers, you can also generate temporary upload URLs for
 direct client uploads.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
13/33

--- PAGE 14 ---

Storing Files
Basic Storage: put
Storage::put('file.jpg', \$contents) stores data.
 • If the write fails, it returns false (unless configured to throw).
• You can enable throwing by config option "throw' $=i$ true'.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
14/33

--- PAGE 15 ---

Storing Files
Prepend / Append
• Storage::prepend('file.log', 'Prepended Text') adds to beginning.
 • Storage::append('file.log', 'Appended Text') adds to end.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
15/33

--- PAGE 16 ---

Storing Files
Copying & Moving Files
• Storage::copy('old/file.jpg', 'new/file.jpg') is used to copy a file from one
 location to another within the same disk.
• Storage::move( old/file.jpg', 'new/file.jpg') moves or renames a file.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
16/33

--- PAGE 17 ---

Uploading User Files
File Uploads (User Uploads)
• When a user uploads a file via a form, you can use: \$path =
\$request->file('avatar')->store( avatars');
• The 'store' method auto-generates a unique filename and determines extension via MIME
 type.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
17/33

--- PAGE 18 ---

File Uploads (User Uploads)
Specifying File Name: storeAs
• Use \$request->file( avatar )->storeAs( avatars', \$request->user()->id);
• On the 'Storage' facade: Storage::putFileAs('avatars',
\$request->file('avatar'), 'photo.jpg');
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
18/33

--- PAGE 19 ---

File Uploads (User Uploads)
Choosing the Disk for Upload
• Default disk used by 'store()' can be overridden by passing disk name.
• Example: \$path =
\$request->file('avatar')->store( avatars/'.\$request->user()->id, 's3');
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
19/33

--- PAGE 20 ---

File Uploads (User Uploads)
Original Name & Secure Names
• You can retrieve the original file name using \$file->getClientOriginalName() and its
 extension via \$file->getClientOriginalExtension(), but these values can be
 tampered with by the user.
• It is recommended to use \$file->hashName() and \$file->extension() to generate
 secure, unique filenames.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
20/33

--- PAGE 21 ---

File Uploads (User Uploads)
File Visibility (public/private)
• Visibility defines permission abstraction: 'public' or 'private'.
• Example write: Storage::put('file.jpg', \$contents, 'public');
• Get or set visibility: Storage::getVisibility( file.jpg') /
Storage::setVisibility('file.jpg', 'public')
• When uploading: \$path =
\$request->file('avatar )->storePublicly('avatars', 's3');
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
21/33

--- PAGE 22 ---

Deleting Files & Directories
Deleting Files
• Use Storage::delete( file.jpg') or array of files.
• To specify disk: Storage::disk $(^{\prime}s3^{\prime})$->delete('path/file.jpg')
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
22/33

--- PAGE 23 ---

Deleting Files & Directories
Working with Directories
• List files: Storage:: files(\$directory) and Storage::allFiles(\$directory) for
 recursive.
• List directories: Storage::directories(\$directory) and
 Storage::allDirectories(\$directory)
• Create directory: Storage::makeDirectory(\$directory)
• Delete directory (and all its files): Storage::deleteDirectory(\$directory)
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
23/33

--- PAGE 24 ---

Testing File Uploads
Testing: using Storage:: fake
• You can fake a disk for testing: Storage::fake('photos );
• Then run a request with fake uploaded files:
UploadedFile::fake()->image('photo1.jpg') etc.
• Assert stored files: Storage::disk('photos )->assertExists('photo1.jpg') etc.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
24/33

--- PAGE 25 ---

Best Practices
Best Practices: Naming, Visibility & Security
• Always validate uploaded files (e.g., size, MIME type) using Laravel's Request validation
 rules.
• Use sanitized and unique filenames such as \$file->hashName() to prevent collisions.
• Set appropriate visibility: private for sensitive files and public only when necessary.
• Avoid exposing internal storage paths in URLs
 abstraction.
always use Laravel's Storage
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
25/33

--- PAGE 26 ---

Best Practices
Best Practices: Choosing the Right Driver
For local development: 'local' driver is fine.
• For production / large scale: cloud storage (S3, DigitalOcean Spaces, etc) via 's3' driver.
• Use scoped or read-only disks when you need path isolation or restrict writes.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
26/33

--- PAGE 27 ---

Best Practices
Best Practices: Handling Large Files & Streams
• Use streaming when uploading/moving large files: Storage::putFile() or
 putFileAs() to automatically handle stream.
• Avoid loading huge files into memory in PHP.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
27/33

--- PAGE 28 ---

Advanced Topics
Custom Filesystems
• You may write custom filesystem drivers by extending Flysystem or Laravel's abstraction.
 • Useful when integrating unusual storage systems.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
28/33

--- PAGE 29 ---

Advanced Topics
On-Demand Disks
• You can build a disk at runtime via: \$disk =
Storage::build(['driver'=>'local', root'=>'/path/to/root']);
 • Useful when you need a temporary storage target that is not pre-configured.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
29/33

--- PAGE 30 ---

Advanced Topics
Temporary Upload URLs (Client-side Uploads)
• Especially for serverless / SPA scenarios: you may use
Storage::temporaryUploadUrl('file.jpg', now()->addMinutes(5)) on S3.
 • Returns an array with 'url' and 'headers' for direct upload from client.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
30/33

--- PAGE 31 ---

Advanced Topics
URL Host Customization
• You may override the url option on a disk configuration to customize the base path or
 domain.
• Example for the local driver: 'url' = env('APP_URL') '/storage'
• This is useful when you use a custom domain, CDN, or subdomain for file delivery.
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
31/33

--- PAGE 32 ---

Summary
Summary
• Laravel's filesystem abstraction gives a consistent API for storing and retrieving files.
• Key concepts: disks, drivers, visibility, URLs/temporary URLs, uploads, streaming, testing.
• Use best practices for naming, security, correct driver and streaming large files.
• Adapt storage strategy depending on environment (local vs cloud).
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
File Upload & Storage in Laravel 12
October 27, 2025
32/33

--- PAGE 33 ---

Summary
Questions
Pengembangan Sistem Informasi Berbasis Web Lanjut (F
Any questions?
222
File Upload & Storage in Laravel 12
October 27, 2025
$33/33$
}
" class="form-control" name="jawaban[{{ $soal->id }}][teks_jawaban]" id="jawaban-{{ $soal->id }}" rows="4" required></textarea>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="d-grid">
                <button type="submit" class="btn btn-primary fw-bold" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan ujian ini?')">
                    <i class="bi bi-check-circle-fill"></i> Selesaikan Ujian
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Script Timer (Sudah ada) --}}
<script>
    function timerData(endTime) {
        return {
            endTime: new Date(endTime),
            displayTime: '00:00',
            interval: null,

            initTimer() {
                this.updateTime(); 
                this.interval = setInterval(() => {
                    this.updateTime();
                }, 1000);
            },

            updateTime() {
                const now = new Date();
                const remaining = this.endTime.getTime() - now.getTime();

                if (remaining <= 0) {
                    clearInterval(this.interval);
                    this.displayTime = '00:00';
                    this.submitForm();
                } else {
                    const minutes = Math.floor((remaining / 1000 / 60) % 60);
                    const seconds = Math.floor((remaining / 1000) % 60);
                    
                    this.displayTime = `${this.pad(minutes)}:${this.pad(seconds)}`;
                }
            },

            pad(num) {
                return num < 10 ? '0' + num : num;
            },

            submitForm() {
                alert('Waktu habis! Jawaban Anda akan dikirim secara otomatis.');
                document.getElementById('form-ujian').submit();
            }
        };
    }
</script>
@endsection