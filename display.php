<?php
include 'connect.php';
session_start();
$self = $_SERVER['PHP_SELF'];
$current_year = date('Y'); // Current year for filtering

// Handle AJAX student data request (for both edit and info)
if (isset($_GET['get_student'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $sql = "SELECT * FROM students WHERE id = '$id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo json_encode($student);
    } else {
        echo json_encode(['error' => 'Student record not found']);
    }
    exit();
}

/* I use again and again for user input
 built fuction mysqli_real_escape_string
 to escape special char and prevent system from
 crashing and I use for input only under control of user */

 // Handle form submission

if (isset($_POST['submit'])) {
    $name = ucwords(strtolower($_POST['name']));
    $sex = $_POST['sex'];
    $idNumber = mysqli_real_escape_string($conn, $_POST['idNumber']);
    $department = ucwords(strtolower($_POST['department']));
    $campus = $_POST['campus']; // Fixed campus name
    $pcSerialNumber = mysqli_real_escape_string($conn, $_POST['pcSerialNumber']);
    $pcModel = mysqli_real_escape_string($conn, $_POST['pcModel']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $year = $_POST['year'];
    
    // Handle file upload
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('photo_', true) . '.' . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                $photo = $target_file;
            }
        } else {
            $_SESSION['error'] = 'Only JPG, JPEG, PNG files are allowed for photo.';
        }
    }
    $sql_check = "SELECT id FROM `students` WHERE `idNumber` = '$idNumber'";
    $result = $conn->query($sql_check);

    // Validate inputs
    if (empty($name)) {
        $_SESSION['error'] = 'Name is required.';
    } elseif (empty($idNumber)) {
        $_SESSION['error'] = 'ID Number is required.';
    } elseif (empty($department)) {
        $_SESSION['error'] = 'Department is required.';
    } elseif (empty($pcSerialNumber)) {
        $_SESSION['error'] = 'PC Serial Number is required.';
    }
    elseif ($result->num_rows > 0) {
                $_SESSION['error'] = 'This ID Number already exists in the system!';
    }
    else {
        // Insert new student
        $sql = $conn->prepare("INSERT INTO students (name, sex, idNumber, department, campus, pcSerialNumber, pcModel, contact, photo, year) 
                        VALUES (?,?,?,?,?,?,?,?,?,?)");
        $sql->bind_param("ssssssssss",$name,$sex,$idNumber,$department,$campus,$pcSerialNumber,$pcModel,$contact,$photo,$year);
                         if ($sql->execute()) {
                            echo "<script> alert(`New record created successfully`)</script>";
                            header("location: display.php");
                            exit();
                        } else {
                            echo "Error: " . $sql . "<br>" . $conn->error;
                        }
            }
        }
// Handle delete request
if (isset($_GET['deleteid'])) {
    $id = mysqli_real_escape_string($conn, $_GET['deleteid']);
    
    // First get photo path to delete the file
    $sql_photo = "SELECT photo FROM students WHERE id='$id'";
    $result = $conn->query($sql_photo);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!empty($row['photo']) && file_exists($row['photo'])) {
            unlink($row['photo']);
        }
    }
    
    $sql = "DELETE FROM students WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Student record deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting student record: " . $conn->error;
    }
    header("Location: $self");
    exit();
}

$searchQuery = '';
if (isset($_POST['search']) && !empty(trim($_POST['search_query']))) {
    $searchQuery = trim($_POST['search_query']);
    $searchQuery = ucwords(strtolower($searchQuery));
}
$searchTerms = explode('+', $searchQuery);
$whereConditions = [];

foreach ($searchTerms as $term) {
    $term = trim($term); 
    if (!empty($term)) {
        $escapedTerm = mysqli_real_escape_string($conn, $term);  

        $whereConditions[] = "(name LIKE '%$escapedTerm%' OR idNumber LIKE '%$escapedTerm%' OR department LIKE '%$escapedTerm%')";
    }
}

if (!empty($whereConditions)) {
    $whereSql = implode(' OR ', $whereConditions);
    
    $sql = "SELECT * FROM students WHERE $whereSql ORDER BY name";
} else {
    $sql = "SELECT * FROM students ORDER BY name";
}

$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Error executing query: ' . mysqli_error($conn));
}

$num = 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC Checkup System - Haramaya University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .header {
            background-color: #003366;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .cl1 {
            font-size: 0.7rem;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #003366;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            z-index: 1000;
            overflow-y: auto;
        }
        .overlay-content {
            background-color:rgba(204, 207, 208, 0.8);
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
        }
        .detail-row {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            font-weight: bold;
            color: #003366;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
<header class="header text-center">
        <h1>PC Checkup System</h1>
        <p class="lead mt-2">Haramaya University - <?php echo $current_year; ?></p>
    </header>

    <div class="container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" >
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-message">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between mb-4 no-print">
            <a href="home.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Go Back
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>

        <div class="form-container">
            <button id="toggleFormBtn" class="btn btn-primary mb-4 no-print" data-bs-toggle="collapse" data-bs-target="#studentForm">
                <i class="bi bi-person-plus"></i> Add New Record
            </button>

            <div id="studentForm" class="collapse">
                <form method="POST" class="row g-3 needs-validation" novalidate enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" id="edit_id" value="0">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                        <div class="invalid-feedback">Please enter student's name.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="sex" id="sex" required>
                            <option value="" selected disabled>Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                        <div class="invalid-feedback">Please select gender.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ID Number</label>
                        <input type="text" class="form-control" name="idNumber" id="idNumber" required>
                        <div class="invalid-feedback">Please enter ID number.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="department">Department</label>
                        <select class="form-select" name="department" id="department" required>
                            <option value="">Select a Department</option>
                        </select>
                        <div class="invalid-feedback">Please select a department.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Campus</label>
                        <select class="form-select" name="campus" id="campus" required>
                            <option value="" selected disabled>Select Campus</option>
                            <option value="Main">Main</option>
                            <option value="HiT">HiT</option>
                            <option value="Station">Station</option>
                            <option value="Harar">Harar</option>
                        </select>
                        <div class="invalid-feedback">Please choose campus.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">PC Serial Number</label>
                        <input type="text" class="form-control" name="pcSerialNumber" id="pcSerialNumber" required>
                        <div class="invalid-feedback">Please enter PC serial number.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">PC Model</label>
                        <input type="text" class="form-control" name="pcModel" id="pcModel">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" name="contact" id="contact">
                    </div>
                    <div class="col-md-6">
                        <label for="year" class="form-label">Year </label>
                        <select class="form-select" id="yaer" name="year" required>
                            <option value="" selected disabled>Select Year</option>
                            <option value="1">First Year</option>
                            <option value="2">Second Year</option>
                            <option value="3">Third Year</option>
                            <option value="4">Fourth Year</option>
                            <option value="5">Fifth Year</option>
                            <option value="6">Six Year</option>
                            <option value="7">Seven Year</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Photo (Optional)</label>
                        <input type="file" class="form-control" name="photo" id="photo" accept="image/jpeg, image/png">
                        <div class="form-text">Only JPG/PNG images accepted</div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success" name="submit">
                            <i class="bi bi-save"></i> Save Record
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeForm()">
                            <i class="bi bi-x"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="search-box no-print my-3">
            <form method="POST" class="input-group">
                <input autocomplete="off" type="search" class="form-control" placeholder="Search by name, ID or department..." 
                    name="search_query" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="btn btn-primary" name="search">
                    <i class="bi bi-search"></i> Search
                </button>
            </form>
        </div>

        <div class="table-responsive cl1">
            <table class="table table-hover zoom-table">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Gender</th>
                        <th>Batch</th>
                        <th>ID Number</th>
                        <th>Department</th>
                        <th>Campus</th>
                        <th>PC Serial</th>
                        <th class="no-print">PC Model</th>
                        <th class="no-print text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result -> num_rows > 0): 
                         while ($row = $result->fetch_assoc()):
                            $num++;
                            $id = $row['id'];
                            $name = $row['name'];
                            $sex = $row['sex'];
                            $year = $row['year'];
                            $idNumber = $row['idNumber'];
                            $department = $row['department'];
                            $campus = $row['campus'];
                            $pcSerialNumber = $row['pcSerialNumber'];
                            $pcModel = $row['pcModel'];
                            ?>
                            <tr>
                                <td><?= $num; ?></td>
                                <td><?= htmlspecialchars($name); ?></td>
                                <td><?= htmlspecialchars($sex); ?></td>
                                <td><?= htmlspecialchars($year); ?></td>
                                <td style="color:rgb(10, 127, 35); font-weight: bolder;"><?= htmlspecialchars($idNumber); ?></td>
                                <td><?= htmlspecialchars($department); ?></td>
                                <td><?= htmlspecialchars($campus); ?></td>
                                <td style="color: blue; font-weight: bolder;"><?= htmlspecialchars($pcSerialNumber); ?></td>
                                <td style="color: red;" class="psm no-print"><?= htmlspecialchars($pcModel); ?></td>
                                <td class="no-print text-center">
                                    <button class="btn btn-sm btn-primary" id="off">
                                        <a href="update.php?updateid=<?= $id; ?>" class="text-light">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </button>

                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $id; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <button class="btn btn-sm btn-info" onclick="viewProfile(<?= $id; ?>)">
                                        <i class="bi bi-info-circle"></i> 
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No PC checkup records found for <?php echo $current_year; ?>.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this record? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile View Overlay -->
    <div id="profileOverlay" class="overlay">
        <div class="overlay-content">
        <button style="margin-left: 86%;" class="btn btn-danger mt-1" onclick="closeProfile()">Close</button>
            <div id="profileContent"></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="main.js"></script>
</body>
</html>