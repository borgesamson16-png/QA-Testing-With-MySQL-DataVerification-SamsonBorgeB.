<?php

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "birthday_wishnote";

$conn = new mysqli($servername, $username, $password, $dbname);

$message = "";
$messageType = "";

if ($conn->connect_error) {
    $message = "Error: could not connect to the database.";
    $messageType = "error";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$conn->connect_error) {
    // Capture all four variables from the form
    $FirstName   = $_POST["FirstName"]   ?? "";
    $LastName    = $_POST["LastName"]    ?? "";
    $GovPosition = $_POST["GovPosition"] ?? "";
    $Location    = $_POST["Location"]    ?? ""; // captured, but never saved (see flaw below)

   
    $stmt = $conn->prepare(
        "INSERT INTO main_corrupt_politicians (FirstName, LastName, GovPosition) VALUES (?, ?, ?)"
    );

    if ($stmt) {
        $stmt->bind_param("sss", $FirstName, $LastName, $GovPosition); // Location intentionally missing
        if ($stmt->execute()) {
            $message = "Success! The wish note has been sent.";
            $messageType = "success";
        } else {
            $message = "Error: the wish note could not be saved.";
            $messageType = "error";
        }
        $stmt->close();
    } else {
        // Fallback error if the query fails to prepare
        $message = "Error: the query failed to prepare.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kara David's Wish Note</title>
<style>
  * { box-sizing: border-box; }
  body {
    margin: 0; padding: 20px; min-height: 100vh;
    background: #f0f2f5; font-family: Arial, Helvetica, sans-serif;
    display: flex; align-items: center; justify-content: center;
  }
  .card {
    background: #fff; width: 100%; max-width: 460px; padding: 30px;
    border-radius: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.12);
  }
  h2 { margin: 0 0 20px; color: #1e6fd9; text-align: center; }
  label { display: block; margin: 14px 0 6px; font-weight: bold; color: #333; font-size: 14px; }
  input[type="text"] {
    width: 100%; padding: 10px; border: 1px solid #c5ccd6;
    border-radius: 4px; font-size: 15px;
  }
  input[type="text"]:focus { outline: none; border-color: #1e6fd9; box-shadow: 0 0 0 2px rgba(30,111,217,0.2); }
  button {
    border: none; border-radius: 4px; padding: 11px 18px;
    font-size: 15px; cursor: pointer; color: #fff; transition: background 0.2s;
  }
  .btn-primary { background: #1e6fd9; width: 100%; margin-top: 22px; }
  .btn-primary:hover { background: #1556a8; }
  .btn-cancel { background: #8a94a3; }
  .btn-cancel:hover { background: #6c7684; }
  .alert { padding: 12px; border-radius: 4px; margin-bottom: 16px; font-size: 14px; }
  .alert.success { background: #e3f1e6; color: #1e6b32; border: 1px solid #b8dcc0; }
  .alert.error { background: #fbe4e4; color: #a12626; border: 1px solid #efbcbc; }
  .overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5);
    align-items: center; justify-content: center; padding: 20px; z-index: 10;
  }
  .overlay.show { display: flex; }
  .modal {
    background: #fff; width: 100%; max-width: 420px; padding: 24px;
    border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.25);
  }
  .modal h3 { margin: 0 0 14px; color: #1e6fd9; }
  .modal p { margin: 8px 0; font-size: 14px; word-break: break-word; }
  .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
  .modal-actions .btn-primary { width: auto; margin: 0; }
</style>
</head>
<body>
<div class="card">
  <h2>Kara David's Wish Note</h2>

  <?php if ($message): ?>
    <div class="alert <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <form id="wishForm" method="POST" action="">
    <label for="FirstName">Target's First Name</label>
    <input type="text" id="FirstName" name="FirstName" maxlength="30" required>

    <label for="LastName">Target's Last Name</label>
    <input type="text" id="LastName" name="LastName" maxlength="30" required>

    <label for="GovPosition">Target's Government Position</label>
    <input type="text" id="GovPosition" name="GovPosition" maxlength="20" required>

    <label for="Location">Target's Location</label>
    <input type="text" id="Location" name="Location" maxlength="40">

    <button type="submit" class="btn-primary">Send Wish Note</button>
  </form>
</div>

<div class="overlay" id="overlay">
  <div class="modal">
    <h3>Verify Target Data</h3>
    <p><strong>First Name:</strong> <span id="vFirst"></span></p>
    <p><strong>Last Name:</strong> <span id="vLast"></span></p>
    <p><strong>Government Position:</strong> <span id="vPos"></span></p>
    <p><strong>Location:</strong> <span id="vLoc"></span></p>
    <div class="modal-actions">
      <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
      <button type="button" class="btn-primary" id="confirmBtn">Confirm &amp; Send</button>
    </div>
  </div>
</div>

<script>
  const form = document.getElementById("wishForm");
  const overlay = document.getElementById("overlay");

  form.addEventListener("submit", function (e) {
    e.preventDefault(); // intercept the submission
    document.getElementById("vFirst").textContent = form.FirstName.value;
    document.getElementById("vLast").textContent = form.LastName.value;
    document.getElementById("vPos").textContent = form.GovPosition.value;
    document.getElementById("vLoc").textContent = form.Location.value || "(none)";
    overlay.classList.add("show");
  });

  document.getElementById("cancelBtn").addEventListener("click", function () {
    overlay.classList.remove("show");
  });

  document.getElementById("confirmBtn").addEventListener("click", function () {
    form.submit(); // bypasses the interception
  });
</script>
</body>
</html>
