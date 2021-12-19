<?php

/**
 * A quick-and-dirty API class for GoodReads.
 *
 * Methods implemented:
 * - author.show (getAuthor)
 * - author.books (getBooksByAuthor)
 * - book.show (getBook)
 * - book.show_by_isbn (getBookByISBN)
 * - book.title (getBookByTitle)
 * - reviews.list (getShelf|getLatestRead|getAllBooks)
 * - review.show (getReview)
 * - user.show (getUser|getUserByUsername)
 *
 * @author danielgwood <github.com/danielgwood>
 */

namespace App\Clients;

use Exception;

class Goodreads
{
    /**
     * Root URL of the API (no trailing slash).
     */
    public const API_URL = 'https://www.goodreads.com';

    /**
     * How long to sleep between requests to prevent flooding/TOS violation (milliseconds).
     */
    public const SLEEP_BETWEEN_REQUESTS = 1000;

    /**
     * @var string|null Your API key.
     */
    protected ?string $apiKey = '';

    /**
     * @param string|null $apiKey
     *
     * @return Goodreads
     */
    public function setApiKey(?string $apiKey): Goodreads
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @var integer When was the last request made?
     */
    protected int $lastRequestTime = 0;

    /**
     * Get details for a given author.
     *
     * @param int $authorId
     *
     * @return array
     * @throws Exception
     */
    public function getAuthor(int $authorId): array
    {
        return $this->request(
            'author/show',
            [
                'key' => $this->apiKey,
                'id' => (int)$authorId
            ]
        );
    }

    /**
     * Get books by a given author.
     *
     * @param int $authorId
     * @param int $page Optional page offset, 1-N.
     *
     * @return array
     * @throws Exception
     */
    public function getBooksByAuthor(int $authorId, int $page = 1): array
    {
        return $this->request(
            'author/list',
            [
                'key' => $this->apiKey,
                'id' => $authorId,
                'page' => $page
            ]
        );
    }

    /**
     * Get details for a given book.
     *
     * @param int $bookId
     *
     * @return array
     * @throws Exception
     */
    public function getBook(int $bookId): array
    {
        return $this->request(
            'book/show',
            [
                'key' => $this->apiKey,
                'id' => $bookId
            ]
        );
    }

    /**
     * Get details for a given book by ISBN.
     *
     * @param string $isbn
     *
     * @return array
     * @throws Exception
     */
    public function getBookByIsbn(string $isbn): array
    {
        return $this->request(
            'book/isbn/' . urlencode($isbn),
            [
                'key' => $this->apiKey
            ]
        );
    }

    /**
     * Get details for a given book by title.
     *
     * @param string $title
     * @param string $author Optionally provide this for more accuracy.
     *
     * @return array
     * @throws Exception
     */
    public function getBookByTitle(string $title, string $author = ''): array
    {
        return $this->request(
            'book/title',
            [
                'key' => $this->apiKey,
                'title' => ($title),
                'author' => $author
            ]
        );
    }

    /**
     * Get details for a given user.
     *
     * @param int $userId
     *
     * @return array
     * @throws Exception
     */
    public function getUser(int $userId): array
    {
        return $this->request(
            'user/show',
            [
                'key' => $this->apiKey,
                'id' => (int)$userId
            ]
        );
    }

    /**
     * Get details for a given user by username.
     *
     * @param string $username
     *
     * @return array
     * @throws Exception
     */
    public function getUserByUsername(string $username): array
    {
        return $this->request(
            'user/show',
            [
                'key' => $this->apiKey,
                'username' => $username
            ]
        );
    }

    /**
     * Get details for of a particular review
     *
     * @param int $reviewId
     * @param int $page Optional page number of comments, 1-N.
     *
     * @return array
     * @throws Exception
     */
    public function getReview(int $reviewId, int $page = 1): array
    {
        return $this->request(
            'review/show',
            [
                'key' => $this->apiKey,
                'id' => (int)$reviewId,
                'page' => (int)$page
            ]
        );
    }

    /**
     * Get a shelf for a given user.
     *
     * @param int $userId
     * @param string $shelf Read|currently-reading|to-read etc.
     * @param string $sort Title|author|rating|year_pub|date_pub|date_read|date_added|avg_rating etc.
     * @param int $limit Limit 1-200.
     * @param int $page Page 1-N.
     *
     * @return array
     * @throws Exception
     */
    public function getShelf(int $userId, string $shelf, string $sort = 'title', int $limit = 100, int $page = 1): array
    {
        return $this->request(
            'review/list',
            [
                'v' => 2,
                'format' => 'xml',     // :( GoodReads still doesn't support JSON for this endpoint
                'key' => $this->apiKey,
                'id' => (int)$userId,
                'shelf' => $shelf,
                'sort' => $sort,
                'page' => $page,
                'per_page' => $limit
            ]
        );
    }

    /**
     * Get all books for a given user.
     *
     * @param int $userId
     * @param string $sort Title|author|rating|year_pub|date_pub|date_read|date_added|avg_rating etc.
     * @param int $limit Limit 1-200.
     * @param int $page Page 1-N.
     *
     * @return array
     * @throws Exception
     */
    public function getAllBooks(int $userId, string $sort = 'title', int $limit = 100, int $page = 1): array
    {
        return $this->request(
            'review/list',
            [
                'v' => 2,
                'format' => 'xml',     // :( GoodReads still doesn't support JSON for this endpoint
                'key' => $this->apiKey,
                'id' => (int)$userId,
                'sort' => $sort,
                'page' => $page,
                'per_page' => $limit
            ]
        );
    }

    /**
     * Get the details of an author.
     *
     * @param int $authorId
     *
     * @return array
     * @throws Exception
     */
    public function showAuthor(int $authorId): array
    {
        return $this->getAuthor($authorId);
    }

    /**
     * Get the details of a user.
     *
     * @param int $userId
     *
     * @return array
     * @throws Exception
     */
    public function showUser(int $userId): array
    {
        return $this->getUser($userId);
    }

    /**
     * Get the latest books read for a given user.
     *
     * @param int $userId
     * @param string $sort Title|author|rating|year_pub|date_pub|date_read|date_added|avg_rating etc.
     * @param int $limit Limit 1-200.
     * @param int $page Page 1-N.
     *
     * @return array
     * @throws Exception
     */
    public function getLatestReads(int $userId, string $sort = 'date_read', int $limit = 100, int $page = 1): array
    {
        return $this->getShelf($userId, 'read', $sort, $limit, $page);
    }

    /**
     * Makes requests to the API.
     *
     * @param string $endpoint A GoodReads API function name.
     * @param array  $params   Optional parameters.
     *
     * @return array
     * @throws Exception If it didn't work.
     */
    private function request(string $endpoint, array $params = []): array
    {
        // Prepare the URL and headers
        $url     = self::API_URL . '/' . $endpoint . '?' . (
            (!empty($params))
                ? http_build_query($params)
                : '');
        $headers = [
            'Accept: application/xml',
        ];

        if (isset($params['format']) && $params['format'] === 'json') {
            $headers = [
                'Accept: application/json',
            ];
        }

        // Execute via CURL
        if (extension_loaded('curl')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            usleep(self::SLEEP_BETWEEN_REQUESTS);
            $headerSize   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body         = substr($response, $headerSize);
            $errorNumber  = curl_errno($ch);
            $errorMessage = curl_error($ch);

            if ($errorNumber > 0) {
                throw new Exception('Method failed: ' . $endpoint . ': ' . $errorMessage);
            }

            curl_close($ch);
        } else {
            throw new Exception('CURL library not loaded!');
        }

        // Try and cadge the results into a half-decent array
        if (isset($params['format']) && $params['format'] === 'json') {
            $results = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } else {
            $results = json_decode(json_encode((array)simplexml_load_string(
                $body,
                'SimpleXMLElement',
                LIBXML_NOCDATA
            ), JSON_THROW_ON_ERROR), 1, 512, JSON_THROW_ON_ERROR); // I know, I'm a terrible human being
        }

        if ($results !== null) {
            return $results;
        }

        throw new Exception('Server error on "' . $url . '": ' . $response);
    }
}
