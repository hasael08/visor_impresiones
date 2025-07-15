<?php
$filteredResults = [];
$errorMessage = '';

// Comprobar si se ha subido un archivo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['txtFile']) && $_FILES['txtFile']['error'] === UPLOAD_ERR_OK) {
        // Leer el archivo subido
        $fileContent = file_get_contents($_FILES['txtFile']['tmp_name']);

        // Separar el contenido en objetos JSON individuales
        preg_match_all('/\{.*?\}/s', $fileContent, $matches);

        $dateThreshold = $_POST['dateThreshold'] ?? '2024-11-03';
        $selectedType = $_POST['type'] ?? 'VENTA';

        foreach ($matches[0] as $jsonString) {
            // Decodificar cada cadena JSON
            $impresion = json_decode($jsonString, true);

            // Verificar si la decodificación fue exitosa y los campos necesarios están presentes
            if ($impresion && isset($impresion['impresion_fechainicio'], $impresion['impresion_tipo'])) {
                // Comparar la fecha y el tipo
                if ($impresion['impresion_fechainicio'] > $dateThreshold && $impresion['impresion_tipo'] === $selectedType) {
                    $filteredResults[] = $impresion;
                }
            }
        }
    } else {
        $errorMessage = "No se ha seleccionado un archivo o ha ocurrido un error al subir el archivo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Subir y Filtrar Impresiones</title>
    <style>
        /* Main styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .container,
        .results {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .container {
            width: 300px;
            text-align: center;
        }

        label,
        select,
        input,
        button {
            display: block;
            width: 100%;
            margin: 10px 0;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .results {
            margin-left: 20px;
            flex: 1;
            max-height: 80vh;
            overflow-y: auto;
        }

        .error {
            color: red;
        }

        /* Styling for JSON display and button alignment */
        .result-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .result-item pre {
            margin: 0;
            flex: 1;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            overflow-y: auto;
            max-height: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Subir y Filtrar Impresiones</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <label for="txtFile">Seleccionar un archivo TXT:</label>
            <input type="file" name="txtFile" id="txtFile" accept=".txt" required>

            <label for="dateThreshold">Seleccionar fecha:</label>
            <input type="date" name="dateThreshold" id="dateThreshold" value="2024-11-03" required>

            <label for="type">Seleccionar tipo:</label>
            <select name="type" id="type">
                <option value="VENTA">VENTA</option>
                <option value="COMANDA">COMANDA</option>
                <option value="PRECUENTA">PRECUENTA</option>
            </select>

            <button type="submit">Filtrar</button>
        </form>
        <?php if ($errorMessage): ?>
            <div class="error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
    </div>

    <div class="results">
        <h2>Resultados Filtrados</h2>
        <?php if (!empty($filteredResults)): ?>
            <?php foreach ($filteredResults as $result): ?>
                <div class="result-item">
                    <pre><?php echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                    <button onclick="showModal(<?php echo htmlspecialchars(json_encode($result['impresion_mensaje'])); ?>)">Ver Mensaje</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay resultados que mostrar.</p>
        <?php endif; ?>
    </div>

    <!-- Modal HTML -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <pre id="modal-message"></pre>
        </div>
    </div>

    <!-- JavaScript for Modal -->
    <script>
        function showModal(message) {
            document.getElementById("modal-message").textContent = message;
            document.getElementById("modal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("modal").style.display = "none";
        }
    </script>
</body>

</html>
