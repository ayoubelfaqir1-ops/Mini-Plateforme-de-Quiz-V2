<?php
require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Quiz.php';

Security::requireStudent();

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$quizId = $_GET['id'];
$quizObj = new Quiz();
$quizDetails = $quizObj->getById($quizId); // Assuming this method exists

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/nav_student.php';
?>

<div id="studentSpace" class="pt-16 min-h-screen w-full overflow-x-hidden">
    <!-- Quiz Taking Interface -->
    <div id="takeQuiz" class="student-section">
        <div class="bg-gradient-to-r from-green-600 to-teal-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold mb-2" id="quizTitle"><?= htmlspecialchars($quizDetails['titre'] ?? 'Quiz') ?></h1>
                        <p class="text-green-100">Question <span id="currentQuestion">1</span> sur <span id="totalQuestions">-</span></p>
                    </div>

                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6" id="questionText">
                    Chargement du quiz...
                </h3>

                <div id="answersContainer" class="space-y-4">
                    <!-- Answers will be loaded here -->
                </div>

                <div class="flex justify-between mt-8">
                    <button onclick="previousQuestion()" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Précédent
                    </button>
                    <button onclick="nextQuestion()" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Suivant<i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="max-w-md mx-auto bg-white shadow-lg rounded-xl p-6 text-center hidden">
        <h1 class="text-2xl font-bold text-gray-800">Résultat du Quiz</h1>

        <div class="relative flex items-center justify-center my-6">
            <svg class="w-32 h-32">
                <circle cx="64" cy="64" r="58" stroke="lightgray" stroke-width="8" fill="transparent" />
                <circle cx="64" cy="64" r="58" stroke="green" stroke-width="8" fill="transparent" stroke-dasharray="364" stroke-dashoffset="72" />
            </svg>
            <span class="absolute text-2xl font-bold">8/10</span>
        </div>

        <p class="text-gray-600 mb-6">Excellent travail ! Vous avez maîtrisé la plupart des concepts.</p>

        <div class="flex flex-col gap-3">
            <button class="bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">Revoir les réponses</button>
            <button class="border border-gray-300 py-3 rounded-lg font-semibold hover:bg-gray-50">Recommencer le quiz</button>
        </div>
    </div>
</div>

<script>
    const quizId = <?= json_encode($quizId) ?>;
    let questions = [];
    let currentQuestionIndex = 0;
    let userAnswers = {};

    document.addEventListener('DOMContentLoaded', function() {
        fetchQuestions();
    });

    function fetchQuestions() {
        fetch(`../../actions/student/quiz_questions.php?quiz_id=${quizId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    questions = data.questions;
                    document.getElementById('totalQuestions').textContent = questions.length;
                    console.log(questions);
                    renderQuestion(0);
                }
            })
            .catch(error => console.error(error));
    }

    function renderQuestion(index) {
        if (index < 0 || index >= questions.length) return;

        currentQuestionIndex = index;
        const question = questions[index];

        // Update UI
        document.getElementById('currentQuestion').textContent = index + 1;
        document.getElementById('questionText').textContent = question.text;

        const answersContainer = document.getElementById('answersContainer');
        answersContainer.innerHTML = '';
        

        question.answers.forEach(answer => {
            const isSelected = userAnswers[question.id] === answer.id;
            const selectedClass = isSelected ? 'border-green-500 bg-green-50' : 'border-gray-200';
            const radioSelectedClass = isSelected ? 'block' : 'hidden';

            const div = document.createElement('div');
            div.className = `answer-option p-4 border-2 ${selectedClass} rounded-lg cursor-pointer hover:border-green-500 hover:bg-green-50 transition`;
            div.onclick = () => selectAnswer(div, question.id, answer.id);

            div.innerHTML = `
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full border-2 border-gray-300 flex items-center justify-center mr-4 option-radio">
                        <div class="w-4 h-4 rounded-full bg-green-600 ${radioSelectedClass} option-selected"></div>
                    </div>
                    <span class="text-lg">${answer.text}</span>
                </div>
            `;
            answersContainer.appendChild(div);
        });
    }

    function selectAnswer(element, questionId, answerId) {
        // Update data
        userAnswers[questionId] = answerId;

        // Update UI visually
        const options = document.querySelectorAll('.answer-option');
        options.forEach(opt => {
            opt.classList.remove('border-green-500', 'bg-green-50');
            opt.classList.add('border-gray-200');
            opt.querySelector('.option-selected').classList.add('hidden');
        });

        element.classList.remove('border-gray-200');
        element.classList.add('border-green-500', 'bg-green-50');
        element.querySelector('.option-selected').classList.remove('hidden');
    }

    function nextQuestion() {
        if (currentQuestionIndex < questions.length - 1) {
            renderQuestion(currentQuestionIndex + 1);
        } else {
            // Submit Quiz Logic here
            alert("Quiz terminé ! (Logique de soumission à implémenter)");
        }
    }

    function previousQuestion() {
        if (currentQuestionIndex > 0) {
            renderQuestion(currentQuestionIndex - 1);
        }
    }

    // fucntion result() {

    // }
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>