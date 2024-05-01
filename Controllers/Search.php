<?php

namespace Controllers;

use Models\Author;
use Models\Book;

class Search
{

    /**
     * Search books by author name
     *
     * @param $author
     * @return array|string
     */
    public function searchAuthor($author): array|string
    {
        $authorModel = new Author();
        $bookModel = new Book();

        $authorIds = $authorModel->getAuthorIdsByName($author);
        if (!$authorIds) {
            return "Author not found";
        }

        $booksList = $bookModel->getBooksByAuthorIds($authorIds);
        if (!$booksList) {
            return "Books not found";
        }

        return $booksList;
    }
}

