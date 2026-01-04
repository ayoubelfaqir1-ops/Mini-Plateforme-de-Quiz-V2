<?php
require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Category.php';
require_once '../../classes/Quiz.php';
require_once '../../classes/Result.php';



// Vérifier que l'utilisateur est étudiant
Security::requireStudent();

$categoryObj = new Category();
if (!isset($_GET) || !isset($_GET['id']) || !$categoryObj->isExist($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

// Récupérer les données de l'utilisateur
$categoryID = $_GET['id'];
$studentID = $_SESSION['user_id'];
$studentName = $_SESSION['user_nom'];

// Récupérer les catégories

$quizObj = new Quiz();
$quizzes = $quizObj->getAllByCategory($categoryID);


?>
<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php require_once __DIR__ . '/../partials/nav_student.php'; ?>

<div id="studentSpace" class="pt-16 min-h-screen w-full overflow-x-hidden">
    <!-- Category Quizzes List -->
    <div id="categoryQuizzes" class="student-section ">
        <div class="bg-gradient-to-r from-green-600 to-teal-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <a href="dashboard.php" onclick="showStudentSection('studentDashboard')" class="text-white hover:text-green-100 mb-4">
                    <i class="fas fa-arrow-left mr-2"></i>Retour aux catégories
                </a>
                <h1 class="text-4xl font-bold mb-2" id="categoryTitle">HTML/CSS</h1>
                <p class="text-xl text-green-100">Sélectionnez un quiz pour commencer</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div id="quizListContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($quizzes)): ?>
                    <div class="col-span-full py-20 flex flex-col items-center justify-center text-center">
                        <div class="bg-gray-100 p-6 rounded-full mb-6">
                            <i class="fas fa-book-open text-gray-400 text-5xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Aucun quiz disponible</h3>
                        <p class="text-gray-500 max-w-sm mb-8">
                            Il n'y a pas encore de quiz dans cette catégorie. Revenez plus tard ou explorez d'autres sujets.
                        </p>
                        <a href="dashboard.php"
                            class="inline-flex items-center px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-lg transition-colors">
                            <i class="fas fa-th-large mr-2"></i>
                            Voir toutes les catégories
                        </a>
                    </div>
                <?php endif; ?>
                <!-- Quiz cards will be loaded dynamically -->
                <?php foreach ($quizzes as $index => $quiz): ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                        <div class="h-2 bg-teal-600"></div>

                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="text-teal-600">
                                    <i class="fas fa-file-alt fa-lg"></i>
                                </div>
                            </div>

                            <h3 class="text-xl font-bold text-gray-800 mb-2 truncate"><?= $quiz['titre'] ?></h3>
                            <p class="text-gray-600 text-sm mb-6 line-clamp-2"><?= $quiz['description'] ?></p>

                            <div class="flex items-center justify-between border-t border-b border-gray-50 py-4 mb-6">
                                <div class="flex items-center text-gray-500">
                                    <i class="far fa-question-circle mr-2 text-teal-500"></i>
                                    <span class="text-sm font-medium"><?= $quiz['questions_count'] . " question" ?>s</span>
                                </div>
                                <div class="flex items-center text-gray-500">
                                    <i class="far fa-clock mr-2 text-teal-500"></i>
                                    <span class="text-sm font-medium"><?= $quiz['periode'] . " minutes" ?></span>
                                </div>
                            </div>

                            <a href="quizTaking.php?id=<?= $quiz['id'] ?>" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center group">
                                Commencer le Quiz
                                <i class="fas fa-play ml-2 text-sm group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php include '../partials/footer.php'; ?>