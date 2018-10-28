<?php
/**
 * Created by PhpStorm.
 * User: maxym
 * Date: 24.10.18
 * Time: 14:49
 */

namespace App\Http\Controllers;

use Google_Client;
use Google_Http_Batch;
use Google_Service_Books;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    protected $google;
    protected $googleBooks;

    /**
     * BookController constructor.
     */
    public function __construct()
    {
        $this->google = new Google_Client();
        $this->googleBooks = new Google_Service_Books($this->google);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStarted()
    {
        $user = Auth::user();
        $libraryBooksID = $user->libraryBooks->pluck('book_id');
        $readingBooksID = $user->readingBooks->pluck('book_id');
        $wishesID = $user->wishes->pluck('book_id');

        $libraryBooks = $this->getManyBooksById($libraryBooksID);
        $readingBooks = $this->getManyBooksById($readingBooksID);
        $wishes = $this->getManyBooksById($wishesID);
        $specialForYou = [
            'by_author' => $this->getSpecialForYou(collect($libraryBooks->random()['volumeInfo']['authors'])->random()),
            'by_category' => $this->getSpecialForYou(collect($libraryBooks->random()['volumeInfo']['categories'])->random()),
        ];

        $userInfo = [
            '_token' => csrf_token(),
            'my_library' => $libraryBooks,
            'my_reading' => $readingBooks,
            'wish_list' => $wishes,
            'special_for_you' => $specialForYou,
        ];

        return response()->json($userInfo);
    }

    /**
     * @param iterable $ids
     * @return \Illuminate\Support\Collection
     */
    public function getManyBooksById(iterable $ids)
    {
        $this->google->setUseBatch(true);
        $batch = new Google_Http_Batch($this->google);

        $ids->map(function ($id) use (&$batch) {
            $batch->add($this->googleBooks->volumes->get($id));
        });
        $response = collect($batch->execute())->values();
        $response = $response->map(function ($values){
            return [
                'id' => $values->id,
                'volumeInfo' => $values->volumeInfo,
                ];
        });
        return $response;
    }

    /**
     * @param $phrase
     * @return \Illuminate\Support\Collection
     */
    public function getSpecialForYou($phrase)
    {
        $client = new Client();
        $response = $client->get('https://www.googleapis.com/books/v1/volumes?q=' . $phrase);
        $bookVolumes = collect(json_decode($response->getBody()->getContents())->items);
        $bookVolumes = $bookVolumes->map(function ($values){
            return [
                'id' => $values->id,
                'volumeInfo' => $values->volumeInfo,
            ];
        });
        return $bookVolumes;
    }
}