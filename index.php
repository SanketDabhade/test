<?php 
require_once __DIR__ . '/controllers/CodeController.php'; 

$controller = new CodeController();
$history = $controller->index();

if(isset($_GET['flg']) && $_GET['flg'] == 1){
    $succmsg = "Please check explaination below";
}

//If FORM is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $complexity = isset($_POST['complexity']);
    if (!trim($code)) {
        $errormsg = "Code cannot be empty";
    }else{
        $codelang = CodeController::checkLanguage($code);
        if($codelang == 'Unknown'){
            $errormsg = "Unsupported or unclear language. Only Python and JavaScript are allowed.";    
        }else{
            $controller->explain($code,$complexity);
        }
    }
}

function generateDiff($old, $new) {
    $oldLines = explode("\n", trim($old));
    $newLines = explode("\n", trim($new));

    $diff = [];

    $max = max(count($oldLines), count($newLines));

    for ($i = 0; $i < $max; $i++) {
        $o = $oldLines[$i] ?? '';
        $n = $newLines[$i] ?? '';

        if ($o === $n) {
            $diff[] = "<div>{$o}</div>";
        } else {
            if ($o !== '') {
                $diff[] = "<div style='background:#ffe6e6;'>- {$o}</div>";
            }
            if ($n !== '') {
                $diff[] = "<div style='background:#e6ffe6;'>+ {$n}</div>";
            }
        }
    }

    return implode("", $diff);
}
?>
<html>
    <head>
        <title>AI Code Explainer</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </head>
    <body class="container">
    <h2>AI Code Explainer</h2>

    <form method="POST">
        <textarea name="code" rows="10" cols="80"></textarea><br><br>

        <label>
            <input type="checkbox" name="complexity">
            Include Complexity
        </label><br><br>
        <?php 
        if(isset($errormsg) && !empty($errormsg)){
            echo "<div class='alert alert-danger'>$errormsg</div>";
        } 
        
        if(isset($succmsg) && !empty($succmsg)){
            echo "<div class='alert alert-success'>$succmsg</div>";
        }
        ?>
        <button class="btn btn-md btn-success" type="submit">Explain</button>
    </form>

    <hr>

    <h3>History</h3>

    <?php 
        //print_r($history);
    foreach ($history as $item){ 
        $result = json_decode($item['history'], true);
    ?>
        <div class="card" style="border:1px solid #ccc;padding:10px;margin-bottom:20px;">

            <strong>Language:</strong> <?= $result['language']?><br><br>

            <strong>Original Code:</strong>
            <pre><?= CodeController::highlightCode(htmlspecialchars($item['code'])) ?></pre>

            <strong>Explanation:</strong>
            <p><?= htmlspecialchars($result['explanation']) ?></p>

            <strong>Optimized Code:</strong>
            <pre style="background:#f4f4f4;">
            <?= htmlspecialchars($result['optimized_code']) ?>
            </pre>

            <strong>Diff (Original vs Optimized):</strong>
            <div style="font-family: monospace; border:1px solid #ccc; padding:10px;">
                <?php
                if($result['optimized_code'] !== "N/A"){
                    echo generateDiff($item['code'], $result['optimized_code']); 
                }
                ?>
            </div>

            <strong>Complexity:</strong>
            <p><?= htmlspecialchars($result['complexity']) ?></p>

        </div>
    <?php } ?>
</body>
</html>