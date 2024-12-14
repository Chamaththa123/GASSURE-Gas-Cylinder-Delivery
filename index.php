<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Page Form</title>
    <style>
    /* body {
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        background-color: #f3f3f3;
    }

    .form-container {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
    }

    .form-page {
        display: none;
    }

    .form-page.active {
        display: block;
    }

    .form-footer {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }

    button {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        background: #007bff;
        color: white;
        cursor: pointer;
        font-size: 16px;
    }

    button:disabled {
        background: #ccc;
    }

    .result {
        margin-top: 20px;
        padding: 10px;
        background: #e7f5e6;
        border: 1px solid #b2d8b0;
        border-radius: 5px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
        padding: 8px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    } */
    </style>
</head>

<body>
<?php include './includes/header.php'; ?>
    <!-- <div class="form-container">
        <form id="multiPageForm">
            <div class="form-page active" id="page1">
                <label for="field1">Field 1:</label>
                <input type="text" id="field1" name="field1" required><br>

                <label for="field2">Field 2:</label>
                <input type="text" id="field2" name="field2" required><br>

                <label for="field3">Field 3:</label>
                <input type="text" id="field3" name="field3" required>
            </div>

            <div class="form-page" id="page2">
                <label for="field4">Field 4:</label>
                <input type="text" id="field4" name="field4" required><br>

                <label for="field5">Field 5:</label>
                <input type="text" id="field5" name="field5" required><br>

                <label for="field6">Field 6:</label>
                <input type="text" id="field6" name="field6" required>
            </div>

            <div class="form-footer">
                <button type="button" id="prevButton" disabled>Previous</button>
                <button type="button" id="nextButton">Next</button>
                <button type="submit" id="submitButton" style="display: none;">Submit</button>
            </div>
        </form>

        <div class="result" id="result" style="display: none;"></div>
    </div> -->

    <script>
    const pages = document.querySelectorAll('.form-page');
    const nextButton = document.getElementById('nextButton');
    const prevButton = document.getElementById('prevButton');
    const submitButton = document.getElementById('submitButton');
    const resultDiv = document.getElementById('result');
    let currentPage = 0;

    function updatePage() {
        pages.forEach((page, index) => {
            page.classList.toggle('active', index === currentPage);
        });
        prevButton.disabled = currentPage === 0;
        nextButton.style.display = currentPage === pages.length - 1 ? 'none' : 'inline-block';
        submitButton.style.display = currentPage === pages.length - 1 ? 'inline-block' : 'none';
    }

    nextButton.addEventListener('click', () => {
        currentPage++;
        updatePage();
    });

    prevButton.addEventListener('click', () => {
        currentPage--;
        updatePage();
    });

    document.getElementById('multiPageForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        resultDiv.style.display = 'block';
        resultDiv.innerHTML = `
      <h4>Form Details:</h4>
      <table>
        <tr><th>Field</th><th>Value</th></tr>
        ${Object.entries(data).map(([key, value]) => `<tr><td>${key}</td><td>${value}</td></tr>`).join('')}
      </table>
    `;
    });

    updatePage();
    </script>

</body>

</html>