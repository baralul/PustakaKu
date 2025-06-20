<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: books.php");
    exit();
}

include_once '../config/database.php';
include_once '../classes/Book.php';

$database = new Database();
$db = $database->getConnection();
$book = new Book($db);

$book->id = $_GET['id'];
if(!$book->readOne()) {
    header("Location: books.php");
    exit();
}

$success_message = "";
$error_message = "";

if($_POST) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $publisher = trim($_POST['publisher']);
    $publication_year = trim($_POST['publication_year']);
    $category = trim($_POST['category']);
    $total_copies = trim($_POST['total_copies']);
    $available_copies = trim($_POST['available_copies']);
    $description = trim($_POST['description']);
    
    // Server-side validation
    $errors = [];
    
    if(empty($title)) {
        $errors[] = "Title is required.";
    }
    
    if(empty($author)) {
        $errors[] = "Author is required.";
    }
    
    if(!empty($isbn) && strlen($isbn) < 10) {
        $errors[] = "ISBN must be at least 10 characters.";
    }
    
    if(!empty($publication_year) && (!is_numeric($publication_year) || $publication_year < 1000 || $publication_year > date('Y'))) {
        $errors[] = "Please enter a valid publication year.";
    }
    
    if(empty($total_copies) || !is_numeric($total_copies) || $total_copies < 1) {
        $errors[] = "Total copies must be at least 1.";
    }
    
    if(empty($available_copies) || !is_numeric($available_copies) || $available_copies < 0) {
        $errors[] = "Available copies cannot be negative.";
    }
    
    if(!empty($total_copies) && !empty($available_copies) && $available_copies > $total_copies) {
        $errors[] = "Available copies cannot exceed total copies.";
    }
    
    if(empty($errors)) {
        $book->title = $title;
        $book->author = $author;
        $book->isbn = $isbn;
        $book->publisher = $publisher;
        $book->publication_year = $publication_year;
        $book->category = $category;
        $book->total_copies = $total_copies;
        $book->available_copies = $available_copies;
        $book->description = $description;
        
        if($book->update()) {
            $success_message = "Book updated successfully!";
        } else {
            $error_message = "Failed to update book. ISBN might already exist.";
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - PustakaKu Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            margin: 5px 0;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="fas fa-book text-white" style="font-size: 2rem;"></i>
                        <h4 class="text-white mt-2">PustakaKu</h4>
                        <small class="text-white-50">Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column px-3">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="books.php">
                                <i class="fas fa-book me-2"></i>Manage Books
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Manage Users
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit Book</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="books.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Books
                        </a>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Edit Book Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Book Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="editBookForm" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="title" class="form-label">Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" required
                                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : htmlspecialchars($book->title); ?>">
                                    <div class="invalid-feedback">
                                        Please provide a book title.
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="author" class="form-label">Author *</label>
                                    <input type="text" class="form-control" id="author" name="author" required
                                           value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : htmlspecialchars($book->author); ?>">
                                    <div class="invalid-feedback">
                                        Please provide the author name.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="isbn" class="form-label">ISBN</label>
                                    <input type="text" class="form-control" id="isbn" name="isbn" minlength="10"
                                           value="<?php echo isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : htmlspecialchars($book->isbn); ?>">
                                    <div class="invalid-feedback">
                                        ISBN must be at least 10 characters.
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="publisher" class="form-label">Publisher</label>
                                    <input type="text" class="form-control" id="publisher" name="publisher"
                                           value="<?php echo isset($_POST['publisher']) ? htmlspecialchars($_POST['publisher']) : htmlspecialchars($book->publisher); ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="publication_year" class="form-label">Publication Year</label>
                                    <input type="number" class="form-control" id="publication_year" name="publication_year" 
                                           min="1000" max="<?php echo date('Y'); ?>"
                                           value="<?php echo isset($_POST['publication_year']) ? htmlspecialchars($_POST['publication_year']) : htmlspecialchars($book->publication_year); ?>">
                                    <div class="invalid-feedback">
                                        Please enter a valid year.
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">Select Category</option>
                                        <?php
                                        $categories = ['Fiction', 'Non-Fiction', 'Science', 'Technology', 'History', 'Biography', 'Romance', 'Mystery', 'Educational'];
                                        $selected_category = isset($_POST['category']) ? $_POST['category'] : $book->category;
                                        foreach($categories as $cat) {
                                            $selected = ($selected_category == $cat) ? 'selected' : '';
                                            echo "<option value='$cat' $selected>$cat</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="total_copies" class="form-label">Total Copies *</label>
                                    <input type="number" class="form-control" id="total_copies" name="total_copies" 
                                           min="1" required
                                           value="<?php echo isset($_POST['total_copies']) ? htmlspecialchars($_POST['total_copies']) : htmlspecialchars($book->total_copies); ?>">
                                    <div class="invalid-feedback">
                                        Total copies must be at least 1.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="available_copies" class="form-label">Available Copies *</label>
                                    <input type="number" class="form-control" id="available_copies" name="available_copies" 
                                           min="0" required
                                           value="<?php echo isset($_POST['available_copies']) ? htmlspecialchars($_POST['available_copies']) : htmlspecialchars($book->available_copies); ?>">
                                    <div class="invalid-feedback">
                                        Available copies cannot be negative.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : htmlspecialchars($book->description); ?></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="books.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Book
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-sync available copies with total copies
        document.getElementById('total_copies').addEventListener('input', function() {
            const totalCopies = parseInt(this.value) || 0;
            const availableCopies = document.getElementById('available_copies');
            const currentAvailable = parseInt(availableCopies.value) || 0;
            
            if (currentAvailable > totalCopies) {
                availableCopies.value = totalCopies;
            }
            availableCopies.max = totalCopies;
        });

        // Validate available copies
        document.getElementById('available_copies').addEventListener('input', function() {
            const totalCopies = parseInt(document.getElementById('total_copies').value) || 0;
            const availableCopies = parseInt(this.value) || 0;
            
            if (availableCopies > totalCopies) {
                this.setCustomValidity('Available copies cannot exceed total copies');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
