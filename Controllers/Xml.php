<?php

namespace Controllers;

use Models\Author;
use Models\Book;
use Exception;
use XMLReader;
use XMLWriter;

class Xml
{
    public const startFolder = "data";  //Path to the directory with xml files
    private const MIN_AMOUNT_DIR = 1;   //Minimum number of subdirectories to be created
    private const MAX_AMOUNT_DIR = 4;   //Maximum number of subdirectories to be created
    private const MAX_AMOUNT_PARAMETERS = 65535;//Maximum number of parameters to insert
                                        // duration for 100000 records: 65535=28s, 32535=35s, 12535=59s

    private const AUTHORS = [         //Array of authors
        "Isaac Asimov",               // English
        "Федор Достоевский",          // Russian
        "村上春樹",                    // Japanese
        "김영하",                      // Korean
        "Pavel Vejinov",
        "Ivan Pavelovskij",
        "Александр Пушкин",
        "Haruki Murakami",
        "이문열",
        "Leo Tolstoy",
    ];
    private const TITLES = [           //Array of titles
        "End of Spirit",               // English
        "Преступление и наказание",    // Russian
        "風の歌を聴け",                  // Japanese
        "너의 얼굴 내가 좋아하는 사람",   // Korean
        "Ficciones",
        "Евгений Онегин",
        "Norwegian Wood",
        "서울의 달",
        "War and Peace",
        "Маленький принц"
    ];


    /**
     * @param $startFolder
     * @return bool
     */
    public function recursiveClearDirectory($startFolder): bool
    {
        if (!is_dir($startFolder)) {
            return false;
        }

        $files = glob($startFolder . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->recursiveClearDirectory($file);
            }
        }

        rmdir($startFolder);

        return true;
    }


    /**
     * Creating a folder structure and XML files
     *
     * @param $startFolder
     * @param $depth
     * @param $minAmountXml
     * @param $maxAmountXml
     * @param $minElementsInXml
     * @param $maxElementsInXml
     * @return bool
     * @throws Exception
     */
    public function createFolderStructure($startFolder, $depth, $minAmountXml, $maxAmountXml, $minElementsInXml, $maxElementsInXml): bool
    {
        if (!is_dir($startFolder)) {
            if (!mkdir($startFolder, 0777, true) && !is_dir($startFolder)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $startFolder));
            }
        }

        $this->createXml($startFolder, $minAmountXml, $maxAmountXml, $minElementsInXml, $maxElementsInXml);

        $amountDir = random_int(self::MIN_AMOUNT_DIR, self::MAX_AMOUNT_DIR);
        for ($j = 1; $j <= $amountDir; $j++) {
            if ($depth > 0) {
                $startFolder .= "/" . $depth . "-level_subfolder#" . $j;
                --$depth;
                $this->createFolderStructure($startFolder, $depth, $minAmountXml, $maxAmountXml, $minElementsInXml, $maxElementsInXml);
            }
        }

        return true;
    }


    /**
     * Create XML file
     *
     * @param $startFolder
     * @param $minAmountXml
     * @param $maxAmountXml
     * @param $minElementsInXml
     * @param $maxElementsInXml
     * @throws Exception
     */
    private function createXml($startFolder, $minAmountXml, $maxAmountXml, $minElementsInXml, $maxElementsInXml): void
    {
        $amountXml = random_int($minAmountXml, $maxAmountXml);

        for ($i = 1; $i <= $amountXml; $i++) {
            $xmlWriter = new XMLWriter();
            $dataFolder = realpath(__DIR__ . '/../' . $startFolder);
            $xmlWriter->openURI($dataFolder . "/book_" . $i . ".xml");

            $xmlWriter->startDocument('1.0', 'UTF-8');
            $xmlWriter->setIndent(true);

            $xmlWriter->startElement("books");

            $numElements = random_int($minElementsInXml, $maxElementsInXml);
            for ($j = 1; $j <= $numElements; $j++) {
                $this->addElements($xmlWriter);
            }

            $xmlWriter->endElement();
            $xmlWriter->endDocument();
            $xmlWriter->flush(); // Flush the buffer and close the writer
        }
    }

    /**
     * Add elements into XML file
     *
     * @param XMLWriter $xmlWriter
     * @throws Exception
     */
    private function addElements(XMLWriter $xmlWriter): void
    {
        $authors = self::AUTHORS;
        $titles = self::TITLES;

        $xmlWriter->startElement("book");
        $xmlWriter->writeElement("author", $authors[random_int(0, 9)] . "-" . random_int(0, 999));
        $xmlWriter->writeElement("title", $titles[random_int(0, 9)] . "-" . random_int(0, 999));
        $xmlWriter->endElement(); // Close book element
    }


    /**
     * Parse XML and insert data to SQL
     *
     * @param $directory
     * @return array|string
     */
    public function parseXML($directory): array|string
    {
        $authorModel = new Author();
        $bookModel = new Book();
        $bookBatch = $this->recursiveParseXML($directory, $authorModel, $bookModel);

        return $bookBatch;
    }


    /**
     * Recursive file parsing itself
     *
     * @param $directory
     * @param $authorModel
     * @param $bookModel
     * @return array|string
     */
    private function recursiveParseXML($directory, $authorModel, $bookModel): array|string
    {
        $files = glob($directory . '/*');

        $authorBatch = []; //Array to store authors for batch insertion
        $bookBatch = []; //Array to store books for batch insertion
        foreach ($files as $file) {
            if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'xml') { // Check if the file is an XML file
                $reader = new XMLReader();
                $reader->open($file);

                $recordsAmount = 1;
                while ($reader->read()) {
                    if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'book') {

                        $xmlString = $reader->readOuterXml();
                        $book = simplexml_load_string($xmlString);

                        $author = (string)$book->author;
                        $title = (string)$book->title;

                        $authorBatch[$author] = true; //Add author to batch
                        $bookBatch[] = ['title' => $title, 'author' => $author];//Add book to batch

                        //Write packages and clean them up
                        if ($recordsAmount > self::MAX_AMOUNT_PARAMETERS / 2) {
                            $this->upsertBatches($authorBatch, $authorModel, $bookBatch, $bookModel);
                            $authorBatch = [];
                            $bookBatch = [];
                            $recordsAmount = 0;
                        }
                        $recordsAmount++;
                    }
                }

                $reader->close();

            } elseif (is_dir($file)) { //Check if the file is a directory
                $this->recursiveParseXML($file, $authorModel, $bookModel); //Recursively call the function for subdirectories
            }
        }

        //recording the remainder of batches
        $this->upsertBatches($authorBatch, $authorModel, $bookBatch, $bookModel);

        return array_slice($bookBatch, -100);
    }


    /**
     * @param $authorBatch
     * @param $authorModel
     * @param $bookBatch
     * @param $bookModel
     */
    private function upsertBatches($authorBatch, $authorModel, $bookBatch, $bookModel): void
    {
        //Insert authors in batch
        $authorIds = $authorModel->upsertAuthorsByBatch($authorBatch);

        //Remove duplicates
        $bookBatch = array_unique(array_map(function ($book) {
            return ['title' => $book['title'], 'author' => $book['author']];
        }, $bookBatch), SORT_REGULAR);

        //Insert books in batch
        $bookModel->upsertBooksByBatch($bookBatch, $authorIds);
    }

    /**
     * Function to check if XML content is valid
     * @param $xmlContent
     * @return bool
     */
    public function isValidXML($xmlContent): bool
    {
        $xml = @simplexml_load_string($xmlContent); // Suppress error messages
        return $xml !== false; // Return true if XML content is valid, false otherwise
    }


    /**
     * Upsert authors and get author IDs. !NOT USED!
     *
     * @param $authors
     * @param $authorModel
     * @return array
     */
    private function insertAuthorBatch($authorBatch, $authorModel): array
    {
        $authors = array_keys($authorBatch);
        $authorIds = []; // Array to store author IDs

        foreach ($authors as $author) {
            $authorModel->upsertAuthor($author);
            $authorId = $authorModel->getAuthorIdByName($author);
            $authorIds[$authorId] = $author;
        }

        return $authorIds;
    }

    /**
     * Write books to DB one by one. !NOT USED!
     *
     * @param $books
     * @param $authorIds
     * @param $bookModel
     */
    private function insertBookBatch($books, $authorIds, $bookModel): void
    {
        // Insert books in batch
        foreach ($books as $bookData) {
            $title = $bookData['title'];
            $author = $bookData['author'];
            $authorId = array_search($author, $authorIds, true);
            $bookModel->upsertBookOneByOne($title, $authorId);
        }
    }

}

