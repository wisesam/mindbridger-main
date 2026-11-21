<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class BookStorageQuota
{
    public static function usedBytes(User $user): int
    {
        $bytes = 0;
        $books = Book::query()
            ->where('inst', $user->inst)
            ->where('created_by', (string) $user->id)
            ->get(['rid']);

        foreach ($books as $book) {
            if (empty($book->rid)) continue;
            $folder = 'ebook/' . $user->inst . '/' . $book->rid;
            foreach (Storage::disk('local')->allFiles($folder) as $path) {
                $bytes += (int) Storage::disk('local')->size($path);
            }
        }

        return $bytes;
    }
}
