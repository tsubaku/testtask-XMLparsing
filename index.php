<!DOCTYPE html>
<html lang="en">

<head>
    <title>Test tasks</title>
    <meta charset="utf-8"/>
    <script type="text/javascript" src="Resources/js/ajax.js"></script>
    <link rel="stylesheet" href="Resources/css/style.css"/>
</head>

<body>
<h1>Test task</h1>
<hr>

<div class="input-block">
    <h4>Delete old xml files and create new ones</h4>
    <div class="input-form">
        <label for="minAmountXml">Minimum XML files:</label>
        <input type="number" id="minAmountXml" value="1">
    </div>
    <div class="input-form">
        <label for="maxAmountXml">Maximum XML files:</label>
        <input type="number" id="maxAmountXml" value="1">
    </div>
    <div class="input-form">
        <label for="minElementsInXml">Minimum elements in one XML:</label>
        <input type="number" id="minElementsInXml" value="5000">
    </div>
    <div class="input-form">
        <label for="maxElementsInXml">Maximum elements in one XML:</label>
        <input type="number" id="maxElementsInXml" value="5000">
    </div>
    <div class="input-form">
        <label for="depth">Directory nesting depth:</label>
        <input type="number" id="depth" value="1">
    </div>
    <button id="reset-xml">Reset XML</button>
</div>


<hr>

<div class="input-block">
    <h4>Parse XML files and write data to the database</h4>
    <button id="parse-xml">Parse XML</button>
</div>

<hr>

<div class="input-block">
    <h4>Search for author's books in the database (non-strict matching)</h4>
    <label> Search
        <input type="text" id="query" placeholder="type author name" value="Pavel">
    </label>
    <button id="search">Search</button>
</div>
<hr>

<h3 id="message"></h3>

<div class="input-block">
    <div id="table-container"></div>
</div>


<div id="status" class="status"></div>

<script type="text/javascript" src="Resources/js/main.js"></script>

</body>
</html>
