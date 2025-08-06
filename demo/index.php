<?php
// This is the code of the demo created for the hackathon and hosted in:  https://tiagoc102.sg-host.com/
// it uses ollama.php that links with the open router and Gemma3N


function readCsv($filename, $limit = 5) {
    $rows = array_map('str_getcsv', file($filename));
    $header = array_shift($rows);
    $rows = array_slice($rows, 0, $limit); // Only first 5
    return [$header, array_map(fn($r) => array_combine($header, $r), $rows)];
}


[$columns, $data] = readCsv('user_data.csv');

// Add empty editable row 6
$emptyRow = array_fill_keys($columns, '');
$data[] = $emptyRow;

header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html>
<head>
    <title>3Nhance - Data Gap Simulator</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        td, th { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .gap { background-color: #f99; }
        input { width: 100%; padding: 4px; box-sizing: border-box; }
        .box { border: 1px solid #ccc; padding: 10px; margin: 10px 0; min-height: 100px; }
        .btn { padding: 8px 12px; cursor: pointer; }
        textarea {
            width: 100%;
            height: 200px;
            padding: 10px;
            box-sizing: border-box;
            margin-bottom: 10px;
            font-family: monospace;
        }
        .loading {
            color: #555;
            font-style: italic;
        }
    </style>

</head>
<body>

<h2>3Nhance Data 📊 Concept Demo</h2>

<h3>📝 Step 1: Fill the <b>Row 6</b> (but leave some data gaps)</h3>

<table id="dataTable">
    <tr>
        <th>#</th>
        <?php foreach ($columns as $col): ?>
            <th><?= htmlspecialchars($col) ?></th>
        <?php endforeach; ?>
    </tr>
    <?php foreach ($data as $rowIndex => $row): ?>
        <tr>
            <td><?= $rowIndex+1 ?>:</td>
            <?php foreach ($columns as $col): ?>
                <?php if ($rowIndex < 5): ?>
                    <td class="<?= empty($row[$col]) ? 'gap' : '' ?>">
                        <?= htmlspecialchars($row[$col]) ?>
                    </td>
                <?php else: ?>
                    <td>
                        <input type="text" name="<?= htmlspecialchars($col) ?>" oninput="removeRed(this)">
                    </td>
                <?php endif; ?>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
</table>



<h3>📝 Step 2: Draft Email to user in row 6 (using <b>Gemma3N</b>)</h3>
<button class="btn" onclick="draftEmail()">✉️ Generate Email</button>
<div id="subjectResult" class="loading"></div>
<div id="emailResult" class="box"></div>
<div id="draftTime" class="loading"></div>

<h3>💬 Step 3: Simulate Reply </h3>
<textarea id="userReply" placeholder="Simulate the user reply here..."></textarea>
<button class="btn" onclick="sendReply()">🚀 Send Reply</button>

<h3>📦 Step 4: Final Cleaned Output (using <b>Gemma3N</b>)</h3>
<div id="finalResult" class="box"></div>
<div id="totalTime" class="loading"></div>

<script>
let draftStartTime, cleanStartTime, draftDuration;

function removeRed(input) {
    if (input.value.trim()) {
        input.classList.remove('gap');
    } else {
        input.classList.add('gap');
    }
}

function draftEmail() {
    draftStartTime = performance.now();
    document.getElementById('draftTime').innerText = '⏳ Generating draft email...';
    document.getElementById('emailResult').innerText = '';
    document.getElementById('subjectResult').innerText = '⏳';

    // Collect input values from last row
    const inputs = document.querySelectorAll('#dataTable tr:last-child input');
    const rowData = {};
    inputs.forEach(input => rowData[input.name] = input.value.trim());
    const emptyKeys = Object.keys(rowData).filter(key => rowData[key] === '');

    const specialPrompt = `Your name is Luisa and you are a helpful assistant designed to close the data gap in our dataset.` +
    `Context: The company name is Blabla and it focus on training. It's based in Abu Dhabi (UAE). Tone: Professional and Polite tone for all conversations.` +
    `\nBased on the information in this row:\n` +  JSON.stringify(rowData, null, 2)  + 
    `Create an email to request the missing fields (without signature). Do not add anything else besides JSON: { subject: emailsubject, email: content without signature}`;

    fetch('ollama.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ prompt: specialPrompt })
    })
    .then(res => res.text()) // ← Expect plain text
    .then(rawText => {
    const elapsed = performance.now() - draftStartTime;
    draftDuration = elapsed.toFixed(2);

    // Step 1: Extract JSON inside ```json ... ```
    const jsonMatch = rawText.match(/```json\s*([\s\S]+?)\s*```/i);
    const jsonString = jsonMatch ? jsonMatch[1] : rawText;

    // Step 2: Decode HTML entities like &quot;
    const htmlDecoder = new DOMParser().parseFromString(jsonString, "text/html");
    const decodedJson = htmlDecoder.documentElement.textContent;

    // Step 3: Parse JSON safely
    let parsed;
    try {
        parsed = JSON.parse(decodedJson);
    } catch (e) {
        document.getElementById('emailResult').innerText = `⚠️ Error parsing JSON: ${e.message}`;
        document.getElementById('draftTime').innerText = '';
        return;
    }

    // Step 4: Extract subject and email
    const subject = parsed.subject || 'No Subject';
    const emailBody = (parsed.email || '').replace(/\n/g, '<br>');

    // Step 5: Display nicely
    document.getElementById('subjectResult').innerHTML = `<strong>📌 Subject:</strong> ${subject}`;
    document.getElementById('emailResult').innerHTML = emailBody;
    
    document.getElementById('draftTime').innerText = `⏱️ Draft generation took: ${draftDuration} ms`;
    })
    .catch(err => {
        document.getElementById('emailResult').innerText = `⚠️ Fetch error: ${err.message}`;
        document.getElementById('subjectResult').innerText = `⚠️ Fetch error: ${err.message}`;
        document.getElementById('draftTime').innerText = '';
    });

}

function sendReply() {
    draftStartTime2 = performance.now();
    const reply = document.getElementById('userReply').value;
    if (!reply.trim()) {
        alert("Please write a simulated reply.");
        return;
    }

    // Collect input values from last row
    const inputs = document.querySelectorAll('#dataTable tr:last-child input');
    const rowData = {};
    inputs.forEach(input => rowData[input.name] = input.value.trim());
    const emptyKeys = Object.keys(rowData).filter(key => rowData[key] === '');


    cleanStartTime = performance.now();
    document.getElementById('totalTime').innerText = '⏳ Cleaning reply and extracting data...';
    document.getElementById('finalResult').innerText = '';

    const cleanPrompt = `Based on this reply:\n\"${reply}"\nExtract the information from the email based on the missing fields (${emptyKeys}) into a JSON array:\n[\n {\n  field: nameoffield1,\n  value: value from email\n },\n {\n  field: nameoffield2,\n  value: value from the email\n }...\n]\nDon't include anything else besides it.`;
    //const cleanPrompt = `The following reply was received. Please extract and clean data into JSON format:\n\n"${reply}"`;

    console.log(cleanPrompt);

    fetch('ollama.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ prompt: cleanPrompt })
    })
    .then(res => res.text())
    .then(rawText => {
    const elapsed = performance.now() - draftStartTime2;
    cleanDuration = elapsed.toFixed(2);
    total = ( parseFloat(draftDuration) || 0 ) + ( parseFloat(cleanDuration) || 0 ); 

    // Step 1: Extract JSON inside ```json ... ```
    const jsonMatch = rawText.match(/```json\s*([\s\S]+?)\s*```/i);
    const jsonString = jsonMatch ? jsonMatch[1] : rawText;

    // Step 2: Decode HTML entities like &quot;
    const htmlDecoder = new DOMParser().parseFromString(jsonString, "text/html");
    const decodedJson = htmlDecoder.documentElement.textContent;
    document.getElementById('finalResult').innerText = decodedJson ?? "Error!";
    document.getElementById('totalTime').innerText =
        `⏱️ Data cleaning took: ${cleanDuration} ms | ⏱️ Total time: ${total} ms`;
    });
}
</script>

<div id="Footer" class="loading">This is just a quick demo also using GEMMA3N, with a similar prompt. It's powered by OpenRouter</div>


</body>
</html>

