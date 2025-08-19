<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReadingHistory;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReadingHistoryController extends Controller
{
    public function set_status(Request $request, Book $book)
    {
        $user = Auth::user();
        $status = $request->input('status');
        
        // Find existing reading history or create new one
        $readingHistory = ReadingHistory::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->first();
            
        if (!$readingHistory) {
            $readingHistory = new ReadingHistory();
            $readingHistory->user_id = $user->id;
            $readingHistory->book_id = $book->id;
        }
        
        switch ($status) {
            case 'in_progress':
                $readingHistory->status = 'in_progress';
                $readingHistory->start_time = Carbon::now();
                $readingHistory->end_time = null;
                
                // Copy book.auto_toc to history_data
                if ($book->auto_toc) {
                    $tocData = json_decode($book->auto_toc, true);
                    $historyData = [];
                    
                    foreach ($tocData as $index => $item) {
                        $historyData[] = [
                            'title' => $item['title'] ?? 'Untitled',
                            'page' => $item['page'] ?? null,
                            'start' => $item['start'] ?? ($item['page'] ?? null),
                            'end' => $item['end'] ?? ($item['page'] ?? null),
                            'level' => $item['level'] ?? 1,
                            'status' => 'none',
                            'start_time' => null,
                            'end_time' => null,
                            'evaluation' => null
                        ];
                    }
                    
                    $readingHistory->history_data = $historyData;
                }
                break;
                
            case 'completed':
                $readingHistory->status = 'completed';
                $readingHistory->end_time = Carbon::now();
                break;
                
            case 'reset':
                // Delete reading history and details
                $readingHistory->delete();
                return redirect()->route('book.show', $book->id)
                    ->with('success', '독서 기록이 초기화되었습니다.');
        }
        
        $readingHistory->save();
        
        return redirect()->route('book.show', $book->id)
            ->with('success', '독서 상태가 업데이트되었습니다.');
    }
    
    public function check(Book $book)
    {
        $user = Auth::user();
        $readingHistory = ReadingHistory::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->first();
            
        if ($readingHistory) {
            return response()->json([
                'hasHistory' => true,
                'history' => [
                    'status' => $readingHistory->status,
                    'start_time' => $readingHistory->start_time,
                    'end_time' => $readingHistory->end_time
                ]
            ]);
        }
        
        return response()->json(['hasHistory' => false]);
    }
} 