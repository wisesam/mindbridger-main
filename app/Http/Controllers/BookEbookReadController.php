<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpFoundation\HeaderUtils;

class BookEbookReadController extends Controller
{
    /**
     * Serves only files listed on a book in the current institution.
     * Supports PDF.js inline viewing, byte-range GETs, and optional
     * start/end page subsetting for PDF documents.
     */
    public function show(Request $request)
    {
        $data = $request->validate([
            'rid' => ['required', 'integer', 'min:1'],
            'rf' => ['required', 'string', 'max:255', 'regex:/\A[A-Za-z0-9][A-Za-z0-9._-]*\z/'],
            'start' => ['nullable', 'integer', 'min:0'],
            'end' => ['nullable', 'integer', 'min:0'],
            'page' => ['nullable', 'integer', 'min:0'],
        ]);

        $inst = session('lib_inst');
        if (!$inst && !config('app.multi_inst', false)) {
            $inst = config('app.inst', 1);
        }
        abort_unless($inst !== null && ctype_digit((string) $inst), 403);

        $book = Book::where('inst', $inst)
            ->where('rid', (int) $data['rid'])
            ->firstOrFail();

        // For a different institution, a login must not bypass guest restrictions.
        $user = Auth::user();
        $reader = $user && ((string) $user->inst === (string) $inst || $user->isAdmin())
            ? $user : null;
        $canManage = $reader && $reader->canManageBook($book);

        if ($book->hide_yn === 'Y' && !$canManage) {
            abort(404);
        }
        if (!$reader && ($book->hide_from_guest_yn === 'Y' || $book->e_res_af_login_yn === 'Y')) {
            abort(403, 'Login required to access this e-book.');
        }

        // Preserve positional alignment; don't treat a partially corrupt row as valid.
        $names = $this->fileList($book->files);
        $stored = $this->fileList($book->rfiles);
        abort_unless(count($names) > 0 && count($names) === count($stored), 404);
        $index = array_search($data['rf'], $stored, true);
        abort_unless($index !== false && !empty($names[$index]), 404);

        $originalName = basename(str_replace('\\', '/', stripslashes((string) $names[$index])));
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
            'ogg' => 'video/ogg',
            'webm' => 'video/webm',
        ];
        abort_unless(isset($mimeTypes[$extension]), 415, 'Unsupported e-book file type.');

        $disk = Storage::disk('local'); // storage/app: same as existing ebook/{inst}/{rid}
        $dir = 'ebook/' . (int) $inst . '/' . (int) $book->rid;
        $dirReal = realpath($disk->path($dir));
        $fileReal = realpath($disk->path($dir . '/' . $data['rf']));
        abort_unless($dirReal && $fileReal &&
            strncmp($fileReal, $dirReal . DIRECTORY_SEPARATOR, strlen($dirReal . DIRECTORY_SEPARATOR)) === 0 &&
            is_file($fileReal) && is_readable($fileReal), 404);

        $fallback = 'ebook.' . $extension;
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_INLINE,
            $originalName !== '' ? $originalName : $fallback,
            $fallback
        );
        $headers = [
            'Content-Type' => $mimeTypes[$extension],
            'Content-Disposition' => $disposition,
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ];

        $start = $request->exists('start') ? (int) $request->input('start') : (int) $request->input('page', 0);
        $end = $request->exists('end') ? (int) $request->input('end') : $start;

        // Laravel/Symfony BinaryFileResponse handles standard HTTP byte ranges.
        if ($extension !== 'pdf' || $start < 1 || $end < 1) {
            return response()->file($fileReal, $headers);
        }

        // Page selection is expensive: cap it to prevent oversized FPDI work.
        // Calling with no start/end returns the original PDF without parsing it.
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }
        abort_if($end - $start + 1 > 250, 422, 'PDF page selection exceeds 250 pages.');

        try {
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($fileReal);
            abort_if($pageCount < 1 || $start > $pageCount, 416, 'Invalid PDF page range.');
            $end = min($pageCount, $end);
            if ($start === 1 && $end === $pageCount) {
                return response()->file($fileReal, $headers);
            }

            $pdf = new Fpdi();
            $pdf->setSourceFile($fileReal);
            for ($page = $start; $page <= $end; $page++) {
                $template = $pdf->importPage($page);
                $size = $pdf->getTemplateSize($template);
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($template);
            }
            $output = $pdf->Output('S');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::warning('Unable to generate PDF page subset', [
                'book_id' => $book->id,
                'inst' => $book->inst,
                'error' => $e->getMessage(),
            ]);
            abort(422, 'Unable to generate the requested PDF pages.');
        }

        $pdfName = 'ebook_pages_' . $start . '-' . $end . '.pdf';
        $headers['Content-Disposition'] = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_INLINE,
            $pdfName,
            $pdfName
        );
        return response($output, 200, $headers);
    }

    private function fileList($input): array
    {
        $parts = explode(';', (string) $input);
        while ($parts && end($parts) === '') {
            array_pop($parts);
        }
        return $parts;
    }
}
