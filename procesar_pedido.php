<?php
/* procesar_pedido.php
 * Registra un pedido en la base de datos a partir del formulario + carrito.
 */

// ========== CONFIGURACIÓN BD (AJUSTA DB_NAME SI TU BD SE LLAMA DISTINTO) ==========
error_reporting(E_ALL);
ini_set('display_errors', 1);

$DB_HOST = "mysql-beerboost.mysql.database.azure.com";
$DB_NAME = "mysql_beerboost";
$DB_USER = "usuario_azure";   // Oculto por seguridad
$DB_PASS = "password_azure";  // Oculto por seguridad

// Ruta del certificado CA
$CA_CERT = "/var/www/html/DigiCertGlobalRootCA.crt.pem";

try {
    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

    $options = [
        PDO::MYSQL_ATTR_SSL_CA => $CA_CERT,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];

    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);

    echo "CONEXIÓN OK (SSL habilitado)<br>";

} catch (PDOException $e) {
    echo "ERROR DE CONEXIÓN: " . $e->getMessage();
    exit;
}

// Solo aceptamos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Método no permitido.";
    exit;
}

// Campos requeridos
$required_fields = [
    'nombre', 'apellidos', 'email', 'telefono',
    'direccion', 'distrito', 'codigo_postal', 'departamento',
    'numero_tarjeta', 'nombre_tarjeta', 'expiracion', 'cvv',
    'cart_data'
];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        http_response_code(400);
        echo "Falta el campo requerido: $field";
        exit;
    }
}

// Datos del cliente y dirección
$nombre        = trim($_POST['nombre']);
$apellidos     = trim($_POST['apellidos']);
$email         = trim($_POST['email']);
$telefono      = trim($_POST['telefono']);
$direccion     = trim($_POST['direccion']);
$distrito      = trim($_POST['distrito']);
$codigo_postal = trim($_POST['codigo_postal']);
$departamento  = trim($_POST['departamento']);

// Carrito
$cartJson = $_POST['cart_data'];
$carrito  = json_decode($cartJson, true);

if (!is_array($carrito) || count($carrito) === 0) {
    http_response_code(400);
    echo "El carrito está vacío o es inválido.";
    exit;
}

try {
    $pdo->beginTransaction();

    // 1) Cliente: buscar por email
    $stmt = $pdo->prepare("SELECT id_cliente FROM Cliente WHERE email = ?");
    $stmt->execute([$email]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        $id_cliente = (int)$cliente['id_cliente'];

        $upd = $pdo->prepare("
            UPDATE Cliente
            SET nombre = ?, apellidos = ?, telefono = ?, direccion = ?,
                distrito = ?, codigo_postal = ?, departamento = ?
            WHERE id_cliente = ?
        ");
        $upd->execute([
            $nombre, $apellidos, $telefono, $direccion,
            $distrito, $codigo_postal, $departamento, $id_cliente
        ]);
    } else {
        $ins = $pdo->prepare("
            INSERT INTO Cliente
                (nombre, apellidos, email, telefono, direccion, distrito, codigo_postal, departamento)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([
            $nombre, $apellidos, $email, $telefono,
            $direccion, $distrito, $codigo_postal, $departamento
        ]);

        $id_cliente = (int)$pdo->lastInsertId();
    }

    // 2) Calcular total a partir del carrito
    $total = 0.0;
    foreach ($carrito as $item) {
        if (!is_array($item)) {
            continue;
        }
        $precio   = isset($item['precio']) ? (float)$item['precio'] : 0.0;
        $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : 0;

        if ($cantidad <= 0 || $precio < 0) {
            continue;
        }
        $total += $precio * $cantidad;
    }

    if ($total <= 0) {
        throw new Exception("Total de pedido inválido.");
    }

    // 3) Insertar pedido
    $stmtPed = $pdo->prepare("
        INSERT INTO Pedido
            (id_cliente, total, direccion_entrega, distrito_entrega, codigo_postal_entrega, departamento_entrega)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtPed->execute([
        $id_cliente,
        $total,
        $direccion,
        $distrito,
        $codigo_postal,
        $departamento
    ]);

    $id_pedido = (int)$pdo->lastInsertId();

    // 4) Insertar detalles
    $stmtDet = $pdo->prepare("
        INSERT INTO Detalle_Pedido
            (id_pedido, id_producto, cantidad, precio_unitario, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($carrito as $item) {
        if (!is_array($item)) {
            continue;
        }

        $id_producto = isset($item['id']) ? (int)$item['id'] : 0;
        $precio      = isset($item['precio']) ? (float)$item['precio'] : 0.0;
        $cantidad    = isset($item['cantidad']) ? (int)$item['cantidad'] : 0;

        if ($id_producto <= 0 || $cantidad <= 0) {
            continue;
        }

        $subtotal = $precio * $cantidad;

        $stmtDet->execute([
            $id_pedido,
            $id_producto,
            $cantidad,
            $precio,
            $subtotal
        ]);
    }

    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo "Ocurrió un error al procesar el pedido.";
    // Para depurar:
    // echo "<pre>" . $e->getMessage() . "</pre>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Pedido confirmado - BeerBoost</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
        "Roboto", sans-serif;
      background: radial-gradient(circle at top left, #eff6ff, #f9fafb 40%, #f3f4f6);
      color: #111827;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    .card {
      background: #ffffff;
      border-radius: 16px;
      padding: 1.8rem 2rem;
      max-width: 420px;
      width: 100%;
      box-shadow: 0 18px 35px rgba(15, 23, 42, 0.18);
      border: 1px solid rgba(148, 163, 184, 0.3);
      text-align: center;
    }
    h1 {
      margin-top: 0;
      margin-bottom: 0.6rem;
      font-size: 1.4rem;
    }
    p {
      margin: 0.35rem 0;
      font-size: 0.9rem;
    }
    .total {
      margin-top: 0.7rem;
      font-weight: 600;
      font-size: 1.05rem;
      color: #14532d;
    }
    .btn {
      display: inline-flex;
      margin-top: 1.1rem;
      padding: 0.5rem 1rem;
      border-radius: 999px;
      border: none;
      background: linear-gradient(to bottom right, #2563eb, #1d4ed8);
      color: #f9fafb;
      font-size: 0.85rem;
      font-weight: 600;
      text-decoration: none;
      cursor: pointer;
      box-shadow: 0 10px 18px rgba(37, 99, 235, 0.35);
    }
    .btn:hover {
      box-shadow: 0 14px 24px rgba(37, 99, 235, 0.4);
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>¡Gracias por tu pedido, <?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>!</h1>
    <p>Tu pedido se ha registrado correctamente en el sistema.</p>
    <p class="total">Total: S/ <?php echo number_format($total, 2); ?></p>
    <p>Se enviará a: <?php echo htmlspecialchars($direccion . ', ' . $distrito . ', ' . $departamento, ENT_QUOTES, 'UTF-8'); ?></p>
    <a href="index.html" class="btn">Volver a la tienda</a>
  </div>
</body>
</html>
