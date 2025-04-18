<?php
include 'connect.php';
session_start(); // Add session_start() to use $_SESSION for error messages

$id = $_GET['updateid'];

$sql = "SELECT * from `students` where id=$id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$name = $row['name'];
$sex = $row['sex'];
$idNumber = $row['idNumber'];
$department = $row['department'];
$campus = $row['campus'];
$pcSerialNumber = $row['pcSerialNumber'];
$pcModel = $row['pcModel'];
$contact = $row['contact'];
$photo = $row['photo'];
$year = $row['year'];

// Check if the form is submitted
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $sex = $_POST['sex'];
    $idNumber = $_POST['idNumber'];
    $department = $_POST['department'];
    $campus = $_POST['campus'];
    $pcSerialNumber = $_POST['pcSerialNumber'];
    $pcModel = $_POST['pcModel'];
    $contact = $_POST['contact'];
    $year = $_POST['year'];
    $name = ucwords(strtolower($name));

    // Handle file upload
    $new_photo = $photo; // Keep the existing photo by default
    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        
        if (in_array($file_ext, $allowed_ext)) {
            // Delete old photo if it exists
            if (!empty($photo) && file_exists($photo)) {
                unlink($photo);
            }
            
            $new_filename = uniqid('photo_', true) . '.' . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                $new_photo = $target_file;
            } else {
                $_SESSION['error'] = 'Error uploading file.';
            }
        } else {
            $_SESSION['error'] = 'Only JPG, JPEG, PNG files are allowed for photo.';
        }
    }
    $sql_check = $conn->query("SELECT id FROM `students` WHERE `id` != '$id' and `idNumber` = '$idNumber';");
    if($sql_check->num_rows > 0) {
        $_SESSION['error'] = 'student By this Id already exist!.';   
    } 
    else {

    // Update the data in the 'students' table using prepared statement
    $sql = $conn->prepare("UPDATE `students` SET name=?, sex=?, idNumber=?, department=?, 
            campus=?, pcSerialNumber=?, pcModel=?, contact=?, photo=?, year=? WHERE id=?");
    $sql->bind_param(
        "ssssssssssi",
        $name,
        $sex,
        $idNumber,
        $department,
        $campus,
        $pcSerialNumber,
        $pcModel,
        $contact,
        $new_photo,
        $year,
        $id
    );

    $result = $sql->execute();
    
    // Check if the query was successful
    if ($result) {
        echo "<script> window.location.href='display.php'; alert('data updated successfully!'); </script>";
        // header(header: "Location: display.php");
        exit();
    } else {
        die(mysqli_error($conn));
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="update.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">
        <!-- Display error messages if any -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <!-- Form to update student data -->
        <div class="header">
            <h2 class="text-primary">Update Student Data</h2>
            <p class="lead">Please fill in the details below to update the student record.</p>
        </div>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group mb-3">
                <label for="name">Name of Student</label>
                <input type="text" class="form-control" id="name" placeholder="Enter Student's Name" name="name" autocomplete="off" value="<?php echo htmlspecialchars($name) ?>" required>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label">Gender</label>
                <select class="form-select" name="sex" required>
                    <option value="Male" <?php echo ($sex == 'Male') ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?php echo ($sex == 'Female') ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="stu id">Student Id</label>
                <input type="text" class="form-control" id="grade" placeholder="Enter stu id" name="idNumber" autocomplete="off" value="<?php echo htmlspecialchars($idNumber) ?>" required minlength="4">
            </div>
            <div class="form-group mb-3">
                <label for="department">Department</label>
                <input type="text" class="form-control" id="section" placeholder="Enter department" name="department" autocomplete="off" value="<?php echo htmlspecialchars($department) ?>" required>
                <div class="invalid-feedback">Please enter correct ID number.</div>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Campus</label>
                <select class="form-select" name="campus" required>
                    <option value="" selected disabled>Select Campus</option>
                    <option value="Main" <?php echo ($campus == 'Main') ? 'selected' : '' ?>>Main</option>
                    <option value="Gende J" <?php echo ($campus == 'HiT') ? 'selected' : '' ?>>HiT</option>
                    <option value="Station" <?php echo ($campus == 'Station') ? 'selected' : '' ?>>Station</option>
                    <option value="Harar" <?php echo ($campus == 'Harar') ? 'selected' : '' ?>>Harar</option>
                </select>
                <div class="invalid-feedback">Please choose campus.</div>
            </div>
            <div class="form-group mb-3">
                <label for="section">Pc Serial Number</label>
                <input type="text" class="form-control" id="section" placeholder="Enter psn" name="pcSerialNumber" autocomplete="off" value="<?php echo htmlspecialchars($pcSerialNumber) ?>" required>
            </div>
            <div class="form-group mb-3">
                <label for="section">Pc pcModel</label>
                <input type="text" class="form-control" id="section" placeholder="Enter pc model" name="pcModel" autocomplete="off" value="<?php echo htmlspecialchars($pcModel) ?>" required>
            </div>
            <div class="form-group mb-3">
                <label for="phone">Contact</label>
                <input type="text" class="form-control" id="phone" placeholder="Enter contact " name="contact" autocomplete="off" value="<?php echo htmlspecialchars($contact) ?>" required>
            </div>
            <div class="form-group mb-3">
                <label for="year" class="form-label">Year </label>
                <select class="form-select" name="year" required>
                    <option value="" selected disabled>Select Year</option>
                    <option value="1" <?php echo ($year == 1) ? 'selected' : '' ?>>First Year</option>
                    <option value="2" <?php echo ($year == 2) ? 'selected' : '' ?>>Second Year</option>
                    <option value="3" <?php echo ($year == 3) ? 'selected' : '' ?>>Third Year</option>
                    <option value="4" <?php echo ($year == 4) ? 'selected' : '' ?>>Fourth Year</option>
                    <option value="5" <?php echo ($year == 5) ? 'selected' : '' ?>>Fifth Year</option>
                    <option value="6" <?php echo ($year == 6) ? 'selected' : '' ?>>Six Year</option>
                    <option value="7" <?php echo ($year == 7) ? 'selected' : '' ?>>Seven Year</option>
                </select>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label">Photo (Optional)</label>
                <input type="file" class="form-control" name="photo" id="photo" accept="image/jpeg, image/png">
                <div class="form-text">Only JPG/PNG images accepted</div>
                <?php if (!empty($photo)): ?>
                    <div class="mt-2">
                        <p>Current Photo:</p>
                        <img src="<?php echo htmlspecialchars($photo) ?>" alt="Current Photo" style="max-width: 200px; max-height: 200px; border: none; border-radius: 10px;">
                    </div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary" name="submit">Update</button>
        </form>
    </div>
    <footer>
        <p>© 2025 Your Company. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>