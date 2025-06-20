<?php
session_start();

// Check if user is logged in and is admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

include_once '../config/database.php';
include_once '../classes/Book.php';

$database = new Database();
$db = $database->getConnection();
$book = new Book($db);

// Handle delete action
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $book->id = $_GET['id'];
    if($book->delete()) {
        $success_message = "Book deleted successfully!";
    } else {
        $error_message = "Failed to delete book.";
    }
}

// Handle search
$search_keyword = '';
$books_stmt = null;

if(isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_keyword = trim($_GET['search']);
    $books_stmt = $book->search($search_keyword);
} else {
    $books_stmt = $book->readAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - PustakaKu Admin</title>
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
        .table-actions .btn {
            margin: 0 2px;
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
                    <h1 class="h2">Manage Books</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="add_book.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add New Book
                        </a>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if(isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Search Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-10">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Search books by title or author..." 
                                           value="<?php echo htmlspecialchars($search_keyword); ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                            </div>
                        </form>
                        <?php if($search_keyword): ?>
                            <div class="mt-2">
                                <small class="text-muted">Search results for: <strong>"<?php echo htmlspecialchars($search_keyword); ?>"</strong></small>
                                <a href="books.php" class="btn btn-sm btn-outline-secondary ms-2">Clear</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Books Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Books List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>ISBN</th>
                                        <th>Category</th>
                                        <th>Copies</th>
                                        <th>Available</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $book_count = 0;
                                    while($row = $books_stmt->fetch(PDO::FETCH_ASSOC)):
                                        extract($row);
                                        $book_count++;
                                    ?>
                                    <tr>
                                        <td><?php echo $id; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($title); ?></strong>
                                            <?php if($publisher): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($publisher); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($author); ?></td>
                                        <td><?php echo htmlspecialchars($isbn); ?></td>
                                        <td>
                                            <?php if($category): ?>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($category); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $total_copies; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $available_copies > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo $available_copies; ?>
                                            </span>
                                        </td>
                                        <td class="table-actions">
                                            <a href="edit_book.php?id=<?php echo $id; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $id; ?>, '<?php echo htmlspecialchars($title); ?>')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            
                            <?php if($book_count == 0): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No books found</h5>
                                    <?php if($search_keyword): ?>
                                        <p class="text-muted">Try searching with different keywords</p>
                                    <?php else: ?>
                                        <p class="text-muted">Start by adding your first book</p>
                                        <a href="add_book.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Add Book
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this book?</p>
                    <p><strong id="bookTitle"></strong></p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(bookId, bookTitle) {
            document.getElementById('bookTitle').textContent = bookTitle;
            document.getElementById('confirmDeleteBtn').href = 'books.php?action=delete&id=' + bookId;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>
</body>
</html>
