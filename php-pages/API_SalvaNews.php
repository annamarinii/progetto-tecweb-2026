<?php
$target_dir = "../assets/images/";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = $_POST['titolo'] ?? '';
    $contenuto = $_POST['contenuto'] ?? '';
    $in_home = isset($_POST['in_home']) ? 1 : 0;
    
    $newsId = $_POST['id_news'] ?? null; // Se presente, è una modifica. Altrimenti è un inserimento.
    $immagine_path = "";
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === UPLOAD_ERR_OK) {
        
        $tmp_name = $_FILES['immagine']['tmp_name'];
        $original_name = basename($_FILES['immagine']['name']);
        $file_type = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        // Sicurezza: Verifichiamo che sia un'immagine valida (scartiamo file malevoli)
        $check = getimagesize($tmp_name);
        if($check !== false) {
            
            // Creiamo un nome unico ed evitiamo collisioni
            // ES: "news_123456789.jpg"
            $new_filename = "news_" . time() . "." . $file_type;
            $target_file = $target_dir . $new_filename;

            // Il server carica l'immagine nella cartella dedicata (../assets/images/)
            if (move_uploaded_file($tmp_name, $target_file)) {
                $immagine_path = $target_file; // Ora l'immagine risiede in assets!
            } else {
                echo "Ops! C'è stato un problema durante il caricamento dell'immagine.";
                exit;
            }
        } else {
            echo "Il file inviato non è un'immagine valida (consentiti solo JPG o PNG).";
            exit;
        }
    }

    require_once "../php-dbManager/DBConnection.php";
    $conn = DBConnection::getConnessione();

    if (isset($_POST['elimina']) && $_POST['elimina'] == 'si') {
        // --- È UNA CANCELLAZIONE ---
        $sql = "DELETE FROM NEWS WHERE idNews = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $newsId);
    } else if ($newsId) {
        // --- È UNA MODIFICA ---
        if ($immagine_path != "") {
             // Aggiorna anche l'immagine
             $sql = "UPDATE NEWS SET titolo=?, testo=?, immagine=? WHERE idNews=?";
             $stmt = $conn->prepare($sql);
             $stmt->bind_param("sssi", $titolo, $contenuto, $immagine_path, $newsId);
        } else {
             // Lascia l'immagine intatta
             $sql = "UPDATE NEWS SET titolo=?, testo=? WHERE idNews=?";
             $stmt = $conn->prepare($sql);
             $stmt->bind_param("ssi", $titolo, $contenuto, $newsId);
        }
    } else {
        $idAutore = 1; // Default admin
        $sql = "INSERT INTO NEWS (titolo, testo, immagine, idAutore, data_pubblicazione) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $titolo, $contenuto, $immagine_path, $idAutore);
    }
    
    if (isset($stmt)) {
        $stmt->execute();
        $stmt->close();
    }
    $conn->close();

    // Torna indietro passivamente all'interfaccia 
    header("Location: AreaAdmin.php");
    exit();
}
