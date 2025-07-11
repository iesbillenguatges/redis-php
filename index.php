<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$redis = new Redis();
try {
    $redis->connect('redis-16979.c74.us-east-1-4.ec2.redns.redis-cloud.com', 16979, 2.5, NULL, 0, 0, [
        'ssl' => ['verify_peer' => false]
    ]);
    $redis->auth(['user' => 'default', 'pass' => '9g9sOEjVi6XPmyihvq7AjcVc3B8odlOa']);
} catch (RedisException $e) {
    die("❌ Error de connexió a Redis: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['alta'])) {
        $marca = $_POST['marca'] ?? '';
        $model = $_POST['model'] ?? '';
        $combustible = $_POST['combustible'] ?? '';

        if ($marca && $model && $combustible) {
            $id = uniqid("cotxe:");
            $cotxe = [
                'marca' => $marca,
                'model' => $model,
                'combustible' => $combustible
            ];
            $redis->set($id, json_encode($cotxe));
            $redis->sAdd('cotxes:ids', $id);
        }
    }

    if (isset($_POST['baixa']) && isset($_POST['id'])) {
        $id = $_POST['id'];
        $redis->del($id);
        $redis->sRem('cotxes:ids', $id);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestió de Cotxes</title>
</head>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Gestió de Cotxes</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background: #f4f4f4;
            color: #333;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #2c3e50;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 8px;
            margin: 5px 0 15px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #3498db;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background: #ecf0f1;
            margin-bottom: 10px;
            padding: 12px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        li form {
            margin: 0;
        }
        .baixa-btn {
            background-color: #e74c3c;
            margin-left: 10px;
        }
        .baixa-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <h1>Base de Dades de Cotxes (Redis + PHP)</h1>

    <h2>Alta de cotxe</h2>
    <form method="POST">
        <label>Marca:</label>
        <input type="text" name="marca" required>

        <label>Model:</label>
        <input type="text" name="model" required>

        <label>Combustible:</label>
        <input type="text" name="combustible" required>

        <button type="submit" name="alta">Afegir</button>
    </form>

    <h2>Llistat de cotxes</h2>
    <ul>
        <?php
        $ids = $redis->sMembers('cotxes:ids');
        foreach ($ids as $id) {
            $json = $redis->get($id);
            if (!$json) continue;
            $dades = json_decode($json, true);
            echo "<li>
                    <span>{$dades['marca']} {$dades['model']} ({$dades['combustible']})</span>
                    <form method='POST'>
                        <input type='hidden' name='id' value='$id'>
                        <button type='submit' name='baixa' class='baixa-btn'>❌</button>
                    </form>
                  </li>";
        }
        ?>
    </ul>
</body>
</html>
