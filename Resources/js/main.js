let resetButton = document.getElementById('reset-xml');
resetButton.addEventListener('click', function () {
    resetXml();
});


let parseButton = document.getElementById('parse-xml');
parseButton.addEventListener('click', function () {
    parseXml();
});


let searchButton = document.getElementById("search");
searchButton.addEventListener("click", function () {
    search();
});


function resetXml() {
    let minAmountXml = document.getElementById('minAmountXml').value;
    let maxAmountXml = document.getElementById('maxAmountXml').value;
    let minElementsInXml = document.getElementById('minElementsInXml').value;
    let maxElementsInXml = document.getElementById('maxElementsInXml').value;
    let depth = document.getElementById('depth').value;

    if (!validatePositive(minAmountXml) || !validatePositive(maxAmountXml) ||
        !validatePositive(minElementsInXml) || !validatePositive(maxElementsInXml) ||
        !validatePositive(depth)) {
        showMessage('Only non-negative numbers!');
        return false;
    }

    if (!validateMinMax(minAmountXml, maxAmountXml) || !validateMinMax(minElementsInXml, maxElementsInXml)) {
        showMessage('The maximum value must be greater than the minimum!');
        return false;
    }

    ajax({
        url: "action.php",
        type: "POST",
        statbox: "status",
        data:
            {
                action: 'reset-xml',
                minAmountXml: minAmountXml,
                maxAmountXml: maxAmountXml,
                minElementsInXml: minElementsInXml,
                maxElementsInXml: maxElementsInXml,
                depth: depth
            },
        success: function (data) {
            document.getElementById("status").innerHTML = '';
            console.log(data);

            showMessage(JSON.parse(data).msg);
        },
        error: function (error) {
            document.getElementById("status").innerHTML = '';
            console.log("Error reset-xml");
        }
    })
}


function parseXml() {
    removeResultTable();

    ajax({
        url: "action.php",
        type: "POST",
        statbox: "status",
        data:
            {action: 'parse-xml'},
        success: function (data) {
            console.log("ok : " + data);
            document.getElementById("status").innerHTML = '';
            showMessage(JSON.parse(data).msg);

            let books = JSON.parse(data).result
            books = Object.values(books); //object Of Objects -> array Of Objects

            let tableTitle = 'Parsing results (last 100 records)';
            showResultTable(books, tableTitle)
        },
        error: function (error) {
            console.log("Error parse-xml");
            document.getElementById("status").innerHTML = '';
        }
    })
}

function search() {
    removeResultTable();

    let query = document.getElementById('query').value;
    ajax({
        url: "action.php",
        type: "POST",
        statbox: "status",
        data:
            {
                action: 'search',
                query: query
            },
        success: function (data) {
            console.log("ok : |" + data + "|");
            document.getElementById("status").innerHTML = '';
            showMessage(JSON.parse(data).msg)

            let dataObj = JSON.parse(data)
            let books = dataObj.result

            if (typeof books !== "string") {
                let tableTitle = 'Search results';
                showResultTable(books, tableTitle)
            }

        },
        error: function (error) {
            console.log("Error search");
        }
    })
}

function validateMinMax(min, max) {
    return min <= max;
}

function validatePositive(amount) {
    return amount >= 0;
}

function showMessage(msg) {
    let message = document.getElementById("message");
    message.innerHTML = msg;

    setTimeout(function () {
        message.style.opacity = "1";
    }, 0);

    setTimeout(function () {
        message.style.opacity = "0";
    }, 5000);
}

function removeResultTable() {
    let container = document.getElementById("table-container");
    container.innerHTML = ''
}

function showResultTable(books, tableTitle) {
    let container = document.getElementById("table-container");

    let table = document.createElement("table");

    var caption = document.createElement('caption');
    caption.textContent = tableTitle;
    table.appendChild(caption);

    let thead = document.createElement("thead");
    let headerRow = document.createElement("tr");

    let authorHeader = document.createElement("th");
    authorHeader.textContent = "Author";
    let titleHeader = document.createElement("th");
    titleHeader.textContent = "Title";

    headerRow.appendChild(authorHeader);
    headerRow.appendChild(titleHeader);

    thead.appendChild(headerRow);
    table.appendChild(thead);

    let tbody = document.createElement("tbody");
    if (Array.isArray(books)) {
        books.forEach(function (book) {
            let row = document.createElement("tr");

            let authorCell = document.createElement("td");
            authorCell.textContent = book.author;
            let titleCell = document.createElement("td");
            titleCell.textContent = book.title;

            row.appendChild(authorCell);
            row.appendChild(titleCell);

            tbody.appendChild(row);
        });
    } else {
        console.error("Error: books not array");
    }
    table.appendChild(tbody);
    container.appendChild(table);

    animation();
}


/** Aimation **/
function animation() {
    const rows = Array.from(document.querySelectorAll('tr'));
    rows.forEach(slideOut);
    rows.forEach(slideIn);
}

function slideOut(row) {
    row.classList.add('slide-out');
}

function slideIn(row, index) {
    setTimeout(function () {
        row.classList.remove('slide-out');
    }, (index + 5) * 200);
}

/*******/




