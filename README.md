##PROJECT DESCRIPTION:

There is a tree of start folder, it's subfolders, their subfolders, etc. In each folder, subfolder, etc. there are the same structured XML files stored.
Example:

    <book>
        <author>Isak Azimov</author>
       <name>End of spirit</name>
    </book>
    <book>
        <author>3</author>
        <name>Standard</name>
    </book>

####1. Read XML parsed content into a data base table:
   1.1. PHP script should read XML files information and add it to PostgreSQL two database tables: “authors” and “books” (use 1:many and unique author’s ID as link between the tables). XML files content should be displayed as a result.
   
1.2. If a record from specified file and subfolder already exists PHP script has to update the record and not to insert it as a new one.

####2. XML files should contain Cyrilic, Korean and Japanese symbols as well.

####3. Create simple page with a search form (should search by author only from data base). Result should be printed right after search form. Search word should be populated to the input after submitting for better user experience. Data grid (result) should display the author and assigned books. Please use single sql query. Example end result:


    Pavel Vejinov	Book 1
    Pavel Vejinov	Book 2
    Ivan Penev	<none> (no books found)
    Blaga Dimitrova	Book Book 1

Result design requirements: each row should slide from left to right one after another with some small animated delay.

Motion (animation) slide example:

![img](https://i.ibb.co/LNGvcfJ/img1.png)


Etc.

####4. PHP script is supposed to be executed regularly as a Cron Job (Scheduled Task).

Optional advanced level addition: Detect whether there is a large amount of data and speed up global Cron Job 3 (four) or more times. Optimize search result time which will affect when having large amount of data.

####5. Requirements:
-	Please use object oriented prorgamming;
-	Please do not use ready-made frameworks. Use your own PHP codes only;
-	Please write short description of test project;
-	Please use HTML5 + CSS3 for design purposes;
-	Please use only native JavaScript;
-	Partial task solution is an option as well;
-	Task additional questions are welcome;
-	Test for unpredicted sutiations.

The deadline for fulfilling the task is 5 days.


---

## Project implementation

###Creating tables:

    host: localhost
    database name: testtask-t
    user: postgres
    password: 12345

![img](https://i.ibb.co/djqnksh/img2.png)

- __authors__ - has a unique `name` field (there cannot be authors with the same names). The name field automatically has an index because of the unique constraint that is created using the constraint unique_name unique. This speeds up the search.


    create table if not exists authors
    (
    id  integer default nextval('author_id_seq'::regclass) not null
         constraint author_pkey
         primary key,
    name  varchar(255)
         constraint unique_name
         unique,
    created_at  timestamp  not null,
    updated_at  timestamp  not null
    );

    alter table authors
        owner to postgres;

- __books__ - there may be a situation when different authors have books with the same title. Therefore, what is unique about this table is the combination of book title and author. To speed up the search by author id, an index has been added to this field.


    create table if not exists books
    (
    id  serial
        primary key,
    title  varchar(255),
    author_id  integer
        references authors,
    created_at  timestamp not null,
    updated_at  timestamp not null,
    constraint unique_title_author
        unique (title, author_id)
    );

    alter table books
      owner to postgres;

    CREATE INDEX idx_author_id ON books (author_id);



###Project architecture
One page. Interaction with the backend is carried out via AJAX.
For this project I used the MVC model.
All requests from the frontend go to action.php, which is both a router and a loader. There, all components are connected and it is determined which action the user requested. Then the controller for this action is called, in which the model is accessed.
I created a based Database class in which the connection to the database occurs. From him I extend two models: Book and Author. All database operations take place only in models.
The frontend is responsible for the work of the view.


###The project is divided into three functional sections

![img](https://i.ibb.co/JFsRCqz/img33.png)

####1. Creating directory structures and XML files in them.

It is assumed that all XML files will be located in the data directory and have the following structure:

    <?xml version="1.0" encoding="UTF-8"?>
    <books>
        <book>
            <author>Haruki Murakami-57</author>
            <title>서울의 달-449</title>
        </book>
        <book>
            <author>Александр Пушкин-86</author>
            <title>Norwegian Wood-317</title>
        </book>
    </books>

The number of files in the directory and the number of entries in the file can be configured. You can also determine the nesting depth of subdirectories.

![img](https://i.ibb.co/C2tCty0/img3.png)

After successful creation, the following message will appear:

![img](https://i.ibb.co/K6Hn9pp/img4.png)


####2. Parsing XML files.

To speed up parsing, indexes have been created in the database. Also, data is written to the database in batches of up to 32,767 records at a time. This value is due to the fact that the maximum number of parameters is 65535, and there are 2 parameters for each record.
After parsing is completed, a message will appear and a list of books with authors recorded in the database will be displayed:

![img](https://i.ibb.co/RDk8Kpp/img6.png)


####3. Search.

Produced in the database according to non-strict compliance. For example, "Pav" - all books written by the authors Pavel, Pavlo and others will be displayed.
After the search is completed, a message will appear and a list of books with authors will be displayed:

![img](https://i.ibb.co/2K917X9/img7.png)


#### cron
Additionally, the project includes a cronJob.php file. It is prepared to run on the crown. His job: send a request to parse files.
You can add it to run once an hour like this:

    0 * * * * php /path/to/script/cron.php >/dev/null 2>&1
