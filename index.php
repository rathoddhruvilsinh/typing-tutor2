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
    <title>Typing Tutor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto+Mono&family=Roboto:wght@400;700&display=swap');

:root {
    --primary-color: #2c3e50;
    --secondary-color: #34495e;
    --accent-color: #3498db;
    --background-color: #ecf0f1;
    --text-color: #2c3e50;
    --success-color: #2ecc71;
    --error-color: #e74c3c;
}

body {
    font-family: 'Roboto', sans-serif;
    margin: 0;
    background-color: var(--background-color);
    color: var(--text-color);
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 100vh;
}

header {
    width: 100%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: #ffffff;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1000;
}

header h1 {
    font-size: 1.8rem;
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    letter-spacing: 2px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    margin-top: 3px;
    margin-left: 30px;
    margin-bottom: 5px ;
    padding: 0;
}

.hamburger-menu {
    display: none;
    flex-direction: column;
    justify-content: space-around;
    width: 30px;
    height: 25px;
    background: transparent;
    border: none;
    cursor: pointer;
    margin-right: 20px;
    margin-top: 0;
    padding: 0;
    z-index: 1001;
}

.hamburger-menu span {
    width: 30px;
    height: 3px;
    background-color: #ffffff;
    border-radius: 10px;
    transition: all 0.3s linear;
    position: relative;
    transform-origin: 1px;
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
    background-color: var(--accent-color);
    color: #ffffff;
    border: none;
    padding: 8px 16px;
    font-size: 0.9rem;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: bold;
    text-transform: uppercase;
    margin-right: 20px;
    letter-spacing: 1px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

#logout-btn:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
}

.main-container {
    width: 100%;
    max-width: 600px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    box-sizing: border-box;
    flex-direction: column;
}

.container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
    width: 100%;
    max-width: 500px;
    padding: 20px;
    background: linear-gradient(135deg, #ffffff, #f5f5f5);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card {
    padding: 20px;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

#text-container {
    background-color: var(--accent-color);
    padding: 20px;
    border-radius: 10px;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

#text-to-type {
    font-size: 1.1rem;
    color: #ffffff;
    line-height: 1.6;
    font-family: 'Roboto Mono', monospace;
}

.correct { color: var(--success-color); }
.incorrect { color: var(--error-color); }
.space-error {
    color: var(--error-color);
    text-decoration: underline wavy var(--error-color);
}

#typing-area {
    width: 100%;
    height: 60px;
    border: 2px solid var(--accent-color);
    border-radius: 10px;
    padding: 10px;
    font-size: 1rem;
    font-family: 'Roboto Mono', monospace;
    background-color: #ffffff;
    color: var(--text-color);
    resize: none;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

#typing-area:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.3);
}

.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
}

.stats p {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    font-weight: 600;
    margin: 5px 0;
}

.stats i {
    font-size: 1.5rem;
    margin-bottom: 5px;
    color: var(--accent-color);
}

.history-container {
    width: 100%;
    max-width: 600px;
    margin-top: 20px;
    background: linear-gradient(135deg, #ffffff, #f5f5f5);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    border-radius: 10px;
    padding: 20px;
    box-sizing: border-box;
}

.history-container h2 {
    font-size: 1.4rem;
    color: var(--primary-color);
    margin-bottom: 15px;
    font-family: 'Montserrat', sans-serif;
}

#history-list {
    list-style-type: none;
    padding: 0;
}

#history-list li {
    background-color: #ffffff;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 5px;
    font-size: 0.9rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

#history-list li:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.popup.show {
    opacity: 1;
    visibility: visible;
}

.popup-content {
    background-color: #ffffff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    text-align: center;
    max-width: 500px;
    width: 90%;
    transform: scale(0.9);
    opacity: 0;
    transition: all 0.3s ease;
}

.popup.show .popup-content {
    transform: scale(1);
    opacity: 1;
}

.popup-content h2 {
    color: var(--primary-color);
    margin-bottom: 20px;
    font-size: 2rem;
}

.result-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.result-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.result-item i {
    font-size: 2rem;
    color: var(--accent-color);
    margin-bottom: 10px;
}

.result-item span {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 5px;
}

.result-item strong {
    font-size: 1.2rem;
    color: var(--primary-color);
}

.popup-actions {
    display: flex;
    justify-content: center;
    gap: 20px;
}

.popup-actions button {
    padding: 10px 20px;
    font-size: 1rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.restart-btn {
    background-color: var(--accent-color);
    color: #ffffff;
}

.share-btn {
    background-color: #4CAF50;
    color: #ffffff;
}

.popup-actions button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.timer-card {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: #ffffff;
    text-align: center;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.timer-display {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 15px;
    color: var(--accent-color);
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    animation: pulse 1s infinite alternate;
}

@keyframes pulse {
    from { transform: scale(1); }
    to { transform: scale(1.05); }
}

.timer-controls {
    display: flex;
    align-items: center;
    margin-top: 10px;
}

#timer-input {
    width: 60px;
    padding: 8px;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    text-align: center;
    margin-right: 10px;
}

.restart-btn, #reset-btn {
    background: linear-gradient(135deg, var(--accent-color), #2980b9);
    color: #ffffff;
    border: none;
    padding: 12px 24px;
    font-size: 1rem;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.restart-btn:hover, #reset-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(0,0,0,0.15);
    background: linear-gradient(135deg, #2980b9, var(--accent-color));
}

.backspace-toggle {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.backspace-toggle label {
    margin-right: 10px;
    font-weight: 600;
}

.backspace-toggle input[type="checkbox"] {
    transform: scale(1.2);
    margin-right: 5px;
}

@media screen and (max-width: 768px) {
    header {
        padding: 15px;
    }

    header h1 {
        font-size: 1.5rem;
        margin-left: 20px;
    }

    .hamburger-menu {
        display: flex;
    }

    header nav {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background-color: var(--primary-color);
        flex-direction: column;
        align-items: center;
        padding: 20px 0;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transform: translateY(-100%);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease-in-out;
    }

    header nav.show {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }

    nav .username {
        color: #ffffff;
        margin-bottom: 10px;
    }

    header nav #logout-btn {
        width: 80%;
        max-width: 200px;
    }

    .container {
        padding: 15px;
    }

    #text-to-type {
        font-size: 0.9rem;
    }

    #typing-area {
        height: 50px;
        font-size: 0.9rem;
    }

    .timer-display {
        font-size: 2rem;
    }

    .restart-btn, #reset-btn {
        padding: 10px 20px;
        font-size: 0.9rem;
    }

    .stats {
        font-size: 0.8rem;
    }

    .history-container h2 {
        font-size: 1.2rem;
    }

    #history-list li {
        font-size: 0.8rem;
    }
}

@media screen and (max-width: 480px) {
    .container {
        padding: 10px;
    }

    header h1 {
        font-size: 1.2rem;
    }

    #text-to-type {
        font-size: 0.8rem;
    }

    #typing-area {
        height: 40px;
        font-size: 0.8rem;
    }

    .timer-display {
        font-size: 1.5rem;
    }

    .restart-btn, #reset-btn {
        padding: 8px 16px;
        font-size: 0.8rem;
    }

    .stats {
        font-size: 0.7rem;
    }

    .history-container h2 {
        font-size: 1rem;
    }

    #history-list li {
        font-size: 0.7rem;
        padding: 10px;
    }

    .timer-controls {
        flex-direction: column;
        align-items: stretch;
    }

    #timer-input {
        width: 100%;
        margin-bottom: 10px;
        margin-right: 0;
    }

    #reset-btn {
        width: 100%;
    }
    .popup-content {
        padding: 20px;
    }

    .popup-content h2 {
        font-size: 1.4rem;
    }

    .popup-content p {
        font-size: 0.9rem;
    }

    .backspace-toggle {
        flex-direction: column;
        align-items: flex-start;
    }

    .backspace-toggle label {
        margin-bottom: 5px;
    }
}

/* Hamburger menu animation */
.hamburger-menu.active span:first-child {
    transform: rotate(45deg) translate(5px, 5px);
}

.hamburger-menu.active span:nth-child(2) {
    opacity: 0;
}

.hamburger-menu.active span:nth-child(3) {
    transform: rotate(-45deg) translate(7px, -6px);
}

/* Fade-in animation for the main content */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.main-container {
    animation: fadeIn 0.5s ease-out;
}

/* Improve focus styles for accessibility */
a:focus, button:focus, input:focus, textarea:focus {
    outline: 2px solid var(--accent-color);
    outline-offset: 2px;
}

/* Add a subtle hover effect to the text container */
#text-container:hover {
    box-shadow: 0 0 15px rgba(52, 152, 219, 0.3);
}

/* Style placeholder text */
::placeholder {
    color: #999;
    opacity: 1;
}

/* Improve contrast for the timer input */
#timer-input {
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Add a transition effect to the history list items */
#history-list li {
    transition: all 0.3s ease;
}

#history-list li:hover {
    background-color: rgba(52, 152, 219, 0.1);
}

/* Add a subtle text shadow to headings */
h1, h2 {
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

/* Improve button active state */
button:active {
    transform: translateY(1px);
}

/* Add a transition to the backspace toggle */
.backspace-toggle input[type="checkbox"] {
    transition: all 0.3s ease;
}

/* Style disabled state of inputs and buttons */
input:disabled, button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Add a subtle border to the main container */
.main-container {
    border: 1px solid rgba(0, 0, 0, 0.1);
}

/* Improve readability of the history list */
#history-list li {
    line-height: 1.4;
}

/* Add a transition to the logout button */
#logout-btn {
    transition: all 0.3s ease;
}

/* Style the selection color */
::selection {
    background-color: var(--accent-color);
    color: #ffffff;
}

/* Add a subtle animation to icons */
.stats i, .popup-content i {
    transition: transform 0.3s ease;
}

.stats p:hover i, .popup-content p:hover i {
    transform: scale(1.2);
}

/* Hide scrollbars for all elements */
* {
    scrollbar-width: none;  /* Firefox */
    -ms-overflow-style: none;  /* Internet Explorer 10+ */
}

/* Hide scrollbars for WebKit browsers */
::-webkit-scrollbar {
    display: none;
}

/* Ensure content is still scrollable */
body, .container, .history-container {
    overflow-y: auto;
}
    </style>
</head>
<body>
    <header>
    <h1>Typing Tutor</h1>
    <div class="hamburger-menu">
        <span></span>
        <span></span>
        <span></span>
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
                <p><i class="fas fa-times-circle"></i> Errors: <span id="errors">0</span></p>
                <p><i class="fas fa-tachometer-alt"></i> WPM: <span id="wpm">0</span></p>
                <p><i class="fas fa-keyboard"></i> CPM: <span id="cpm">0</span></p>
                <p><i class="fas fa-bullseye"></i> Accuracy: <span id="accuracy">0%</span></p>
                <p><i class="fas fa-backspace"></i> Backspaces: <span id="backspaces">0</span></p>
            </div>
        </div>

        <div class="popup" id="result-popup">
    <div class="popup-content">
        <h2>Practice Complete!</h2>
        <div class="result-grid">
            <div class="result-item">
                <i class="far fa-clock"></i>
                <span>Time</span>
                <strong id="popup-time">0s</strong>
            </div>
            <div class="result-item">
                <i class="fas fa-times-circle"></i>
                <span>Errors</span>
                <strong id="popup-errors">0</strong>
            </div>
            <div class="result-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>WPM</span>
                <strong id="popup-wpm">0</strong>
            </div>
            <div class="result-item">
                <i class="fas fa-keyboard"></i>
                <span>CPM</span>
                <strong id="popup-cpm">0</strong>
            </div>
            <div class="result-item">
                <i class="fas fa-bullseye"></i>
                <span>Accuracy</span>
                <strong id="popup-accuracy">0%</strong>
            </div>
        </div>
        <div class="popup-actions">
            <button class="restart-btn" id="restart-btn">Try Again</button>
            <button class="share-btn" id="share-btn">Share Result</button>
        </div>
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
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const nav = document.querySelector('header nav');

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

    hamburgerMenu.addEventListener('click', () => {
        hamburgerMenu.classList.toggle('active');
        nav.classList.toggle('show');
    });

    document.addEventListener('click', (event) => {
        if (!hamburgerMenu.contains(event.target) && !nav.contains(event.target)) {
            hamburgerMenu.classList.remove('active');
            nav.classList.remove('show');
        }
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

        const accuracy = calculateAccuracy(totalCharacters, totalErrors);
        accuracyElement.textContent = accuracy.toFixed(2) + '%';

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

    function calculateAccuracy(totalCharacters, totalErrors) {
        if (totalCharacters === 0) return 0;
        const accuracy = Math.max(0, ((totalCharacters - totalErrors) / totalCharacters) * 100);
        return Math.min(accuracy, 100);
    }

    function displayPopup(time, errors, wpm, cpm, accuracy) {
        popupTime.textContent = time.toFixed(2);
        popupErrors.textContent = errors;
        popupWPM.textContent = wpm;
        popupCPM.textContent = cpm;
        popupAccuracy.textContent = accuracy.toFixed(2) + '%';
        resultPopup.classList.add('show');
    }

    function endTypingSession() {
        clearInterval(interval);
        typingArea.disabled = true;
        backspaceToggle.disabled = false;
        isTyping = false;
        const endTime = new Date();
        const timeInMinutes = (endTime - startTime) / 60000;
        const { wpm, cpm } = calculateWPMAndCPM(typedCharacters, timeInMinutes);
        const accuracy = calculateAccuracy(totalCharacters, totalErrors);
        
        displayPopup(timeInMinutes * 60, totalErrors, wpm, cpm, accuracy);
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
        li.innerHTML = `
            <i class="fas fa-tachometer-alt"></i> WPM: ${wpm}, 
            <i class="fas fa-keyboard"></i> CPM: ${cpm}, 
            <i class="fas fa-bullseye"></i> Accuracy: ${accuracy.toFixed(2)}%, 
            <i class="fas fa-times-circle"></i> Errors: ${errors}, 
            <i class="far fa-clock"></i> Time: ${time.toFixed(2)}s
        `;
        historyList.insertBefore(li, historyList.firstChild);
    }

    function updateStats() {
        errorsElement.textContent = totalErrors;
        backspacesElement.textContent = backspaceCount;
        const elapsedTimeInMinutes = (new Date() - startTime) / 60000;
        const { wpm, cpm } = calculateWPMAndCPM(typedCharacters, elapsedTimeInMinutes);
        wpmElement.textContent = wpm;
        cpmElement.textContent = cpm;
        
        const accuracy = calculateAccuracy(totalCharacters, totalErrors);
        accuracyElement.textContent = accuracy.toFixed(2) + '%';
    }

    function calculateWPMAndCPM(characters, timeInMinutes) {
        const words = characters / 5;
        const wpm = timeInMinutes > 0 ? Math.round(words / timeInMinutes) : 0;
        const cpm = timeInMinutes > 0 ? Math.round(characters / timeInMinutes) : 0;
        return { wpm, cpm };
    }

    restartBtn.addEventListener('click', () => {
        resultPopup.classList.remove('show');
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

    textToTypeElement.addEventListener('copy', (e) => e.preventDefault());
    textToTypeElement.addEventListener('selectstart', (e) => e.preventDefault());
    textToTypeElement.addEventListener('contextmenu', (e) => e.preventDefault());
    textToTypeElement.addEventListener('dragstart', (e) => e.preventDefault());
    typingArea.addEventListener('paste', (e) => e.preventDefault());

    logoutBtn.addEventListener('click', () => {
        window.location.href = 'login.php';
    });

    document.getElementById('share-btn').addEventListener('click', () => {
        const shareText = `I just completed a typing test with ${popupWPM.textContent} WPM, ${popupAccuracy.textContent} accuracy, and ${popupErrors.textContent} errors!`;
        
        if (navigator.share) {
            navigator.share({
                title: 'My Typing Test Result',
                text: shareText,
                url: window.location.href,
            })
            .then(() => console.log('Successful share'))
            .catch((error) => console.log('Error sharing', error));
        } else {
            alert('Share feature is not supported on this browser. You can copy this text:\n\n' + shareText);
        }
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
            li.textContent = 'Error fetching history';
            historyList.appendChild(li);
        } else {
            data.forEach(item => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <i class="fas fa-tachometer-alt"></i> WPM: ${item.wpm}, 
                    <i class="fas fa-keyboard"></i> CPM: ${item.cpm}, 
                    <i class="fas fa-bullseye"></i> Accuracy: ${parseFloat(item.accuracy).toFixed(2)}%, 
                    <i class="fas fa-times-circle"></i> Errors: ${item.errors}, 
                    <i class="far fa-clock"></i> Time: ${parseFloat(item.time).toFixed(2)}s
                `;
                historyList.appendChild(li);
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const historyList = document.getElementById('history-list');
        const li = document.createElement('li');
        li.textContent = 'Error fetching history';
        historyList.appendChild(li);
    });
}
</script>
</body>
</html>