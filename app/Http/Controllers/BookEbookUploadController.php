<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\BookStorageQuota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class BookEbookUploadController extends Controller
{
    public function store(Request $request, $book)
    {
        abort_unless(Auth::check(), 403);
        $user = Auth::user();
        $inst = session('lib_inst');
        $record = Book::where('inst', $inst)->where('id', $book)->firstOrFail();
        // Checks institution, the role's create_yn, and book ownership.
        abort_unless($user->canManageBook($record), 403);

        $request->validate([
            'openFile' => 'required|array|min:1|max:20',
            'openFile.*' => 'required|file|mimes:pdf,mp4,ogg,webm|max:250000',
        ]);

        $files = $request->file('openFile', []);
        $totalBytes = array_sum(array_map(function ($file) {
            return (int) $file->getSize();
        }, $files));

        $uploadedPaths = [];
        try {
            DB::transaction(function () use ($record, $book, $user, $inst, $files, $totalBytes, &$uploadedPaths) {
                // Serialize uploads by this user, including uploads to different books.
                $userRow = DB::table('users')->where('inst', $user->inst)
                    ->where('id', $user->id)->lockForUpdate()->first(['id']);
                abort_unless($userRow, 403);

                $locked = Book::where('inst', $inst)->where('id', $book)
                    ->lockForUpdate()->firstOrFail();
                abort_unless($user->canManageBook($locked), 403);

                $quota = $user->bookDiskQuotaBytes();
                if ($quota !== null) {
                    $used = BookStorageQuota::usedBytes($user);
                    if ($used + $totalBytes > $quota) {
                        throw ValidationException::withMessages([
                            'openFile' => 'Disk quota exceeded. Used ' .
                                round($used / 1048576, 2) . ' MB; limit ' .
                                round($quota / 1048576, 2) . ' MB.',
                        ]);
                    }
                }

                $originals = $this->parts($locked->files);
                $stored = $this->parts($locked->rfiles);
                if (count($originals) !== count($stored)) {
                    abort(409, 'Book file metadata needs repair before uploading.');
                }

                $dir = 'ebook/' . $inst . '/' . $locked->rid;
                foreach ($files as $file) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    abort_unless(in_array($ext, ['pdf', 'mp4', 'ogg', 'webm'], true), 422, 'Unsupported file extension.');
                    $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
                    $name = trim(preg_replace('/[;\r\n]+/u', ' ', $name));
                    if ($name === '') $name = 'ebook.' . $ext;
                    if (in_array($name, $originals, true)) {
                        throw ValidationException::withMessages(['openFile' => 'Duplicate e-book filename: ' . $name]);
                    }
                    $rfile = (string) Str::uuid() . '.' . $ext;
                    $path = Storage::disk('local')->putFileAs($dir, $file, $rfile);
                    if (!$path) throw new RuntimeException('Could not store the e-book file.');
                    $uploadedPaths[] = $path;
                    $originals[] = $name;
                    $stored[] = $rfile;
                }
                $locked->files = implode(';', $originals);
                $locked->rfiles = implode(';', $stored);
                $locked->e_resource_yn = 'Y';
                $locked->save();
            }, 1);
        } catch (Throwable $e) {
            // A DB rollback cannot undo physical files; clean them up.
            foreach ($uploadedPaths as $path) Storage::disk('local')->delete($path);
            throw $e;
        }

        // Old progress_up.php uses a 307 redirect and expects exactly MOD_SUCCESS.
        if ($request->query('legacy') === '1') {
            return response('MOD_SUCCESS', 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        return response()->json([
            'success' => true,
            'message' => 'E-book upload completed.',
            'bytes_uploaded' => $totalBytes,
        ]);
    }

    private function parts($value): array
    {
        return $value === null || $value === '' ? [] : array_values(array_filter(explode(';', (string) $value), static function ($part) {
            return $part !== '';
        }));
    }
}
