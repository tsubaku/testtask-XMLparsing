<?php

namespace Models;

class Author extends Database
{
    public $name;

    /**
     * Add the authors package to the database
     * @param $authorBatch
     * @return array|false
     */
    public function upsertAuthorsByBatch($authorBatch){
        //prepare the data for use in the request
        $values = [];
        $params = [];
        $i = 0;
        foreach ($authorBatch as $authorName => $value) {
            $values[] = "(:name$i, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            $params[":name$i"] = $authorName;
            $i++;
        }

        //collect the VALUES string based on the number of authors
        $valuesString = implode(',', $values);

        //prepare a request
        $sql = "INSERT INTO authors (name, created_at, updated_at) 
            VALUES $valuesString 
            ON CONFLICT (name) DO UPDATE 
            SET updated_at = EXCLUDED.updated_at
            RETURNING id"; //return the id of inserted/updated records

        //complete the request
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $authorArray = $this->getAuthorsByIds($stmt);

        return $authorArray;
    }

    /**
     * Get array authors
     * @param $stmt
     * @return array|false
     */
    public function getAuthorsByIds($stmt): bool|array
    {
        // get the actual id from the query result
        $authorIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // prepare a request to obtain the names of authors by their id
        $sqlNames = "SELECT id, name FROM authors WHERE id IN (" . implode(",", $authorIds) . ")";
        $stmtNames = $this->pdo->query($sqlNames);
        $authorArray = $stmtNames->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $authorArray;
    }

    /**
     * Get author ID's by name
     *
     * @param $author
     * @return array|false
     */
    public function getAuthorIdsByName($author): array|false
    {
        $sql = "SELECT id FROM authors WHERE name LIKE :name";
        $stmt = $this->pdo->prepare($sql);
        $author = '%' . $author . '%';
        $stmt->bindParam(':name', $author);
        $stmt->execute();
        $authorIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        return $authorIds;
    }


    /**
     * Insert one author into database !NOT USED!
     *
     * @param $author
     */
    public function upsertAuthor($author): void
    {
        $sql = "INSERT INTO authors (name, created_at, updated_at)
                VALUES (:name, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ON CONFLICT (name) DO UPDATE SET updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $author);
        $stmt->execute();
    }

    /**
     * Get author ID by name. !NOT USED!
     *
     * @param $author
     * @return int|false
     */
    public function getAuthorIdByName($author): int|false
    {
        $sql = "SELECT id FROM authors WHERE name = :name LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $author);
        $stmt->execute();
        $authorId = $stmt->fetchColumn();

        return $authorId;
    }

}
