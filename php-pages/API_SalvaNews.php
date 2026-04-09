<?php
$target_dir = "../assets/images/";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recupero dati dal form (assicurati che i 'name' nel form HTML siano questi)
    $titolo = $_POST['titolo'] ?? '';
    $testo = $_POST['contenuto'] ?? ''; // Nel DB si chiama 'testo'
    $inEvidenza = isset($_POST['in_home']) ? 1 : 0; // Nel DB si chiama 'inEvidenza'
    $newsId = $_POST['id_news'] ?? null; 
    
    $immagine_path = "";

    // 2. Gestione Caricamento Immagine
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['immagine']['tmp_name'];
        $original_name = basename($_FILES['immagine']['name']);
        $file_type = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if(getimagesize($tmp_name) !== false) {
            $new_filename = "news_" . time() . "." . $file_type;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($tmp_name, $target_file)) {
                // Salviamo il percorso relativo corretto per il DB
                $immagine_path = "assets/images/" . $new_filename; 
            }
        }
    }

    require_once "../php-dbManager/DBConnection.php";
    $conn = DBConnection::getConnessione();

    // 3. Logica SQL con i nomi corretti del tuo Database
    if (isset($_POST['elimina']) && $_POST['elimina'] == 'si') {
        // CANCELLAZIONE
        $sql = "DELETE FROM NEWS WHERE idNews = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $newsId);
    } 
    else if ($newsId) {
        // MODIFICA
        if ($immagine_path != "") {
            $sql = "UPDATE NEWS SET titolo=?, testo=?, immagine=?, inEvidenza=? WHERE idNews=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssii", $titolo, $testo, $immagine_path, $inEvidenza, $newsId);
        } else {
            $sql = "UPDATE NEWS SET titolo=?, testo=?, inEvidenza=? WHERE idNews=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $titolo, $testo, $inEvidenza, $newsId);
        }
    } 
    else {
        // INSERIMENTO
        $idAutore = 1; // ID dell'admin Anna Marini
        $sql = "INSERT INTO NEWS (titolo, testo, immagine, idAutore, inEvidenza) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $titolo, $testo, $immagine_path, $idAutore, $inEvidenza);
    }
    
    // 4. Esecuzione e generazione messaggio esito
    if (isset($stmt) && $stmt->execute()) {
        $messaggio_esito = "<div class='success'>Operazione completata con successo!</div>";
        $stmt->close();
    } else {
        $messaggio_esito = "<div class='error'>Errore durante l'aggiornamento del database.</div>";
    }
    $conn->close();

    // 5. Ricarico l'area admin con il messaggio (Stile Registrazione)
    $pagina_html = file_get_contents('../html/AreaAdmin.html');
    $pagina_finita = str_replace('[messaggio_esito]', $messaggio_esito, $pagina_html);
    echo $pagina_finita;
    exit();
}
?>