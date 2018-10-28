<?php

namespace App\Http\Controllers;

use App\Book;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $book = Book::where('book_id', $request->book_id)->first();
        $user = Auth::user();
        if ($user->ratedBooks()->save($book, ['rating' => $request->rating])) {
            return response('rating added', 200);
        }
        return response()->json([
            'error' => 'something went wrong',
        ], 200);
    }

    /**
     * @param $book_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy($book_id)
    {
        $book = Book::where('book_id', $book_id)->first();
        $user = Auth::user();
        if ($user->ratedBooks()->detach($book)) {
            return response('rating deleted', 200);
        }
        return response()->json([
            'error' => 'something went wrong',
        ], 200);
    }

    /**
     * @param $book_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function averageRating($book_id)
    {
        $book = Book::where('book_id', $book_id)->first();
        $average = $book->ratings->pluck('rating')->avg();
        return response()->json([
            'average' => $average,
        ], 200);
    }

    /**
     * @param $book_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function allRating($book_id)
    {
        $book = Book::where('book_id', $book_id)->first();
        if ($ratings = $book->ratings) {
            $all = $ratings->map(function ($rating) {
                return [
                    'rating' => $rating->rating,
                    'user' => $rating->user->only(['name', 'id']),
                ];
            });
            return response()->json([
                'ratings' => $all,
            ], 200);
        }
        return response()->json('Book don`t have rating', 200);
    }
}