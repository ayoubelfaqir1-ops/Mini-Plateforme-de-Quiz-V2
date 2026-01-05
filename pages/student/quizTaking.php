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
                    <div>
                        <h1 class="text-3xl font-bold mb-2">Temps restant</h1>
                        <h1 id="timer">00:00</h2>
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
    
    <!-- Result Section -->
    <div id="resultSection" class="max-w-md mx-auto bg-white shadow-lg rounded-xl p-6 text-center hidden mt-10">
        <div class="mb-6">
            <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-flag-checkered text-4xl text-teal-600" id="resultIcon"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2" id="resultTitle">Quiz Terminé !</h1>
            <p class="text-gray-600" id="resultMessage">Voici votre score final</p>
        </div>

        <div class="relative flex items-center justify-center my-8">
            <svg class="w-32 h-32 transform -rotate-90">
                <circle cx="64" cy="64" r="58" stroke="#e5e7eb" stroke-width="8" fill="transparent" />
                <circle cx="64" cy="64" r="58" stroke="#0d9488" stroke-width="8" fill="transparent" stroke-dasharray="364" stroke-dashoffset="364" id="scoreCircle" class="transition-all duration-1000 ease-out" />
            </svg>
            <div class="absolute text-center">
                <span class="block text-3xl font-bold text-gray-800" id="scoreValue">0%</span>
                <span class="text-sm text-gray-500" id="scoreText">0/0</span>
            </div>
        </div>

        <div class="space-y-3">
            <a href="quizzes.php" class="block w-full bg-teal-600 text-white py-3 px-4 rounded-lg font-bold hover:bg-teal-700 transition-colors">
                <i class="fas fa-th-large mr-2"></i>Retour aux Quiz
            </a>
            <button onclick="window.location.reload()" class="block w-full border-2 border-gray-200 text-gray-700 py-3 px-4 rounded-lg font-bold hover:border-teal-600 hover:text-teal-600 transition-colors">
                <i class="fas fa-redo mr-2"></i>Recommencer
            </button>
        </div>
    </div>
</div>

<script>
    const quizId = <?= json_encode($quizId) ?>;
    let questions = [];
    let currentQuestionIndex = 0;
    let userAnswers = {};
    let score = 0;
    let finishtimeMINUTES = 0;
    let finishtimeSECONDS = 0;
    let finishtime = `${finishtimeMINUTES}:${finishtimeSECONDS}`;
    let time = <?= json_encode($quizDetails['periode']) ?> * 60;
    
const timer = setInterval(() => {
    let minutes = Math.floor(time / 60);
    let seconds = time % 60;

    seconds = seconds < 10 ? "0" + seconds : seconds;

    document.getElementById("timer").textContent =
        `${minutes}:${seconds}`;

    time--;
    finishtimeSECONDS++;
    if (finishtimeSECONDS >= 60) {
        finishtimeMINUTES++;
        finishtimeSECONDS = 0;
    }
    if (time < 0) {
        clearInterval(timer);
        document.getElementById("timer").textContent = "00:00";
        result(true); // Time is up
    }
}, 1000);

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
            div.onclick = () => selectAnswer(div, question.id, answer.id, question.reponse);

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

    function selectAnswer(element, questionId, answerId, reponse) {
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
            clearInterval(timer);
            finishtime = document.getElementById("timer").textContent;
            document.getElementById("timer").textContent = "00:00";
            result(false);
            submitResult();
        }
    }

    function previousQuestion() {
        if (currentQuestionIndex > 0) {
            renderQuestion(currentQuestionIndex - 1);
        }
    }

    function result(isTimeUp) {
        // Calculate Score
        let correctCount = 0;
        questions.forEach(q => {
            if (userAnswers[q.id] == q.reponse) {
                correctCount++;
            }
        });
        score = correctCount;

        // Update UI Text
        if (isTimeUp) {
            document.getElementById('resultTitle').textContent = "Temps Écoulé !";
            document.getElementById('resultMessage').textContent = "Le temps imparti est terminé.";
            document.getElementById('resultIcon').className = "fas fa-hourglass-end text-4xl text-orange-500";
        } else {
            document.getElementById('resultTitle').textContent = "Quiz Terminé !";
            document.getElementById('resultMessage').textContent = "Bravo, vous avez complété le quiz.";
            document.getElementById('resultIcon').className = "fas fa-check-circle text-4xl text-teal-600";
        }

        // Update Score Display
        document.getElementById('scoreText').textContent = `${correctCount}/${questions.length}`;
        const percentage = Math.round((correctCount / questions.length) * 100);
        document.getElementById('scoreValue').textContent = `${percentage}%`;

        // Animate Circle
        const circle = document.getElementById('scoreCircle');
        const circumference = 364;
        const offset = circumference - (percentage / 100) * circumference;
        circle.style.strokeDashoffset = offset;

        // Show Section
        document.getElementById('resultSection').classList.remove('hidden');
        document.getElementById('takeQuiz').classList.add('hidden');
    }

    function submitResult() {
        fetch('../../actions/student/result_submission.php',{
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({'quiz_id':quizId, 'score':score, 'total_questions':questions.length, 'finishtime':finishtime})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Saved");
        }else {
            console.error(data.error);
        }
        })
    }
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>