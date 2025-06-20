<?php
session_start();

// Check if user is logged in and is member
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: ../index.php");
    exit();
}

include_once '../config/database.php';
include_once '../classes/Book.php';

$database = new Database();
$db = $database->getConnection();
$book = new Book($db);

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
    <title>Member Dashboard - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
            color: #667eea !important;
        }
        .search-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
        }
        .book-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 1rem;
        }
        .book-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        .availability-badge {
            font-size: 0.8rem;
        }
        .book-title {
            color: #333;
            font-weight: 600;
        }
        .book-author {
            color: #666;
            font-style: italic;
        }
        .book-details {
            font-size: 0.9rem;
            color: #777;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-book me-2"></i>PustakaKu
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Section with Search -->
        <div class="search-container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-2">
                        <i class="fas fa-book-open me-2"></i>Library Collection
                    </h2>
                    <p class="mb-0">Discover and explore our extensive book collection</p>
                </div>
                <div class="col-md-6">
                    <form method="GET" class="d-flex">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   name="search" 
                                   placeholder="Search by title or author..." 
                                   value="<?php echo htmlspecialchars($search_keyword); ?>">
                            <button class="btn btn-light" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Search Results Info -->
        <?php if($search_keyword): ?>
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            Search results for: <strong>"<?php echo htmlspecialchars($search_keyword); ?>"</strong>
            <a href="dashboard.php" class="btn btn-sm btn-outline-info ms-2">
                <i class="fas fa-times me-1"></i>Clear Search
            </a>
        </div>
        <?php endif; ?>

        <!-- Books Grid -->
        <div class="row">
            <?php
            $book_count = 0;
            while($row = $books_stmt->fetch(PDO::FETCH_ASSOC)):
                extract($row);
                $book_count++;
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card book-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title book-title mb-1"><?php echo htmlspecialchars($title); ?></h5>
                            <span class="badge availability-badge <?php echo $available_copies > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $available_copies > 0 ? 'Available' : 'Not Available'; ?>
                            </span>
                        </div>
                        
                        <p class="book-author mb-2">by <?php echo htmlspecialchars($author); ?></p>
                        
                        <div class="book-details mb-3">
                            <?php if($isbn): ?>
                            <div><strong>ISBN:</strong> <?php echo htmlspecialchars($isbn); ?></div>
                            <?php endif; ?>
                            
                            <?php if($publisher): ?>
                            <div><strong>Publisher:</strong> <?php echo htmlspecialchars($publisher); ?></div>
                            <?php endif; ?>
                            
                            <?php if($publication_year): ?>
                            <div><strong>Year:</strong> <?php echo htmlspecialchars($publication_year); ?></div>
                            <?php endif; ?>
                            
                            <?php if($category): ?>
                            <div><strong>Category:</strong> 
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($category); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if($description): ?>
                        <p class="card-text text-muted small">
                            <?php echo htmlspecialchars(substr($description, 0, 100)) . (strlen($description) > 100 ? '...' : ''); ?>
                        </p>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <small class="text-muted">
                                <i class="fas fa-copy me-1"></i>
                                <?php echo $available_copies; ?>/<?php echo $total_copies; ?> copies available
                            </small>
                            
                            <?php if($available_copies > 0): ?>
                            <button class="btn btn-primary btn-sm" onclick="showBookDetails(<?php echo $id; ?>)">
                                <i class="fas fa-eye me-1"></i>View Details
                            </button>
                            <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled>
                                <i class="fas fa-ban me-1"></i>Unavailable
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- No Results Message -->
        <?php if($book_count == 0): ?>
        <div class="text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No books found</h4>
            <?php if($search_keyword): ?>
            <p class="text-muted">Try searching with different keywords or <a href="dashboard.php">browse all books</a></p>
            <?php else: ?>
            <p class="text-muted">The library collection is currently empty.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Book Details Modal -->
    <div class="modal fade" id="bookDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="bookDetailsContent">
                    <!-- Book details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white mt-5 py-4 border-top">
        <div class="container text-center">
            <p class="text-muted mb-0">
                <i class="fas fa-book me-2"></i>PustakaKu Library Management System
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to show book details in modal
        function showBookDetails(bookId) {
            // For now, just show a placeholder
            // In a full implementation, this would fetch book details via AJAX
            document.getElementById('bookDetailsContent').innerHTML = `
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-3"></i>
                    <p>Loading book details...</p>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('bookDetailsModal'));
            modal.show();
            
            // Simulate loading (in real implementation, use fetch/AJAX)
            setTimeout(() => {
                document.getElementById('bookDetailsContent').innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Book details feature will be implemented in the next phase. 
                        Book ID: ${bookId}
                    </div>
                `;
            }, 1000);
        }

        // Auto-focus search input when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });

        // Add keyboard shortcut for search (Ctrl+K or Cmd+K)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.querySelector('input[name="search"]').focus();
            }
        });
    </script>
</body>
</html>
