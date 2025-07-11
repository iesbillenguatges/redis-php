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
<body>
    <h1>🚗 Base de Dades de Cotxes (Redis)</h1>

    <h2>➕ Alta de cotxe</h2>
    <form method="POST">
        Marca: <input type="text" name="marca" required><br>
        Model: <input type="text" name="model" required><br>
        Combustible: <input type="text" name="combustible" required><br>
        <button type="submit" name="alta">Afegir</button>
    </form>

    <h2>📋 Llistat de cotxes</h2>
    <ul>
        <?php
        $ids = $redis->sMembers('cotxes:ids');
        foreach ($ids as $id) {
            $json = $redis->get($id);
            if (!$json) continue;
            $dades = json_decode($json, true);
            echo "<li>{$dades['marca']} {$dades['model']} ({$dades['combustible']})
                    <form method='POST' style='display:inline'>
                        <input type='hidden' name='id' value='$id'>
                        <button type='submit' name='baixa'>❌</button>
                    </form>
                  </li>";
        }
        ?>
    </ul>
</body>
</html>
