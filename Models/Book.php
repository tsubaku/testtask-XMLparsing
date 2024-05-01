<?php

namespace Models;

class Book extends Database
{
    public $title;
    public $authorId;

    /**
     * Write books to DB by batch.
     *
     * @param $books
     * @param $authorIds
     * @return string
     */
    public function upsertBooksByBatch($books, $authorIds): string
    {
        //prepare the data for use in the request
        $values = [];
        $params = [];
        $i = 0;
        foreach ($books as $bookData) {
            $title = $bookData['title'];
            $author = $bookData['author'];
            $authorId = array_search($author, $authorIds, true);

            $values[] = "(:title$i, :author_id$i, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            $params[":title$i"] = $title;
            $params[":author_id$i"] = $authorId;

            $i++;
        }

        //collect the VALUES string based on the number of books
        $valuesString = implode(',', $values);

        //prepare a request
        $sql = "INSERT INTO books (title, author_id, created_at, updated_at) 
            VALUES $valuesString 
            ON CONFLICT (title, author_id) DO UPDATE 
            SET updated_at = EXCLUDED.updated_at";

        //complete the request
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return 'ddd';
    }

    /**
     * Insert book into database. !NOT USED!
     *
     * @param $title
     * @param $authorId
     */
    public function upsertBookOneByOne($title, $authorId): void
    {
        $sql = "INSERT INTO books (title, author_id, created_at, updated_at)
                VALUES (:title, :author_id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ON CONFLICT (title, author_id) DO UPDATE SET updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':author_id', $authorId);
        $stmt->execute();
    }


    /**
     * Get books by author ID
     *
     * @param $authorId
     * @return array|false
     */
    public function getBooksByAuthorId($authorId): bool|array
    {
        $sql = "SELECT id, title FROM books WHERE author_id = :author_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':author_id', $authorId);
        $stmt->execute();
        $booksList = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $booksList;

    }

    /**
     * Get books by author IDs (several authors)
     *
     * @param $authorIds
     * @return array|false
     */
    public function getBooksByAuthorIds($authorIds): bool|array
    {
        $placeholders = implode(',', array_map(function ($id) {
            return ':author_id_' . $id;
        }, $authorIds));
        $sql = "SELECT b.id, COALESCE(b.title, '(книги не найдены)') AS title, a.name AS author 
                FROM authors a
                LEFT JOIN books b ON b.author_id = a.id
                WHERE a.id IN ($placeholders)";
        $stmt = $this->pdo->prepare($sql);

        // Linking author ID values to named placeholders
        foreach ($authorIds as $authorId) {
            $stmt->bindValue(':author_id_' . $authorId, $authorId);
        }

        $stmt->execute();
        $booksList = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $booksList;

    }

}
