<?php
require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Category.php';
require_once '../../classes/Quiz.php';
require_once '../../classes/Result.php';


// Vérifier que l'utilisateur est étudiant
Security::requireStudent();


$studentID = $_SESSION['user_id'];
$studentName = $_SESSION['user_nom'];

// Récupérer les catégories
$categoryObj = new Category();
$categories = $categoryObj->getAll();

$quizObj = new Quiz();
$quizzes = $quizObj->getAllByTeacher($studentID);

$totalQuizzes = count($quizzes);
$totalCategories = count($categories);

$userName = $_SESSION['user_nom'] ?? 'Étudiant';
?>
<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php require_once __DIR__ . '/../partials/nav_student.php'; ?>

<div id="studentSpace" class="pt-16 min-h-screen w-full overflow-x-hidden">
    <!-- Student Dashboard -->
    <div id="studentDashboard" class="student-section">
        <div class="bg-gradient-to-r from-green-600 to-teal-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <h1 class="text-4xl font-bold mb-4">Espace Étudiant</h1>
                <p class="text-xl text-green-100 mb-6">Passez des quiz et suivez votre progression</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Catégories Disponibles</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $colors = ['blue', 'purple', 'green', 'red', 'yellow', 'pink', 'indigo', 'teal'];
                foreach ($categories as $index => $category):
                    $color = $colors[$index % count($colors)];
                    $show = $quizObj->getTotalByCategory($category["id"]);
                ?>
                    <div onclick="showStudentSection('categoryQuizzes')" class="bg-<?= $color ?> rounded-xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden group cursor-pointer">
                        <form action="" method="post">
                            <div class="bg-gradient-to-br from-<?= $color ?>-600 to-<?= $color ?>-500 p-6 text-white">
                                <i class="fas fa-code text-4xl mb-3"></i>
                                <h3 class="text-xl font-bold"><?= $category['nom'] ?></h3>
                            </div>
                            <div class="p-6">
                                <p class="text-gray-600 mb-4"> desc: <?= $category["description"] ?></p>
                                <div class="flex justify-between items-center text-
                                sm">
                                    <span class="text-gray-500"><i class="fas fa-clipboard-list mr-2"></i><?= $quizObj->getTotalByCategory($category["id"])['total_quiz']; ?> quiz</span>
                                        <a href="categoryQuiz.php?id=<?= $category["id"] ?>" name="Explorer" class="text-green-600 font-semibold group-hover:translate-x-2 transition-transform">Explorer →</a>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
                <div onclick="showStudentSection('studentResults')" class="bg-white rounded-xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden group cursor-pointer">
                    <div class="bg-gradient-to-br from-orange-500 to-orange-600 p-6 text-white">
                        <i class="fas fa-chart-line text-4xl mb-3"></i>
                        <h3 class="text-xl font-bold">Mes Résultats</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-4">Consultez vos performances</p>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500"><i class="fas fa-trophy mr-2"></i>24 tentatives</span>
                            <span class="text-orange-600 font-semibold group-hover:translate-x-2 transition-transform">Voir →</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>