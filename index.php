<?php
session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
include 'db.php';

function getUserId($username) {
    global $con;
    $stmt = $con->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user ? $user['id'] : null;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$userId = $username !== 'Guest' ? getUserId($username) : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Typing Practice Tutor</title>
    <style>
        :root {
            --primary-color: #4a4e69;
            --secondary-color: #9a8c98;
            --accent-color: #a6ccff;
            --background-color: #87bbff;
            --text-color: #22223b;
        }

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background-image: url(bg1.jpeg);
            background-size: 1366px 768px;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center;
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        header {
            width: 100%;
            background-color: transparent;
            backdrop-filter: blur(2px);
            color: #ffffff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            font-size: 1.8rem;
            font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            color: #ffffff;
            margin-left: 30px;
            margin-bottom: 1px;
            margin-top: 1px;
            padding: 10px 0;
            font-weight: bold;
        }

        .hamburger-menu {
            display: none;
            flex-direction: column;
            cursor: pointer;
            background-color: #000000;
            padding: 5px;
        }

        .bar {
            width: 25px;
            height: 3px;
            background-color: #ffffff;
            margin: 3px 0;
            transition: 0.4s;
        }

        header nav {
            display: flex;
            align-items: center;
        }

        .username {
            font-size: 0.9rem;
            color: #ffffff;
            margin-right: 15px;
            padding: 5px 10px;
            border-radius: 5px;
        }

        #logout-btn {
            background-color: #a6ccff;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            font-size: 0.9rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-right: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #logout-btn:hover {
            background-color: #22223b;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        #logout-btn:active {
            transform: translateY(1px);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }       

        @media screen and (max-width: 768px) {
            header {
                flex-wrap: wrap;
            }

            header h1 {
                font-size: 1.5rem;
                margin-left: 10px;
            }

            .hamburger-menu {
                display: flex;
                margin-right: 10px;
                border-radius: 5px
            }

            header nav {
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
                padding: 10px;
                background-color: #ffffff;
            }

            header nav.show {
                display: flex;
            }

            nav .welcome{
                color: black;
                margin-bottom: 0;
            }

             header nav #username-display {
               margin-bottom: 30px;
                margin-left: 145px;
                color: #000000;
            }

            header nav button#logout-btn {
                margin-left: 38px;
                width: 80%;
            }
        }

        .hamburger-menu.active .bar:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }

        .hamburger-menu.active .bar:nth-child(2) {
            opacity: 0;
        }

        .hamburger-menu.active .bar:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        .main-container {
            width: 100%;
            max-width: 600px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            box-sizing: border-box;
            flex-direction: column;
        }

        .container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            width: 100%;
            max-width: 500px;
            padding: 10px;
            background-color: transparent;
            backdrop-filter: blur(8px);
            border-radius: 10px;
            /* box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); */
        }

        .card {
            padding: 10px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        h1 {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            text-align: center;
        }

        #text-container {
            background-color: var(--accent-color);
            padding: 10px;
            border-radius: 10px;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        #text-to-type {
            font-size: 1rem;
            color: var(--text-color);
            line-height: 1.4;
        }

        .correct { color: #2ff335; }
        .incorrect { color: #ff1100; }
        .space-error {
            color: #ff1100;
            text-decoration: underline wavy #f44336;
        }

        #typing-area {
            width: 100%;
            max-width: 500px;
            height: 60px;
            border: 2px solid var(--secondary-color);
            border-radius: 10px;
            padding: 5px;
            font-size: 0.9rem;
            background-color: #ffffff;
            color: var(--text-color);
            resize: none;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        #typing-area:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .stats {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin: 10px 0;
            font-size: 0.9rem;
        }

        .stats p {
            margin: 5px 10px;
        }

        .history-container {
    width: 100%;
    max-width: 600px;
    margin-top: 20px;
    background-color: transparent;
    backdrop-filter: blur(8px);
    border-radius: 10px;
    /* box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); */
    padding: 20px;
    box-sizing: border-box;
}

.history-container h2 {
    font-size: 1.2rem;
    color: var(--primary-color);
    margin-bottom: 10px;
}

#history-list {
    list-style-type: none;
    padding: 0;
}

#history-list li {
    background-color:#ffffff;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    font-size: 0.9rem;
}

        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
        }

        .popup-content {
            text-align: center;
        }

        .timer-card {
            background-color: var(--primary-color);
            color: #ffffff;
            text-align: center;
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .timer-display {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .timer-controls {
            display: flex;
            align-items: center;
            margin-top: 5px;
        }

        #timer-input {
            width: 60px;
            padding: 5px;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            text-align: center;
        }

        .restart-btn {
            background-color: var(--accent-color);
            color: var(--primary-color);
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 15px;
        }

        .restart-btn:hover {
            background-color: var(--primary-color);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .restart-btn:active {
            transform: translateY(1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #reset-btn {
            margin-left: 10px;
            height: auto;
            font-size: 0.9rem;
            text-align: center;
            align-items: center;
            margin-top: 0;
            background-color: #87bbff;
            color: #ffffff;
        }

        #reset-btn:hover {
            background-color: #22223b;
        }

        .backspace-toggle {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .backspace-toggle label {
            margin-right: 5px;
        }

        .backspace-toggle input[type="checkbox"] {
            transform: scale(1.2);
            margin-right: 5px;
        }

        .backspace-toggle input[type="checkbox"]:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
            }

            h1 {
                font-size: 1.5rem;
            }

            #text-to-type {
                font-size: 0.8rem;
            }

            #typing-area {
                height: 50px;
            }

            .timer-display {
                font-size: 1.5rem;
            }

            .restart-btn {
                padding: 8px 16px;
                font-size: 0.8rem;
            }
        }

        @media screen and (max-width: 480px) {
            .container {
                padding: 5px;
            }

            h1 {
                font-size: 1.2rem;
            }

            #text-to-type {
                font-size: 0.7rem;
            }

            #typing-area {
                height: 40px;
                font-size: 0.7rem;
            }

            .timer-display {
                font-size: 1.2rem;
            }

            .restart-btn {
                padding: 6px 12px;
                font-size: 0.7rem;
            }

            .stats {
                font-size: 0.7rem;
            }

            .history h2 {
                font-size: 1rem;
            }

            .history li {
                font-size: 0.7rem;
            }

            .timer-controls {
                flex-direction: column;
            }

            #reset-btn {
                margin-left: 0;
                margin-top: 10px;
            }

            .popup {
                width: 90%;
                padding: 15px;
            }

            .popup-content h2 {
                font-size: 1.2rem;
            }

            .popup-content p {
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>TYPING PRACTICE TUTOR</h1>
        <div class="hamburger-menu">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
        <nav>
        <span class="username">Welcome, <?php echo htmlspecialchars($username); ?></span>
            <button id="logout-btn">Logout</button>
        </nav>
    </header>
    <div class="main-container">
        <div class="container">
            <div class="card timer-card">
                <div class="timer-display">
                    <span id="minutes">00</span>:<span id="seconds">00</span>
                </div>
                <div class="timer-controls">
                    <input type="number" id="timer-input" min="1" max="3600" value="60">
                    <button id="reset-btn" class="restart-btn">Reset</button>
                </div>
            </div>
            <div class="card" id="text-container">
                <p id="text-to-type"></p>
            </div>
            <div class="card backspace-toggle">
                <label for="backspace-toggle">Allow Backspace:</label>
                <input type="checkbox" id="backspace-toggle" checked>
            </div>
            <textarea id="typing-area" placeholder="Start typing here..."></textarea>
            <div class="card stats">
                <p>Errors: <span id="errors">0</span></p>
                <p>WPM: <span id="wpm">0</span></p>
                <p>CPM: <span id="cpm">0</span></p>
                <p>Accuracy: <span id="accuracy">0%</span></p>
                <p>Backspaces: <span id="backspaces">0</span></p>
            </div>
        </div>

        <div class="popup" id="result-popup">
            <div class="popup-content">
                <h2>Practice Complete!</h2>
                <p id="popup-message">Time: <span id="popup-time">0</span>s, Errors: <span id="popup-errors">0</span>, WPM: <span id="popup-wpm">0</span>, CPM: <span id="popup-cpm">0</span>, Accuracy: <span id="popup-accuracy">0%</span></p>
                <button class="restart-btn" id="restart-btn">Restart</button>
            </div>
        </div>
        
        <div class="history-container">
            <h2>Your Typing History</h2>
            <ul id="history-list"></ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const texts = [
                "The quick brown efficiency fox jumps over the lazy dog.",
                "A journey of a thousand miles begins with a single step.",
                "To be or not to be, that is the question.",
                "In the end, it's not the years in your life that count. It's the life in your years.",
                "It is during our darkest moments that we must focus to see the light."
            ];

            const textToTypeElement = document.getElementById('text-to-type');
            const typingArea = document.getElementById('typing-area');
            const errorsElement = document.getElementById('errors');
            const wpmElement = document.getElementById('wpm');
            const cpmElement = document.getElementById('cpm');
            const accuracyElement = document.getElementById('accuracy');
            const backspacesElement = document.getElementById('backspaces');
            const resultPopup = document.getElementById('result-popup');
            const popupTime = document.getElementById('popup-time');
            const popupErrors = document.getElementById('popup-errors');
            const popupWPM = document.getElementById('popup-wpm');
            const popupCPM = document.getElementById('popup-cpm');
            const popupAccuracy = document.getElementById('popup-accuracy');
            const restartBtn = document.getElementById('restart-btn');
            const resetBtn = document.getElementById('reset-btn');
            const timerInput = document.getElementById('timer-input');
            const minutesElement = document.getElementById('minutes');
            const secondsElement = document.getElementById('seconds');
            const backspaceToggle = document.getElementById('backspace-toggle');
            const logoutBtn = document.getElementById('logout-btn');

            let timer = 0;
            let errors = 0;
            let totalErrors = 0;
            let totalCharacters = 0;
            let interval;
            let startTime;
            let currentText = '';
            let userSetTime;
            let timerStarted = false;
            let typedCharacters = 0;
            let previousLength = 0;
            let backspaceCount = 0;
            let isTyping = false;

            // Hamburger menu functionality
            const hamburgerMenu = document.querySelector('.hamburger-menu');
            const nav = document.querySelector('header nav');

            hamburgerMenu.addEventListener('click', () => {
                hamburgerMenu.classList.toggle('active');
                nav.classList.toggle('show');
            });

            function updateTimerDisplay() {
                const minutes = Math.floor(timer / 60);
                const seconds = timer % 60;
                minutesElement.textContent = minutes.toString().padStart(2, '0');
                secondsElement.textContent = seconds.toString().padStart(2, '0');
            }

            function startTimer() {
                clearInterval(interval);
                timer = parseInt(timerInput.value, 10);
                updateTimerDisplay();
                interval = setInterval(() => {
                    timer--;
                    if (timer < 0) {
                        endTypingSession();
                    } else {
                        updateTimerDisplay();
                    }
                }, 1000);
            }

            function resetPractice() {
                clearInterval(interval);
                typingArea.value = '';
                typingArea.disabled = false;
                errors = 0;
                totalErrors = 0;
                totalCharacters = 0;
                timerStarted = false;
                startTime = null;
                typedCharacters = 0;
                previousLength = 0;
                backspaceCount = 0;
                timer = parseInt(timerInput.value, 10);
                updateTimerDisplay();
                updateStats();
                isTyping = false;
                backspaceToggle.disabled = false;
                startPractice();
            }

            function startPractice() {
                currentText = texts[Math.floor(Math.random() * texts.length)];
                textToTypeElement.innerHTML = currentText.split('').map(char => `<span>${char}</span>`).join('');
                typingArea.value = '';
                typingArea.disabled = false;
                typingArea.focus();
                userSetTime = parseInt(timerInput.value, 10);
                timer = userSetTime;
                errors = 0;
                totalErrors = 0;
                totalCharacters = 0;
                timerStarted = false;
                startTime = null;
                typedCharacters = 0;
                previousLength = 0;
                backspaceCount = 0;
                isTyping = false;
                backspaceToggle.disabled = false;
                updateStats();
                updateTimerDisplay();
            }

            typingArea.addEventListener('input', (e) => {
                if (!isTyping) {
                    isTyping = true;
                    backspaceToggle.disabled = true;
                }

                if (!timerStarted) {
                    startTimer();
                    timerStarted = true;
                    startTime = new Date();
                }
                const typedText = typingArea.value;
                const textSpans = textToTypeElement.querySelectorAll('span');
                typedCharacters = typedText.length;

                if (typedText.length < previousLength) {
                    backspaceCount++;
                } else if (typedText.length > previousLength) {
                    const newChar = typedText[typedText.length - 1];
                    const expectedChar = currentText[typedText.length - 1];
                    if (newChar !== expectedChar) {
                        totalErrors++;
                    }
                }

                previousLength = typedText.length;
                totalCharacters = typedCharacters;

                if (totalCharacters > 0) {
                    const accuracy = ((totalCharacters - totalErrors) / totalCharacters) * 100;
                    accuracyElement.textContent = accuracy.toFixed(2) + '%';
                }

                textSpans.forEach((span, index) => {
                    if (index < typedText.length) {
                        if (typedText[index] === currentText[index]) {
                            span.classList.add('correct');
                            span.classList.remove('incorrect', 'space-error');
                        } else {
                            span.classList.remove('correct');
                            if (currentText[index] === ' ') {
                                span.classList.add('space-error');
                                span.classList.remove('incorrect');
                            } else {
                                span.classList.add('incorrect');
                                span.classList.remove('space-error');
                            }
                        }
                    } else {
                        span.classList.remove('correct', 'incorrect', 'space-error');
                    }
                });

                updateStats();

                if (typedText.length >= currentText.length) {
                    endTypingSession();
                }
            });

            function displayPopup(time, errors, wpm, cpm) {
                const accuracy = totalCharacters > 0 ? ((totalCharacters - errors) / totalCharacters) * 100 : 0;
                popupTime.textContent = time.toFixed(2);
                popupErrors.textContent = errors;
                popupWPM.textContent = wpm;
                popupCPM.textContent = cpm;
                popupAccuracy.textContent = accuracy.toFixed(2) + '%';
                resultPopup.style.display = 'block';

            }           

            function endTypingSession() {
                clearInterval(interval);
                typingArea.disabled = true;
                backspaceToggle.disabled = false;
                isTyping = false;
                const endTime = new Date();
                const timeInMinutes = (endTime - startTime) / 60000;
                const { wpm, cpm } = calculateWPMAndCPM(typedCharacters, timeInMinutes);
                const accuracy = totalCharacters > 0 ? ((totalCharacters - totalErrors) / totalCharacters) * 100 : 0;
                displayPopup(timeInMinutes * 60, totalErrors, wpm, cpm);
    
                // Save result and update history
                saveResult(wpm, cpm, accuracy, totalErrors, timeInMinutes * 60);
                updateHistory(wpm, cpm, accuracy, totalErrors, timeInMinutes * 60);
            }    
            
            function saveResult(wpm, cpm, accuracy, errors, time) {
                fetch('save_result.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `wpm=${wpm}&cpm=${cpm}&accuracy=${accuracy}&errors=${errors}&time=${time}`
                })
                .then(response => response.text())
                .then(data => console.log(data))
                .catch((error) => console.error('Error:', error));
            }              

            function updateHistory(wpm, cpm, accuracy, errors, time) {
                const historyList = document.getElementById('history-list');
                const li = document.createElement('li');
                li.textContent = `WPM: ${wpm}, CPM: ${cpm}, Accuracy: ${accuracy.toFixed(2)}%, Errors: ${errors}, Time: ${time.toFixed(2)}s`;
                historyList.insertBefore(li, historyList.firstChild);
            }

            function updateStats() {
                errorsElement.textContent = totalErrors;
                backspacesElement.textContent = backspaceCount;
                const elapsedTimeInMinutes = (new Date() - startTime) / 60000;
                const { wpm, cpm } = calculateWPMAndCPM(typedCharacters, elapsedTimeInMinutes);
                wpmElement.textContent = wpm;
                cpmElement.textContent = cpm;
            }

            function calculateWPMAndCPM(characters, timeInMinutes) {
                const words = characters / 5;
                const wpm = timeInMinutes > 0 ? Math.round(words / timeInMinutes) : 0;
                const cpm = timeInMinutes > 0 ? Math.round(characters / timeInMinutes) : 0;
                return { wpm, cpm };
            }

            restartBtn.addEventListener('click', () => {
                resultPopup.style.display = 'none';
                startPractice();
            });

            resetBtn.addEventListener('click', resetPractice);

            backspaceToggle.addEventListener('change', () => {
                if (!backspaceToggle.checked) {
                    typingArea.addEventListener('keydown', handleBackspace);
                } else {
                    typingArea.removeEventListener('keydown', handleBackspace);
                }
            });

            function handleBackspace(e) {
                if (!backspaceToggle.checked && e.key === 'Backspace') {
                    e.preventDefault();
                }
            }

            textToTypeElement.addEventListener('copy', (e) => {
                e.preventDefault();
                return false;
            });

            textToTypeElement.addEventListener('selectstart', (e) => {
                e.preventDefault();
                return false;
            });

            textToTypeElement.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                return false;
            });

            textToTypeElement.addEventListener('dragstart', (e) => {
                e.preventDefault();
                return false;
            });

            typingArea.addEventListener('paste', (e) => {
                e.preventDefault();
                return false;
            });

            logoutBtn.addEventListener('click', () => {
                window.location.href = 'login.php';
            });

            startPractice();
            fetchHistory();
        });
        function fetchHistory() {
                fetch('get_history.php')
                .then(response => response.json())
                .then(data => {
                    const historyList = document.getElementById('history-list');
                    historyList.innerHTML = '';
                    if (data.error) {
                        console.error('Error fetching history:', data.error);
                        const li = document.createElement('li');
                        li.textContent = 'Error fetching history. Please try again later.';
                        historyList.appendChild(li);
                    } else if (data.length === 0) {
                        const li = document.createElement('li');
                        li.textContent = 'No history available. Complete a typing session to see your results.';
                        historyList.appendChild(li);
                    } else {
                        data.forEach(result => {
                            const li = document.createElement('li');
                            li.textContent = `WPM: ${result.wpm}, CPM: ${result.cpm}, Accuracy: ${result.accuracy}%, Errors: ${result.errors}, Time: ${parseFloat(result.time).toFixed(2)}s`;
                            historyList.appendChild(li);
                        });
                    }
                })
                .catch((error) => {
                    console.error('Fetch error:', error);
                    const historyList = document.getElementById('history-list');
                    historyList.innerHTML = '<li>Error fetching history. Please try again later.</li>';
                });
            } 
            fetchHistory();    
            
    </script>
</body>
</html>