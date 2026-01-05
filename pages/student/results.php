<?php
require_once '../../config/database.php';
require_once '../../classes/Database.php';
require_once '../../classes/Security.php';
require_once '../../classes/Quiz.php';

Security::requireStudent();

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/nav_student.php';
?>
<!-- Student Results -->
        <div id="studentResults" class="student-section">
            <div class="bg-gradient-to-r from-green-600 to-teal-600 text-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <button onclick="showStudentSection('studentDashboard')" class="text-white hover:text-green-100 mb-4">
                        <i class="fas fa-arrow-left mr-2"></i>Retour au tableau de bord
                    </button>
                    <h1 class="text-4xl font-bold mb-2">Mes Résultats</h1>
                    <p class="text-xl text-green-100">Suivez votre progression et vos performances</p>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Quiz Complétés</p>
                                <p id='totalquiz' class="text-3xl font-bold text-gray-900">0</p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-check-circle text-blue-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Moyenne</p>
                                <p id="avg" class="text-3xl font-bold text-gray-900">0%</p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-lg">
                                <i class="fas fa-star text-green-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Taux Réussite</p>
                                <p id="successRate" class="text-3xl font-bold text-gray-900">0%</p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-lg">
                                <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Classement</p>
                                <p id="bestScore" class="text-3xl font-bold text-gray-900">-</p>
                            </div>
                            <div class="bg-yellow-100 p-3 rounded-lg">
                                <i class="fas fa-trophy text-yellow-600 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quiz</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Temps</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                </tr>
                            </thead>
                            <tbody id="resultsBody" class="bg-white divide-y divide-gray-200">
                                <!-- Rows will be injected here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
            let etudiantId = <?= json_encode($_SESSION['user_id']) ?>;
            let results = null;
            let stats = null;

            function student_Result_stats() {
                return fetch(`../../actions/student/result_display.php?etudiant_id=${etudiantId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log("Success");
                            results = data.results;
                            stats = data.stats;
                            return true;
                        } else {
                            console.error(data.error);
                            return false;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        return false;
                    });
            }

            function infosloading() {
                document.getElementById('totalquiz').textContent = (stats && stats['total_quiz']) ? stats['total_quiz'] : 0;
            }

            // Load stats then update UI
            student_Result_stats().then(ok => {
                if (ok) infosloading();
                console.log(etudiantId);
                console.log(results);
                console.log(stats);
                // populate results table
                if (ok && Array.isArray(results)) {
                    const tbody = document.getElementById('resultsBody');
                    tbody.innerHTML = '';
                    results.forEach(r => {
                        const date = new Date(r.created_at);
                        const dateStr = isNaN(date.getTime()) ? (r.created_at || '') : date.toLocaleDateString();
                        const scoreText = `${r.score}/${r.total_questions}`;
                        const percent = r.total_questions ? Math.round((r.score / r.total_questions) * 100) : 0;
                        const status = percent >= 50 ? 'Réussi' : 'Échoué';
                        const statusClass = percent >= 50 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-gray-50';
                        row.innerHTML = `
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">${escapeHtml(r.quiz_titre || '')}</td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">${escapeHtml(r.categorie_nom || '')}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="text-lg font-bold ${percent>=50 ? 'text-green-600' : 'text-red-600'}">${escapeHtml(scoreText)}</span></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(dateStr)}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${escapeHtml(r.finishtime || '')}</td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}"><i class="fas ${percent>=50 ? 'fa-check' : 'fa-times'} mr-1"></i>${status}</span></td>
                        `;
                        tbody.appendChild(row);
                    });
                }
            });

            // simple HTML escape
            function escapeHtml(text) {
                return String(text).replace(/[&<>"']/g, function (m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]; });
            }
        </script>