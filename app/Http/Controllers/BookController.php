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
        $libraryBooksID = $user->libraryBooks->pluck('book_id') ?? [];
        $readingBooksID = $user->readingBooks->pluck('book_id') ?? [];
        $wishesID = $user->wishes->pluck('book_id') ?? [];

        $libraryBooks = $this->getManyBooksById($libraryBooksID);
        $readingBooks = $this->getManyBooksById($readingBooksID);
        $wishes = $this->getManyBooksById($wishesID);

        if ($libraryBooks->isNotEmpty()) {
            $randomAuthor = collect($libraryBooks->random()['volumeInfo']['authors'])->random();
            $randomCategory = collect($libraryBooks->random()['volumeInfo']['categories'])->random();
        }else{
            $randomAuthor = 'bestseller';
            $randomCategory = $this->getGenre();
        }

        $specialForYou = [
            'by_author' => $this->getSpecialForYou($randomAuthor),
            'by_genre' => $this->getSpecialForYou($randomCategory),
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
        $response = $response->map(function ($values) {
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

        if(!$phrase){
            return collect([]);
        }

        $client = new Client();
        $response = $client->get('https://www.googleapis.com/books/v1/volumes?q=' . $phrase);
        $bookVolumes = collect(json_decode($response->getBody()->getContents())->items);
        $bookVolumes = $bookVolumes->map(function ($values) {
            return [
                'id' => $values->id,
                'volumeInfo' => $values->volumeInfo,
            ];
        });
        return $bookVolumes;
    }

    public function getGenre()
    {
        $genres = collect(explode(', ','Science fiction, Satire, Drama, Action and Adventure, Romance, Mystery, Horror, Self help, Health, Guide, Travel, Children\'s, Religion, Spirituality & New Age, Science, History, Math, Anthology, Poetry, Encyclopedias, Dictionaries, Comics, Art, Cookbooks, Diaries, Journals, Prayer books, Series, Trilogy, Biographies, Autobiographies, Fantasy'));
        return $genres->random();
    }
}