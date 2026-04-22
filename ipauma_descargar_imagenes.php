<?php
require_once 'includes/dbconnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $solicitud_id = isset($_POST['solicitud_id']) ? intval($_POST['solicitud_id']) : 0;
    $img_ids = isset($_POST['img_ids']) ? $_POST['img_ids'] : [];
    
    $where = "";
    $params = [];
    
    if (!empty($img_ids)) {
        // Preparar marcadores para descargar solo las seleccionadas
        $placeholders = implode(',', array_fill(0, count($img_ids), '?'));
        $where = "WHERE id IN ($placeholders)";
        $params = $img_ids;
    } elseif ($solicitud_id > 0) {
        // Descargar todas las del reporte
        $where = "WHERE solicitud_id = ?";
        $params = [$solicitud_id];
    } else {
        die("No se seleccionaron imágenes.");
    }

    $stmt = $pdo->prepare("SELECT ruta_archivo, nombre_archivo FROM ipauma_imagenes $where");
    $stmt->execute($params);
    $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($imagenes) > 0) {
        $zip = new ZipArchive();
        $zip_name = "imagenes_reporte_" . ($solicitud_id ?: 'seleccion') . "_" . time() . ".zip";
        $zip_path = sys_get_temp_dir() . "/" . $zip_name;

        if ($zip->open($zip_path, ZipArchive::CREATE) === TRUE) {
            foreach ($imagenes as $img) {
                if (file_exists($img['ruta_archivo'])) {
                    $zip->addFile($img['ruta_archivo'], $img['nombre_archivo']);
                }
            }
            $zip->close();
            
            header('Content-Type: application/zip');
            header('Content-disposition: attachment; filename='.$zip_name);
            header('Content-Length: ' . filesize($zip_path));
            readfile($zip_path);
            unlink($zip_path);
            exit;
        } else {
            die("No se pudo crear el archivo ZIP.");
        }
    } else {
        echo "<script>alert('No se encontraron imágenes físicas en el servidor.'); window.history.back();</script>";
    }
}
?>